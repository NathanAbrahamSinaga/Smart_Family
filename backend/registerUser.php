<?php
session_start();
require_once 'config.php';

$conn = new mysql(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$nama_lengkap = $_POST["nama_lengkap"];
$username = $_POST["username"];
$password = $_POST["password"];
$confirm_password = $_POST["confirm_password"];

if ($password != $confirm_password) {
    header("location: registerForum.php?register_gagal=password");
    exit();
}