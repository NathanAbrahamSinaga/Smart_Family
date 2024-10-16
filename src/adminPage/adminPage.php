<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("location: ../loginPage/loginAdmin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page - Smart Family</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h1>INI HALAMAN ADMIN</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</p>
        <!-- Perbaiki link logout di sini -->
        <a href="../loginPage/logout.php" class="btn btn-danger">Logout</a>
    </div>

    <!-- Footer -->
    <footer>
        <p class="mb-0">&copy; 2024 Smart Family. All rights reserved.</p>
    </footer>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
