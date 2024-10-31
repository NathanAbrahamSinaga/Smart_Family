<?php
session_start();
require_once '../../server/config.php';

$userType = $_SESSION["user_type"] ?? '';

session_unset();
session_destroy();

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