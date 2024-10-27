<?php
function handleImageUpload($file, $member_id, $generation) {
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Check if GD extension is loaded
    if (!extension_loaded('gd')) {
        return ['success' => false, 'error' => 'GD Library is not installed'];
    }

    // Validate file type using mime content
    $mime_type = mime_content_type($file['tmp_name']);
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, and GIF are allowed. Detected type: ' . $mime_type];
    }

    // Set upload directory
    $upload_dir = __DIR__;

    // Get the next available number
    $next_number = 0;
    $existing_files = glob($upload_dir . DIRECTORY_SEPARATOR . '*.webp');
    
    if (!empty($existing_files)) {
        $numbers = array_map(function($file) {
            return (int)pathinfo($file, PATHINFO_FILENAME);
        }, $existing_files);
        
        if (!empty($numbers)) {
            $next_number = max($numbers) + 1;
        }
    }

    $new_filename = $next_number . '.webp';
    $destination = $upload_dir . DIRECTORY_SEPARATOR . $new_filename;

    // Ensure upload directory exists and is writable
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            return ['success' => false, 'error' => 'Failed to create upload directory'];
        }
    }

    if (!is_writable($upload_dir)) {
        return ['success' => false, 'error' => 'Upload directory is not writable'];
    }

    // Convert and save image
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

        // Convert to WebP
        $result = imagewebp($image, $destination, 80);
        imagedestroy($image);

        if (!$result) {
            return ['success' => false, 'error' => 'Failed to save WebP image'];
        }

        $relative_path = '/uploads/' . $new_filename;
        return [
            'success' => true,
            'link' => $relative_path,
            'message' => 'File uploaded successfully'
        ];
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Error processing image: ' . $e->getMessage()];
    }
}

// This part is only used when the file is called directly, not when included
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $member_id = isset($_POST['member_id']) ? $_POST['member_id'] : '0';
    $generation = isset($_POST['generation']) ? $_POST['generation'] : '1';
    
    $result = handleImageUpload($_FILES['image'], $member_id, $generation);

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
?>