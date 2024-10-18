<?php
session_start();
require_once '../../server/config.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "src/loginPage/loginTarombo.php");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil generasi maksimal
$maxGenQuery = "SELECT MAX(generasi) as max_gen FROM anggota";
$maxGenResult = $conn->query($maxGenQuery);
$maxGen = $maxGenResult->fetch_assoc()['max_gen'];

// Ambil anggota dari generasi yang dipilih
$selectedGen = isset($_GET['sundut']) ? intval($_GET['sundut']) : 1;
$membersQuery = "SELECT id, nama, jenis_kelamin, id_istri_1, id_istri_2, id_istri_3, id_ayah FROM anggota WHERE generasi = ? ORDER BY nama";
$stmt = $conn->prepare($membersQuery);
$stmt->bind_param("i", $selectedGen);
$stmt->execute();
$membersResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarombo - Smart Family</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/output.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-blue-500 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-semibold ml-5">Smart Family - Tarombo</h1>
            <div>
                <span class="mr-4">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <a href="../loginPage/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded mr-5">Logout</a>
            </div>
        </div>
    </header>

    <!-- Container Utama -->
    <main class="container mx-auto mt-12 px-4">
        <!-- Pilihan Generasi -->
        <div class="flex justify-between items-center mb-10">
            <h2 class="text-3xl font-bold text-gray-800">Anggota Sundut <?php echo $selectedGen; ?></h2>
            <div>
                <select onchange="location = this.value;" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded shadow focus:outline-none">
                    <?php for ($i = 1; $i <= $maxGen; $i++): ?>
                        <option value="?sundut=<?php echo $i; ?>" <?php echo $selectedGen == $i ? 'selected' : ''; ?>>
                            Sundut <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>

        <!-- Daftar Anggota -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if ($membersResult->num_rows > 0): ?>
                <?php while ($member = $membersResult->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <h3 class="text-2xl font-semibold text-blue-800 mb-3">
                            <a href="kumpulanProfile.php?id=<?php echo $member['id']; ?>" class="hover:underline">
                                <?php echo htmlspecialchars($member['nama']); ?>
                            </a>
                        </h3>

                        <!-- Tampilkan Nama Pasangan -->
                        <?php
                        $spouseName = null;
                        if ($member['jenis_kelamin'] == 'Laki-laki') {
                            $spouseIds = [$member['id_istri_1'], $member['id_istri_2'], $member['id_istri_3']];
                            $spouseNames = [];
                            foreach ($spouseIds as $spouseId) {
                                if ($spouseId) {
                                    $spouseQuery = "SELECT nama FROM anggota WHERE id = ?";
                                    $spouseStmt = $conn->prepare($spouseQuery);
                                    $spouseStmt->bind_param("i", $spouseId);
                                    $spouseStmt->execute();
                                    $spouseResult = $spouseStmt->get_result();
                                    if ($spouseResult->num_rows > 0) {
                                        $spouseNames[] = $spouseResult->fetch_assoc()['nama'];
                                    }
                                    $spouseStmt->close();
                                }
                            }
                            if (!empty($spouseNames)) {
                                $spouseName = "Istri: " . implode(', ', $spouseNames);
                            }
                        } elseif ($member['jenis_kelamin'] == 'Perempuan' && $member['id_ayah']) {
                            $spouseQuery = "SELECT nama FROM anggota WHERE id = ?";
                            $spouseStmt = $conn->prepare($spouseQuery);
                            $spouseStmt->bind_param("i", $member['id_ayah']);
                            $spouseStmt->execute();
                            $spouseResult = $spouseStmt->get_result();
                            if ($spouseResult->num_rows > 0) {
                                $spouseName = "Suami: " . $spouseResult->fetch_assoc()['nama'];
                            }
                            $spouseStmt->close();
                        }

                        if ($spouseName) {
                            echo "<p class='text-gray-600 mt-2'>$spouseName</p>";
                        }
                        ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-700 col-span-full text-center">Tidak ada anggota di Sundut ini.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer Static -->
    <footer id="footer-static" class="bg-blue-500 text-white py-4 mt-20">
        <div class="container mx-auto text-center">
            <p>&copy; 2024 Smart Family. All rights reserved.</p>
        </div>
    </footer>

    <!-- Footer Fixed -->
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

<?php
$stmt->close();
$conn->close();
?>
