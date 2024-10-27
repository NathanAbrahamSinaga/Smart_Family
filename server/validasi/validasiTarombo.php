<?php
session_start();
require_once '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        header("Location: " . BASE_URL . "src/loginPage/loginTarombo.php?error=empty");
        exit();
    }

    $stmt = $conn->prepare("SELECT id, username, password FROM users_forum WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Clear any existing session data
            session_unset();
            
            // Set tarombo user session
            $_SESSION["user_id"] = $row['id'];
            $_SESSION["username"] = $row['username'];
            $_SESSION["user_type"] = "tarombo";
            
            header("Location: " . BASE_URL . "src/taromboPage/tarombo.php");
            exit();
        }
    }
    
    // If login fails
    header("Location: " . BASE_URL . "src/loginPage/loginTarombo.php?error=invalid");
    exit();

    $stmt->close();
    $conn->close();
}

// If not POST request
header("Location: " . BASE_URL . "src/loginPage/loginTarombo.php");
exit();
?>
