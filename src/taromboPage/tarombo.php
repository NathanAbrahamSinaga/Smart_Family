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

// Function to get members of a specific generation
function getMembersByGeneration($conn, $generation) {
    $sql = "SELECT id, nama, id_orang_tua_1, id_orang_tua_2 FROM anggota WHERE generasi = ? ORDER BY nama";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $generation);
    $stmt->execute();
    $result = $stmt->get_result();
    $members = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $members;
}

// Get all generations
$sql = "SELECT DISTINCT generasi FROM anggota ORDER BY generasi";
$result = $conn->query($sql);
$generations = $result->fetch_all(MYSQLI_ASSOC);

// Get selected generation (default to the first generation if not set)
$selectedGeneration = isset($_GET['generation']) ? intval($_GET['generation']) : $generations[0]['generasi'];

// Get members of the selected generation
$members = getMembersByGeneration($conn, $selectedGeneration);

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarombo - Smart Family</title>
    <link rel="stylesheet" href="../../assets/css/output.css">
</head>
<body class="bg-gray-100 flex">
    <!-- Sidebar -->
    <nav class="bg-blue-500 text-white w-64 min-h-screen p-5">
        <h1 class="text-2xl font-bold mb-6">Sundut (Generasi)</h1>
        <?php foreach ($generations as $gen): ?>
            <a href="?generation=<?php echo $gen['generasi']; ?>" 
               class="block py-2 px-4 mb-2 <?php echo $gen['generasi'] == $selectedGeneration ? 'bg-blue-700' : 'hover:bg-blue-600'; ?> rounded">
                Generasi <?php echo $gen['generasi']; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 p-10">
        <h2 class="text-3xl font-bold mb-6">Generasi <?php echo $selectedGeneration; ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($members as $member): ?>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-2">
                        <a href="kumpulanProfile.php?search=<?php echo urlencode($member['nama']); ?>" 
                           class="text-blue-600 hover:underline">
                            <?php echo htmlspecialchars($member['nama']); ?>
                        </a>
                    </h3>
                    <p class="text-gray-600">
                        <?php
                        // Get parent names
                        $parentNames = [];
                        if ($member['id_orang_tua_1']) {
                            $parentNames[] = getParentName($conn, $member['id_orang_tua_1']);
                        }
                        if ($member['id_orang_tua_2']) {
                            $parentNames[] = getParentName($conn, $member['id_orang_tua_2']);
                        }
                        if (!empty($parentNames)) {
                            echo "Orang Tua: " . implode(", ", $parentNames) . "<br>";
                        }

                        // Get children names
                        $childrenNames = getChildrenNames($conn, $member['id']);
                        if (!empty($childrenNames)) {
                            echo "Anak: " . implode(", ", $childrenNames);
                        }
                        ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>

<?php
function getParentName($conn, $parentId) {
    $sql = "SELECT nama FROM anggota WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $parentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $parent = $result->fetch_assoc();
    $stmt->close();
    return $parent ? $parent['nama'] : 'Unknown';
}

function getChildrenNames($conn, $parentId) {
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