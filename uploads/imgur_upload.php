<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_FILES['image'])) {
        throw new Exception('No image file received');
    }

    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload error: ' . $_FILES['image']['error']);
    }

    $client_id = "054e6e9a51240aa";
    $image_path = $_FILES['image']['tmp_name'];

    if (!file_exists($image_path)) {
        throw new Exception('Uploaded file not found');
    }

    $image_data = file_get_contents($image_path);
    if ($image_data === false) {
        throw new Exception('Failed to read image data');
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.imgur.com/3/image.json',
        CURLOPT_POST => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HTTPHEADER => [
            'Authorization: Client-ID ' . $client_id
        ],
        CURLOPT_POSTFIELDS => [
            'image' => base64_encode($image_data)
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception('cURL error: ' . curl_error($ch));
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        throw new Exception('HTTP Error: ' . $http_code . ', Response: ' . $response);
    }

    $data = json_decode($response, true);
    if (!$data) {
        throw new Exception('Failed to decode JSON response');
    }

    if (!$data['success']) {
        throw new Exception('Imgur upload failed: ' . ($data['data']['error'] ?? 'Unknown error'));
    }

    echo json_encode([
        'success' => true,
        'link' => $data['data']['link']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>