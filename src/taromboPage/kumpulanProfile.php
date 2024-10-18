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
$whereClause = $search ? "WHERE a.nama LIKE '%$search%'" : "";

$query = "
    SELECT a.*, 
           ayah.nama as nama_ayah, 
           ibu.nama as nama_ibu, 
           istri1.nama as nama_istri_1,
           istri2.nama as nama_istri_2,
           istri3.nama as nama_istri_3,
           GROUP_CONCAT(DISTINCT anak.nama ORDER BY anak.nama ASC SEPARATOR ', ') as nama_anak
    FROM anggota a
    LEFT JOIN anggota ayah ON a.id_ayah = ayah.id
    LEFT JOIN anggota ibu ON a.id_ibu = ibu.id
    LEFT JOIN anggota istri1 ON a.id_istri_1 = istri1.id
    LEFT JOIN anggota istri2 ON a.id_istri_2 = istri2.id
    LEFT JOIN anggota istri3 ON a.id_istri_3 = istri3.id
    LEFT JOIN anggota anak ON anak.id_ayah = a.id OR anak.id_ibu = a.id
    $whereClause
    GROUP BY a.id
    ORDER BY a.nama
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumpulan Profile - Smart Family</title>
    <link rel="stylesheet" href="../../assets/css/output.css">
</head>
<body class="bg-gray-100 p-8">
    <h1 class="text-3xl font-bold mb-6">Kumpulan Profile Keluarga</h1>
    
    <!-- Search Form -->
    <form action="" method="GET" class="mb-8">
        <input type="text" name="search" placeholder="Cari anggota keluarga..." value="<?php echo htmlspecialchars($search); ?>" class="p-2 border rounded">
        <button type="submit" class="bg-blue-500 text-white p-2 rounded">Cari</button>
    </form>

    <!-- Profile Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="bg-white p-6 rounded shadow">
                <img src="<?php echo $row['foto'] ? htmlspecialchars($row['foto']) : '../../assets/img/profile_pictures/default.jpg'; ?>" alt="<?php echo htmlspecialchars($row['nama']); ?>" class="w-32 h-32 rounded-full mx-auto mb-4">
                <h2 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($row['nama']); ?></h2>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <p class="font-semibold">Jenis Kelamin:</p>
                    <p><?php echo htmlspecialchars($row['jenis_kelamin']); ?></p>
                    <p class="font-semibold">Generasi:</p>
                    <p><?php echo htmlspecialchars($row['generasi']); ?></p>
                    <p class="font-semibold">Domisili:</p>
                    <p><?php echo htmlspecialchars($row['domisili']); ?></p>
                    <p class="font-semibold">Ayah:</p>
                    <p><?php echo htmlspecialchars($row['nama_ayah'] ?? 'Tidak diketahui'); ?></p>
                    <p class="font-semibold">Ibu:</p>
                    <p><?php echo htmlspecialchars($row['nama_ibu'] ?? 'Tidak diketahui'); ?></p>
                    <p class="font-semibold">Pasangan:</p>
                    <p>
                        <?php
                        $pasangan = array_filter([$row['nama_istri_1'], $row['nama_istri_2'], $row['nama_istri_3']]);
                        echo $pasangan ? htmlspecialchars(implode(', ', $pasangan)) : 'Tidak ada';
                        ?>
                    </p>
                    <p class="font-semibold">Anak:</p>
                    <p><?php echo $row['nama_anak'] ? htmlspecialchars($row['nama_anak']) : 'Tidak ada'; ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>