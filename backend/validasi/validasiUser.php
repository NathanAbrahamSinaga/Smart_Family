<?php
session_start();
require_once 'config.php';

$conn = new mysql(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_POST["username"];
$password = $_POST["password"];

$stmt = $conn->prepare("SELECT * FROM users_forum WHERE username = ? AND password = ?");
$stmt->bind_param("ssss", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        $_SESSION["user_id"] = $row['id'];
        $_SESSION["username"] = $row['username'];
        header("location: forumPage.php");
        exit();
    } else {
        header("location: loginForum.php?login_gagal=password");
        exit();
    }
} else {
    header("location: loginForum.php?login_gagal=username");
    exit();
}

$stmt->close();
$conn->close();
?>
