<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Smart Family</title>
        <link rel="stylesheet" href="assets/css/output.css">
    </head>
    <body class="bg-gray-100">

        <div class="container mx-auto text-center mt-20">
            <div class="mb-8">
                <img src="assets/img/logo.png" alt="Smart Family Logo" class="mx-auto max-w-xs sm:max-w-sm">
            </div>

            <h2 class="text-3xl font-semibold mb-8">Smart Family</h2>

        <div class="flex flex-row justify-center space-x-4 sm:space-x-2 mb-8">
            <a href="src/loginPage/loginAdmin.php" class="w-32 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                Admin
            </a>
            <a href="src/loginPage/loginForum.php" class="w-32 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                Forum
            </a>
            <a href="src/loginPage/loginTarombo.php" class="w-32 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                Tarombo
            </a>
            <a href="src/loginPage/register.php" class="w-32 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                Register
            </a>
        </div>

    <footer id="footer-static" class="bg-blue-500 text-white py-4 mt-20">
        <div class="container mx-auto text-center">
            <p>&copy; 2024 Smart Family. All rights reserved.</p>
        </div>
    </footer>

    <footer id="footer-fixed" class="bg-blue-500 text-white py-4 fixed bottom-0 left-0 right-0 flex justify-center items-center">
        <p class="text-center">&copy; 2024 Smart Family. All rights reserved.</p>
    </footer>

    <script>
        function toggleFooter() {
            const footerStatic = document.getElementById('footer-static');
            const footerFixed = document.getElementById('footer-fixed');
            const isScrollable = document.body.scrollHeight > window.innerHeight;
            
            if (isScrollable) {
                footerStatic.classList.remove('hidden');
                footerFixed.classList.add('hidden');
            } else {
                footerStatic.classList.add('hidden');
                footerFixed.classList.remove('hidden');
            }
        }

        window.addEventListener('load', toggleFooter);

        window.addEventListener('resize', toggleFooter);
    </script>
    </body>
</html>