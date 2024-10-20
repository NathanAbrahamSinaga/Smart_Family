<?php
require_once '../server/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function convertToWebP($source, $destination) {
    // Check if GD extension is loaded
    if (!extension_loaded('gd')) {
        throw new Exception('GD Library is not installed');
    }

    // Get image information
    $imageInfo = getimagesize($source);
    if ($imageInfo === false) {
        throw new Exception('Could not get image information');
    }

    $mime = $imageInfo['mime'];

    switch ($mime) {
        case 'image/jpeg':
        case 'image/jpg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            // Handle transparency
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        default:
            throw new Exception('Unsupported image type: ' . $mime);
    }

    if (!$image) {
        throw new Exception('Failed to create image resource');
    }

    // Make sure the destination directory exists
    $destDir = dirname($destination);
    if (!file_exists($destDir)) {
        if (!mkdir($destDir, 0777, true)) {
            throw new Exception('Failed to create destination directory');
        }
    }

    // Check if directory is writable
    if (!is_writable($destDir)) {
        throw new Exception('Destination directory is not writable');
    }

    // Convert to WebP
    $result = imagewebp($image, $destination, 80);
    
    // Free up memory
    imagedestroy($image);

    if (!$result) {
        throw new Exception('Failed to save WebP image');
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $response = array();
    try {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $_FILES['image']['error']);
        }

        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        $file = $_FILES['image'];
        $member_id = isset($_POST['member_id']) ? $_POST['member_id'] : '0';
        $generation = isset($_POST['generation']) ? $_POST['generation'] : '1';

        // Validate file type using mime content
        $mime_type = mime_content_type($file['tmp_name']);
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed. Detected type: ' . $mime_type);
        }

        // Set absolute path for uploads directory
        $upload_dir = __DIR__;
        
        // Ensure upload directory exists and is writable
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        if (!is_writable($upload_dir)) {
            throw new Exception('Upload directory is not writable');
        }

        // Generate new filename
        $new_filename = $member_id . '_' . $generation . '.webp';
        $upload_path = $upload_dir . DIRECTORY_SEPARATOR . $new_filename;

        // Delete existing file if it exists
        if (file_exists($upload_path)) {
            unlink($upload_path);
        }

        // Convert and save image
        if (convertToWebP($file['tmp_name'], $upload_path)) {
            // Update database with new image path
            $relative_path = '/uploads/' . $new_filename;
            $stmt = $conn->prepare("UPDATE anggota SET foto = ? WHERE id = ?");
            $stmt->bind_param("si", $relative_path, $member_id);
            
            if ($stmt->execute()) {
                $response = [
                    'success' => true,
                    'link' => $relative_path,
                    'message' => 'File uploaded successfully'
                ];
            } else {
                throw new Exception('Failed to update database: ' . $conn->error);
            }
            $stmt->close();
        } else {
            throw new Exception('Failed to convert and save image');
        }

        $conn->close();
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'error' => $e->getMessage()
        ];
        error_log('Upload error: ' . $e->getMessage());
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
