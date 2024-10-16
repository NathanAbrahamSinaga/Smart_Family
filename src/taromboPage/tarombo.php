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

// Fungsi untuk mendapatkan generasi
function getGenerations($conn) {
    $sql = "SELECT DISTINCT generasi FROM anggota ORDER BY generasi";
    $result = $conn->query($sql);
    $generations = [];
    while ($row = $result->fetch_assoc()) {
        $generations[] = $row['generasi'];
    }
    return $generations;
}

// Fungsi untuk mendapatkan anggota berdasarkan generasi
function getMembersByGeneration($conn, $generation) {
    $sql = "SELECT id, nama, id_orang_tua_1, id_orang_tua_2 FROM anggota WHERE generasi = ? ORDER BY nama";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $generation);
    $stmt->execute();
    $result = $stmt->get_result();
    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
    $stmt->close();
    return $members;
}

$generations = getGenerations($conn);
$selectedGeneration = isset($_GET['generation']) ? intval($_GET['generation']) : $generations[0];
$members = getMembersByGeneration($conn, $selectedGeneration);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarombo - Smart Family</title>
    <link rel="stylesheet" href="../../assets/css/output.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-blue-500 min-h-screen p-4">
            <h2 class="text-white text-xl font-semibold mb-4">Sundut (Generasi)</h2>
            <nav>
                <?php foreach ($generations as $gen): ?>
                    <a href="?generation=<?php echo $gen; ?>" class="block py-2 px-4 text-white hover:bg-blue-600 rounded <?php echo $gen == $selectedGeneration ? 'bg-blue-600' : ''; ?>">
                        Generasi <?php echo $gen; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <h1 class="text-3xl font-semibold mb-6">Generasi <?php echo $selectedGeneration; ?></h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($members as $member): ?>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold mb-2">
                            <a href="kumpulanProfile.php?search=<?php echo urlencode($member['nama']); ?>" class="text-blue-600 hover:underline">
                                <?php echo htmlspecialchars($member['nama']); ?>
                            </a>
                        </h3>
                        <p class="text-gray-600">
                            <?php
                            // Fetch and display parent names
                            if ($member['id_orang_tua_1']) {
                                $parentQuery = "SELECT nama FROM anggota WHERE id IN (?, ?)";
                                $stmt = $conn->prepare($parentQuery);
                                $stmt->bind_param("ii", $member['id_orang_tua_1'], $member['id_orang_tua_2']);
                                $stmt->execute();
                                $parentResult = $stmt->get_result();
                                $parents = $parentResult->fetch_all(MYSQLI_ASSOC);
                                $stmt->close();

                                if (!empty($parents)) {
                                    echo "Orang Tua: " . implode(", ", array_column($parents, 'nama'));
                                }
                            }
                            ?>
                        </p>
                        <!-- You can add more details here if needed -->
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>

<?php
$conn->close();
?>