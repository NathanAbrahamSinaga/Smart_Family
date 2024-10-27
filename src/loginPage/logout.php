<?php
session_start();
require_once '../../server/config.php';

// Get the user type before clearing session
$userType = $_SESSION["user_type"] ?? '';

// Clear all session data
session_unset();
session_destroy();

// Redirect based on user type
switch($userType) {
    case 'admin':
        header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php");
        break;
    case 'forum':
        header("Location: " . BASE_URL . "src/loginPage/loginForum.php");
        break;
    case 'tarombo':
        header("Location: " . BASE_URL . "src/loginPage/loginTarombo.php");
        break;
    default:
        header("Location: " . BASE_URL . "index.php");
}
exit();
?>