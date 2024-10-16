<?php
session_start();
require_once '../../server/config.php';

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "src/loginPage/loginTarombo.php");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Search functionality
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sql = "SELECT * FROM anggota WHERE nama LIKE '%$search%' ORDER BY nama";
$result = $conn->query($sql);

$members = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();

function getRelativeName($conn, $id) {
    if (!$id) return 'Tidak ada';
    $sql = "SELECT nama FROM anggota WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $relative = $result->fetch_assoc();
    $stmt->close();
    return $relative ? $relative['nama'] : 'Unknown';
}

function getChildren($conn, $parentId) {
    $sql = "SELECT nama FROM anggota WHERE id_orang_tua_1 = ? OR id_orang_tua_2 = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $parentId, $parentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $children = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return array_column($children, 'nama');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumpulan Profile - Smart Family</title>
    <link rel="stylesheet" href="../../assets/css/output.css">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-500 text-white py-4">
        <div class="container mx-auto">
            <h1 class="text-3xl font-bold">Kumpulan Profile Keluarga</h1>
        </div>
    </header>

    <main class="container mx-auto mt-8 px-4">
        <!-- Search Form -->
        <form action="" method="GET" class="mb-8">
            <div class="flex">
                <input type="text" name="search" placeholder="Cari anggota keluarga..." 
                       value="<?php echo htmlspecialchars($search); ?>"
                       class="flex-grow p-2 border rounded-l">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r">Cari</button>
            </div>
        </form>

        <!-- Members Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($members as $member): ?>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($member['nama']); ?></h2>
                    <img src="<?php echo $member['foto_url'] ? htmlspecialchars($member['foto_url']) : '../../assets/img/default-profile.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($member['nama']); ?>"
                         class="w-full h-48 object-cover mb-4 rounded">
                    <p><strong>Jenis Kelamin:</strong> <?php echo htmlspecialchars($member['jenis_kelamin']); ?></p>
                    <p><strong>Domisili:</strong> <?php echo htmlspecialchars($member['domisili'] ?: 'Tidak diketahui'); ?></p>
                    <p><strong>Generasi:</strong> <?php echo htmlspecialchars($member['generasi']); ?></p>
                    <p><strong>Istri 1:</strong> <?php echo htmlspecialchars(getRelativeName($conn, $member['id_istri_1'])); ?></p>
                    <p><strong>Istri 2:</strong> <?php echo htmlspecialchars(getRelativeName($conn, $member['id_istri_2'])); ?></p>
                    <p><strong>Orang Tua 1:</strong> <?php echo htmlspecialchars(getRelativeName($conn, $member['id_orang_tua_1'])); ?></p>
                    <p><strong>Orang Tua 2:</strong> <?php echo htmlspecialchars(getRelativeName($conn, $member['id_orang_tua_2'])); ?></p>
                    <p><strong>Anak:</strong> <?php echo implode(', ', getChildren($conn, $member['id'])) ?: 'Tidak ada'; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <footer class="bg-blue-500 text-white py-4 mt-8">
        <div class="container mx-auto text-center">
            <p>&copy; 2024 Smart Family. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>