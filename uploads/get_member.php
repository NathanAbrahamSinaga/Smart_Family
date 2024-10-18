<?php
require_once '../../server/config.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No ID provided']);
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM anggota WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $member = $result->fetch_assoc();
    echo json_encode($member);
} else {
    echo json_encode(['error' => 'Member not found']);
}

$stmt->close();
$conn->close();
?>