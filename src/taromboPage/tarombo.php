<?php
require_once '../../server/taromboBackend.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumpulan Profile - Smart Family</title>
    <link rel="stylesheet" href="../../assets/css/output.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <header class="bg-blue-500 text-white py-4">
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
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Profile</h2>
                <p class="text-gray-600">Jelajahi dan temukan anggota keluarga dalam silsilah Smart Family.</p>
            </div>
            <div class="mt-4 md:mt-0 w-full md:w-auto">
                <form action="" method="GET" class="flex flex-col md:flex-row items-center gap-2">
                    <input type="text" name="search" placeholder="Cari anggota keluarga..." value="<?php echo htmlspecialchars($search); ?>" class="w-full md:w-64 p-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <select name="generation" class="w-full md:w-auto p-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Sundut</option>
                        <?php foreach ($generations as $gen): ?>
                            <option value="<?php echo htmlspecialchars($gen); ?>" <?php echo $generation == $gen ? 'selected' : ''; ?>>
                                Sundut <?php echo htmlspecialchars($gen); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="w-full md:w-auto bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md transition duration-300 ease-in-out">
                        Cari
                    </button>
                </form>
                <div class="mt-4 text-gray-600">
                    Menampilkan <?php echo ($offset + 1); ?>-<?php echo min($offset + $records_per_page, $total_records); ?> 
                    dari <?php echo $total_records; ?> anggota keluarga
                </div>
                <?php if ($search || $generation): ?>
                    <a href="?" class="text-blue-500 hover:underline text-sm mt-1 inline-block">Reset pencarian</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div x-data="{ open: false }" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <div class="flex flex-col h-[300px]">
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
                                    <?php 
                                        $names = explode('|', $row['nama']);
                                        foreach ($names as $index => $name) {
                                            if ($index === 0) {
                                                echo '<h3 class="text-xl font-semibold text-center line-clamp-2">' . htmlspecialchars($name) . '</h3>';
                                            } else {
                                                echo '<h5 class="text-lg font-medium text-center line-clamp-1">' . htmlspecialchars($name) . '</h5>';
                                            }
                                        }
                                    ?>
                                </div>
                                
                                <p class="text-gray-600 text-center mb-4">Sundut <?php echo htmlspecialchars($row['generasi']); ?></p>
                            </div>

                            <div class="p-4 mt-auto">
                                <button @click="open = !open" class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600 transition duration-300 ease-in-out">
                                    Lihat Detail
                                </button>
                            </div>
                        </div>

                        <div 
                            x-show="open" 
                            x-transition:enter="transition ease-out duration-300" 
                            x-transition:enter-start="opacity-0 transform scale-y-0" 
                            x-transition:enter-end="opacity-100 transform scale-y-100" 
                            x-transition:leave="transition ease-in duration-200" 
                            x-transition:leave-start="opacity-100 transform scale-y-100" 
                            x-transition:leave-end="opacity-0 transform scale-y-0" 
                            class="p-4 border-t origin-top"
                        >
                            <?php if (!empty($row['jenis_kelamin'])): ?>
                                <p><strong>Jenis Kelamin:</strong> <?php echo htmlspecialchars($row['jenis_kelamin']); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($row['domisili'])): ?>
                                <p><strong>Domisili:</strong> <?php echo htmlspecialchars($row['domisili']); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($row['nama_ayah'])): ?>
                                <p><strong>Ayah:</strong> <?php echo htmlspecialchars($row['nama_ayah']); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($row['nama_ibu'])): ?>
                                <p><strong>Ibu:</strong> <?php echo htmlspecialchars($row['nama_ibu']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($row['jenis_kelamin'] === 'Laki-laki'): ?>
                                <?php
                                $istri = array_filter([
                                    $row['nama_istri_1'],
                                    $row['nama_istri_2'],
                                    $row['nama_istri_3']
                                ]);
                                if (!empty($istri)): ?>
                                    <p><strong>Istri:</strong></p>
                                    <?php foreach ($istri as $nama_istri): ?>
                                        <p><?php echo htmlspecialchars($nama_istri); ?></p>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php elseif ($row['jenis_kelamin'] === 'Perempuan' && !empty($row['nama_suami'])): ?>
                                <p><strong>Suami:</strong></p>
                                <?php
                                $suami_list = explode(',', $row['nama_suami']);
                                foreach ($suami_list as $nama_suami): ?>
                                    <p><?php echo htmlspecialchars(trim($nama_suami)); ?></p>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (!empty($row['nama_anak'])): ?>
                                <p><strong>Anak:</strong></p>
                                <?php 
                                $anak_list = explode(',', $row['nama_anak']);
                                foreach ($anak_list as $nama_anak): ?>
                                    <p><?php echo htmlspecialchars(trim($nama_anak)); ?></p>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-8">
                    <p class="text-gray-500">Tidak ada anggota keluarga yang ditemukan<?php echo $search ? ' untuk pencarian "'.htmlspecialchars($search).'"' : ''; ?>.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-8 flex justify-center items-center space-x-2">
            <?php if ($total_pages > 1): ?>
                <div class="flex items-center space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $generation ? '&generation='.urlencode($generation) : ''; ?>" 
                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                            &laquo;
                        </a>
                        <a href="?page=<?php echo $page-1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $generation ? '&generation='.urlencode($generation) : ''; ?>" 
                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                            &lsaquo;
                        </a>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);

                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $generation ? '&generation='.urlencode($generation) : ''; ?>" 
                        class="px-3 py-1 <?php echo $i == $page ? 'bg-blue-600' : 'bg-blue-500'; ?> text-white rounded hover:bg-blue-600">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $generation ? '&generation='.urlencode($generation) : ''; ?>" 
                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                            &rsaquo;
                        </a>
                        <a href="?page=<?php echo $total_pages; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $generation ? '&generation='.urlencode($generation) : ''; ?>" 
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

    <footer id="footer-static" class="bg-blue-500 text-white py-4 mt-auto">
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