<?php
session_start();
require_once '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION["user_id"] = $row['id'];
            $_SESSION["username"] = $row['username'];
            header("location: /src/adminPage/adminPage.php");
            exit();
        } else {
            header("location: /src/loginPage/loginAdmin.php?login_gagal=password");
            exit();
        }
    } else {
        header("location: /src/loginPage/loginAdmin.php?login_gagal=username");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("location: /src/loginPage/loginAdmin.php");
    exit();
}
?>