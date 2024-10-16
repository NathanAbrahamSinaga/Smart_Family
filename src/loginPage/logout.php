<?php
session_start();
require_once '../../server/config.php';
session_destroy();
header("Location: " . BASE_URL . "index.php");
exit();
?>
