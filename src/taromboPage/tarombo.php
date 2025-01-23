<?php
require_once '../../server/taromboBackend.php';
?>

<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumpulan Profile - Smart Family</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {}
            }
        };
    </script>
    <style>
        .detail-section {
            max-height: 0;
            opacity: 0;
            transform: scaleY(0);
            transform-origin: top;
            transition: all 0.3s ease-out;
            overflow: hidden;
        }
        
        .detail-section.active {
            max-height: 1000px;
            opacity: 1;
            transform: scaleY(1);
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen flex flex-col">

    <header class="bg-blue-500 dark:bg-blue-800 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="../../index.php" class="ml-5 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-1 px-3 rounded"><</a>
                <h1 class="text-xl font-semibold ml-5">Tarombo</h1>
            </div>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="mb-8 flex flex-col md:flex-row justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">Profile</h2>
                <p class="text-gray-600 dark:text-gray-300">Jelajahi dan temukan anggota keluarga dalam silsilah Smart Family.</p>
            </div>
            <div class="mt-4 md:mt-0 w-full md:w-auto">
                <form action="" method="GET" class="flex flex-col md:flex-row items-center gap-2">
                    <input type="text" name="search" placeholder="Cari anggota keluarga..." value="<?php echo htmlspecialchars($search); ?>" class="w-full md:w-64 p-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                    
                    <select name="generation" class="w-full md:w-auto p-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        <option value="">Sundut</option>
                        <?php foreach ($generations as $gen): ?>
                            <option value="<?php echo htmlspecialchars($gen); ?>" <?php echo $generation == $gen ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($gen); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="type" class="w-full md:w-auto p-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        <option value="">Generasi</option>
                        <option value="sundut" <?php echo $type === 'sundut' ? 'selected' : ''; ?>>Sundut</option>
                        <option value="bere" <?php echo $type === 'bere' ? 'selected' : ''; ?>>Bere</option>
                    </select>
                    
                    <button type="submit" class="w-full md:w-auto bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md transition duration-300 ease-in-out">
                        Cari
                    </button>
                </form>
                <div class="mt-4 text-gray-600 dark:text-gray-300">
                    Menampilkan <?php echo ($offset + 1); ?>-<?php echo min($offset + $records_per_page, $total_records); ?> 
                    dari <?php echo $total_records; ?> anggota keluarga
                </div>
                <?php if ($search || $generation || $type): ?>
                    <a href="?" class="text-blue-500 hover:underline text-sm mt-1 inline-block">Reset pencarian</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <div class="flex flex-col h-[400px]">
                            <div class="p-4 flex-1">
                                <img 
                                    src="<?php 
                                        if ($row['foto']) {
                                            echo '../../' . htmlspecialchars($row['foto']) . '?v=' . time();
                                        } else {
                                            echo ($row['jenis_kelamin'] === 'Laki-laki') 
                                                ? '../../assets/img/default_male.jpg?v=' . time()
                                                : '../../assets/img/default_female.jpg?v=' . time();
                                        }
                                    ?>" 
                                    alt="<?php echo htmlspecialchars($row['nama']); ?>" 
                                    class="w-32 h-32 rounded-full mx-auto mb-4 object-cover shadow-lg">
                                
                                <div class="h-24 flex flex-col justify-center">
                                    <h3 class="text-xl font-semibold text-center line-clamp-2 dark:text-white"><?php echo htmlspecialchars($row['nama']); ?></h3>
                                </div>
                                
                                <p class="text-gray-600 dark:text-gray-300 text-center mb-4">
                                    <?php echo ucfirst(htmlspecialchars($row['query_boolean'])); ?> <?php echo htmlspecialchars($row['generasi']); ?>
                                </p>
                            </div>

                            <div class="p-4 mt-auto">
                                <button class="toggle-detail w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600 transition duration-300 ease-in-out">
                                    Lihat Detail
                                </button>
                            </div>
                        </div>

                        <div class="detail-section p-4 border-t dark:border-gray-700 origin-top">
                            <?php if (!empty($row['jenis_kelamin'])): ?>
                                <p class="dark:text-white"><strong>Jenis Kelamin:</strong> <?php echo htmlspecialchars($row['jenis_kelamin']); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($row['domisili'])): ?>
                                <p class="dark:text-white"><strong>Domisili:</strong> <?php echo htmlspecialchars($row['domisili']); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($row['nama_ayah'])): ?>
                                <p class="dark:text-white"><strong>Ayah:</strong> <?php echo htmlspecialchars($row['nama_ayah']); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($row['nama_ibu'])): ?>
                                <p class="dark:text-white"><strong>Ibu:</strong> <?php echo htmlspecialchars($row['nama_ibu']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($row['jenis_kelamin'] === 'Laki-laki'): ?>
                                <?php
                                $istri = array_filter([
                                    $row['nama_istri_1'],
                                    $row['nama_istri_2'],
                                    $row['nama_istri_3']
                                ]);
                                if (!empty($istri)): ?>
                                    <p class="dark:text-white"><strong>Istri:</strong></p>
                                    <?php foreach ($istri as $nama_istri): ?>
                                        <p class="dark:text-white"><?php echo htmlspecialchars($nama_istri); ?></p>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php elseif ($row['jenis_kelamin'] === 'Perempuan' && !empty($row['nama_suami'])): ?>
                                <p class="dark:text-white"><strong>Suami:</strong></p>
                                <?php
                                $suami_list = explode(',', $row['nama_suami']);
                                foreach ($suami_list as $nama_suami): ?>
                                    <p class="dark:text-white"><?php echo htmlspecialchars(trim($nama_suami)); ?></p>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (!empty($row['nama_anak'])): ?>
                                <p class="dark:text-white"><strong>Anak:</strong></p>
                                <?php 
                                $anak_list = explode(',', $row['nama_anak']);
                                foreach ($anak_list as $nama_anak): ?>
                                    <p class="dark:text-white"><?php echo htmlspecialchars(trim($nama_anak)); ?></p>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-8">
                    <p class="text-gray-500 dark:text-gray-300">Tidak ada anggota keluarga yang ditemukan<?php echo $search ? ' untuk pencarian "'.htmlspecialchars($search).'"' : ''; ?>.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-8 flex justify-center items-center space-x-2">
            <?php if ($total_pages > 1): ?>
                <div class="flex items-center space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $generation ? '&generation='.urlencode($generation) : ''; ?><?php echo $type ? '&type='.urlencode($type) : ''; ?>" 
                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                            &laquo;
                        </a>
                        <a href="?page=<?php echo $page-1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $generation ? '&generation='.urlencode($generation) : ''; ?><?php echo $type ? '&type='.urlencode($type) : ''; ?>" 
                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                            &lsaquo;
                        </a>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);

                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $generation ? '&generation='.urlencode($generation) : ''; ?><?php echo $type ? '&type='.urlencode($type) : ''; ?>" 
                        class="px-3 py-1 <?php echo $i == $page ? 'bg-blue-600' : 'bg-blue-500'; ?> text-white rounded hover:bg-blue-600">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $generation ? '&generation='.urlencode($generation) : ''; ?><?php echo $type ? '&type='.urlencode($type) : ''; ?>" 
                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                            &rsaquo;
                        </a>
                        <a href="?page=<?php echo $total_pages; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $generation ? '&generation='.urlencode($generation) : ''; ?><?php echo $type ? '&type='.urlencode($type) : ''; ?>" 
                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                            &raquo;
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-8 space-x-4">
            <a href="../../assets/file/tarombo.pdf" target="_blank" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-md transition duration-300 ease-in-out">PDF</a>  
        </div>
    </main>

    <footer id="footer-static" class="bg-blue-500 dark:bg-blue-800 text-white py-4 mt-auto">
        <div class="container mx-auto text-center">
            <p>&copy; 2024 Smart Family. All rights reserved.</p>
        </div>
    </footer>

    <footer id="footer-fixed" class="bg-blue-500 dark:bg-blue-800 text-white py-4 fixed bottom-0 left-0 right-0 flex justify-center items-center hidden">
        <p class="text-center">&copy; 2024 Smart Family. All rights reserved.</p>
    </footer>

    <button onclick="toggleDarkMode()" class="fixed bottom-4 right-4 p-3 bg-gray-200 dark:bg-gray-700 rounded-full hover:scale-110 transition-transform duration-200">
        <span class="dark:hidden">🌙</span>
        <span class="hidden dark:inline">☀️</span>
    </button>

    <script>
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

        document.querySelectorAll('.toggle-detail').forEach(button => {
            button.addEventListener('click', function() {
                const card = this.closest('.bg-white');
                const detailSection = card.querySelector('.detail-section');
                detailSection.classList.toggle('active');
            });
        });

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