<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = "YOUR_IMGUR_CLIENT_ID"; // Replace with your Imgur client ID

    if (isset($_FILES['image'])) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Client-ID ' . $client_id));
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('image' => base64_encode($image)));
        
        $reply = curl_exec($ch);
        curl_close($ch);
        
        $reply = json_decode($reply);
        
        if ($reply->success) {
            echo json_encode(['success' => true, 'link' => $reply->data->link]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Upload failed']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No image file received']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>