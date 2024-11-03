<?php
require_once __DIR__ . '/../server/config.php';

function handleImageUpload($file, $member_name) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    if (!extension_loaded('gd')) {
        return ['success' => false, 'error' => 'GD Library is not installed'];
    }

    $mime_type = mime_content_type($file['tmp_name']);
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, and GIF are allowed. Detected type: ' . $mime_type];
    }

    $formatted_name = preg_replace('/[^a-zA-Z0-9_]/', '_', $member_name);
    $formatted_name = strtolower(trim($formatted_name, '_'));
    
    $upload_dir = dirname(__DIR__) . '/assets/foto';

    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            return ['success' => false, 'error' => 'Failed to create upload directory'];
        }
    }

    $filename = $formatted_name . '.webp';
    $destination = $upload_dir . DIRECTORY_SEPARATOR . $filename;

    if (!is_writable($upload_dir)) {
        chmod($upload_dir, 0755);
        if (!is_writable($upload_dir)) {
            return ['success' => false, 'error' => 'Upload directory is not writable'];
        }
    }

    try {
        $image = null;
        switch ($mime_type) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file['tmp_name']);
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file['tmp_name']);
                break;
        }

        if (!$image) {
            return ['success' => false, 'error' => 'Failed to create image resource'];
        }

        if (file_exists($destination)) {
            unlink($destination);
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $new_width = min(800, $width);
        $new_height = floor($height * ($new_width / $width));
        
        $tmp_image = imagecreatetruecolor($new_width, $new_height);
        imagealphablending($tmp_image, false);
        imagesavealpha($tmp_image, true);
        
        imagecopyresampled(
            $tmp_image, $image,
            0, 0, 0, 0,
            $new_width, $new_height,
            $width, $height
        );

        $result = imagewebp($tmp_image, $destination, 80);
        
        imagedestroy($image);
        imagedestroy($tmp_image);

        chmod($destination, 0644);

        if (!$result) {
            return ['success' => false, 'error' => 'Failed to save WebP image'];
        }

        $relative_path = 'assets/foto/' . $filename;
        return [
            'success' => true,
            'link' => $relative_path,
            'message' => 'File uploaded successfully'
        ];
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Error processing image: ' . $e->getMessage()];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $member_name = isset($_POST['member_name']) ? $_POST['member_name'] : 'unknown';
    
    $result = handleImageUpload($_FILES['image'], $member_name);

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
?>