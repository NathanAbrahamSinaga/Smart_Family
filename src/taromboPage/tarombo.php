<?php
session_start();
require_once '../../server/config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "src/loginPage/loginTarombo.php");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$generation = isset($_GET['generation']) ? $conn->real_escape_string($_GET['generation']) : '';

if (isset($_GET['id']) && empty($search)) {
    $memberId = $conn->real_escape_string($_GET['id']);
    $nameQuery = "SELECT nama FROM anggota WHERE id = ?";
    $stmt = $conn->prepare($nameQuery);
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $nameResult = $stmt->get_result();
    if ($nameRow = $nameResult->fetch_assoc()) {
        $search = $nameRow['nama'];
    }
    $stmt->close();
}

$whereClause = [];
if ($search) {
    $whereClause[] = "a.nama LIKE '%$search%'";
}
if ($generation) {
    $whereClause[] = "a.generasi = '$generation'";
}

$whereClauseSql = !empty($whereClause) ? "WHERE " . implode(" AND ", $whereClause) : "";

$query = "
    SELECT a.*,
           ayah.nama as nama_ayah,
           ibu.nama as nama_ibu,
           -- Info istri untuk anggota laki-laki
           istri1.nama as nama_istri_1,
           istri2.nama as nama_istri_2,
           istri3.nama as nama_istri_3,
           -- Info suami untuk anggota perempuan
           (SELECT GROUP_CONCAT(DISTINCT suami.nama ORDER BY suami.nama ASC SEPARATOR ', ')
            FROM anggota suami 
            WHERE suami.id_istri_1 = a.id 
               OR suami.id_istri_2 = a.id 
               OR suami.id_istri_3 = a.id) as nama_suami,
           GROUP_CONCAT(DISTINCT anak.nama ORDER BY anak.nama ASC SEPARATOR ', ') as nama_anak
    FROM anggota a
    LEFT JOIN anggota ayah ON a.id_ayah = ayah.id
    LEFT JOIN anggota ibu ON a.id_ibu = ibu.id
    LEFT JOIN anggota istri1 ON a.id_istri_1 = istri1.id
    LEFT JOIN anggota istri2 ON a.id_istri_2 = istri2.id
    LEFT JOIN anggota istri3 ON a.id_istri_3 = istri3.id
    LEFT JOIN anggota anak ON anak.id_ayah = a.id OR anak.id_ibu = a.id
    $whereClauseSql
    GROUP BY a.id
    ORDER BY a.nama
";

$result = $conn->query($query);

$generationQuery = "SELECT DISTINCT generasi FROM anggota ORDER BY generasi";
$generationResult = $conn->query($generationQuery);
$generations = [];
while ($row = $generationResult->fetch_assoc()) {
    $generations[] = $row['generasi'];
}
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
            <h1 class="text-xl font-semibold ml-5">Tarombo</h1>
            <div>
                <span class="mr-4"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <a href="../loginPage/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded mr-5">Logout</a>
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
                <?php if ($search || $generation): ?>
                    <a href="?" class="text-blue-500 hover:underline text-sm mt-1 inline-block">Reset pencarian</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div x-data="{ open: false }" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <div class="p-4">
                            <img 
                                src="<?php 
                                    if ($row['foto']) {
                                        echo '../../' . htmlspecialchars($row['foto']);
                                    } else {
                                        echo ($row['jenis_kelamin'] === 'Laki-laki') 
                                            ? '../../assets/img/default_male.jpg' 
                                            : '../../assets/img/default_female.jpg';
                                    }
                                ?>" 
                            alt="<?php echo htmlspecialchars($row['nama']); ?>" 
                            class="w-32 h-32 rounded-full mx-auto mb-4 object-cover shadow-lg">
                            <h3 class="text-xl font-semibold text-center mb-2"><?php echo htmlspecialchars($row['nama']); ?></h3>
                            <p class="text-gray-600 text-center mb-4"><?php echo htmlspecialchars($row['generasi']); ?> Generation</p>
                            <button @click="open = !open" class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600 transition duration-300 ease-in-out">
                                Lihat Detail
                            </button>
                        </div>
                        <div x-show="open" class="p-4 border-t">
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
                                    <p><strong>Istri:</strong> <?php echo htmlspecialchars(implode(', ', $istri)); ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if (!empty($row['nama_suami'])): ?>
                                    <p><strong>Suami:</strong> <?php echo htmlspecialchars($row['nama_suami']); ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if (!empty($row['nama_anak'])): ?>
                                <p><strong>Anak:</strong> <?php echo htmlspecialchars($row['nama_anak']); ?></p>
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

        <div class="mt-8">
            <a href="../../index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-md transition duration-300 ease-in-out">Kembali ke Tarombo</a>
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