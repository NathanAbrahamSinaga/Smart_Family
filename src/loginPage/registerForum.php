<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Masukkan nama lengkap, username, dan password</h1>
    <form method="post" action="registerUser.php">
        <div>
            <label for="Name">Nama Lengkap</label>
            <input type="text" name="name" id="name" placeholder="Masukkan nama lengkap" required>
        </div>
        <div>
            <label for="Username">Username</label>
            <input type="text" name="username" id="username" placeholder="Masukkan username" required>
        </div>
        <div>
            <label for="Password">Password</label>
            <input type="password" name="password" id="password" placeholder="Masukkan password" required>
        </div>
        <div>
            <button type="submit" name="register">Register</button>
        </div>
    </form>
</body>
</html>