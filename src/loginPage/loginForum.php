<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Masukkan username dan password</h1>
    <form method="post" action="validasiUser.php" >
        <div>
            <label for="Username">Username</label>
            <input type="text" name="username" id="username" required>
        </div>
        <div>
            <label for="Password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>
    </form>
</body>
</html>