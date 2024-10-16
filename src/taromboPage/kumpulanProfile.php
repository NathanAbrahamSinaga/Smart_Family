<?php
session_start();
require_once '../../server/config.php';

// Pastikan user sudah login
if (!isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "src/loginPage/loginTarombo.php");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get all members or search results
function getMembers($conn, $search = '') {
    $sql = "SELECT a.*, 
                   i1.nama AS nama_istri_1, 
                   i2.nama AS nama_istri_2, 
                   o1.nama AS nama_orang_tua_1, 
                   o2.nama AS nama_orang_tua_2
            FROM anggota a
            LEFT JOIN anggota i1 ON a.id_istri_1 = i1.id
            LEFT JOIN anggota i2 ON a.id_istri_2 = i2.id
            LEFT JOIN anggota o1 ON a.id_orang_tua_1 = o1.id
            LEFT JOIN anggota o2 ON a.id_orang_tua_2 = o2.id
            WHERE a.nama LIKE ?
            ORDER BY a.nama";
    $stmt = $conn->prepare($sql);
    $searchParam = "%$search%";
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
    $stmt->close();
    return $members;
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$members = getMembers($conn, $search);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumpulan Profile - Smart Family</title>
    <link rel="stylesheet" href="../../assets/css/output.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-semibold mb-6">Kumpulan Profile Anggota Keluarga</h1>

        <!-- Search Form -->
        <form action="" method="GET" class="mb-8">
            <div class="flex">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nama anggota..." class="flex-grow p-2 border rounded-l">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r">Cari</button>
            </div>
        </form>

        <!-- Members Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($members as $member): ?>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($member['nama']); ?></h2>
                    <img src="<?php echo $member['foto_url'] ? htmlspecialchars($member['foto_url']) : '/path/to/default/image.jpg'; ?>" alt="<?php echo htmlspecialchars($member['nama']); ?>" class="w-full h-48 object-cover mb-4 rounded">
                    <p><strong>Jenis Kelamin:</strong> <?php echo htmlspecialchars($member['jenis_kelamin']); ?></p>
                    <p><strong>Domisili:</strong> <?php echo htmlspecialchars($member['domisili']); ?></p>
                    <p><strong>Generasi:</strong> <?php echo htmlspecialchars($member['generasi']); ?></p>
                    <?php if ($member['nama_istri_1'] || $member['nama_istri_2']): ?>
                        <p><strong>Istri:</strong> 
                            <?php 
                            echo htmlspecialchars($member['nama_istri_1'] ?? '');
                            echo $member['nama_istri_1'] && $member['nama_istri_2'] ? ', ' : '';
                            echo htmlspecialchars($member['nama_istri_2'] ?? '');
                            ?>
                        </p>
                    <?php endif; ?>
                    <?php if ($member['nama_orang_tua_1'] || $member['nama_orang_tua_2']): ?>
                        <p><strong>Orang Tua:</strong> 
                            <?php 
                            echo htmlspecialchars($member['nama_orang_tua_1'] ?? '');
                            echo $member['nama_orang_tua_1'] && $member['nama_orang_tua_2'] ? ', ' : '';
                            echo htmlspecialchars($member['nama_orang_tua_2'] ?? '');
                            ?>
                        </p>
                    <?php endif; ?>
                    <!-- You can add more details here if needed -->
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>