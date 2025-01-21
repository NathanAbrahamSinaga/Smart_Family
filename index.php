<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Smart Family</title>
        <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body class="bg-gray-100 dark:bg-gray-900 min-h-screen">
        <div class="container mx-auto text-center mt-20">
            <button onclick="toggleDarkMode()" class="fixed top-4 right-4 p-3 bg-gray-200 dark:bg-gray-700 rounded-full hover:scale-110 transition-transform duration-200">
                <span class="dark:hidden">🌙</span>
                <span class="hidden dark:inline">☀️</span>
            </button>

            <div class="mb-8 transform transition-transform duration-500 hover:scale-105">
                <img src="assets/img/logo.png" alt="Smart Family Logo" class="mx-auto max-w-xs sm:max-w-sm">
            </div>

            <h2 class="text-3xl font-semibold mb-8 animate-pulse text-gray-900 dark:text-white">
                Smart Family
            </h2>

            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4 mb-8">
                <a href="src/loginPage/loginAdmin.php" 
                   class="w-32 mx-auto sm:mx-0 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded transform transition-transform duration-300 hover:scale-110 dark:bg-blue-600 dark:hover:bg-blue-700">
                    Admin
                </a>
                <a href="src/loginPage/loginForum.php" 
                   class="w-32 mx-auto sm:mx-0 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded transform transition-transform duration-300 hover:scale-110 dark:bg-blue-600 dark:hover:bg-blue-700">
                    Forum
                </a>
                <a href="src/taromboPage/tarombo.php" 
                   class="w-32 mx-auto sm:mx-0 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded transform transition-transform duration-300 hover:scale-110 dark:bg-blue-600 dark:hover:bg-blue-700">
                    Tarombo
                </a>
                <a href="src/loginPage/register.php" 
                   class="w-32 mx-auto sm:mx-0 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded transform transition-transform duration-300 hover:scale-110 dark:bg-blue-600 dark:hover:bg-blue-700">
                    Register
                </a>
            </div>

            <footer id="footer-fixed" class="bg-blue-500 text-white py-4 fixed bottom-0 left-0 right-0 flex justify-center items-center">
                <p class="text-center">&copy; 2024 Smart Family. All rights reserved.</p>
            </footer>
        </div>

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

            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                    }
                }
            }

            function initializeTheme() {
                const savedTheme = localStorage.getItem('theme');
                
                const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                
                if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }

            function toggleDarkMode() {
                const html = document.documentElement;
                
                if (html.classList.contains('dark')) {
                    html.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    html.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            }

            document.addEventListener('DOMContentLoaded', initializeTheme);
        </script>
    </body>
</html>