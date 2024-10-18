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

// Get max generation
$maxGenQuery = "SELECT MAX(generasi) as max_gen FROM anggota";
$maxGenResult = $conn->query($maxGenQuery);
$maxGen = $maxGenResult->fetch_assoc()['max_gen'];

// Get members of selected generation
$selectedGen = isset($_GET['sundut']) ? intval($_GET['sundut']) : 1;
$membersQuery = "SELECT id, nama FROM anggota WHERE generasi = ? ORDER BY nama";
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
    <link rel="stylesheet" href="../../assets/css/output.css">
</head>
<body class="bg-gray-100 flex">
    <!-- Sidebar -->
    <nav class="bg-blue-500 text-white w-64 min-h-screen p-5">
        <h1 class="text-2xl font-bold mb-8">Tarombo</h1>
        <ul>
            <?php for ($i = 1; $i <= $maxGen; $i++): ?>
                <li class="mb-2">
                    <a href="?sundut=<?php echo $i; ?>" class="block p-2 hover:bg-blue-600 rounded <?php echo $selectedGen == $i ? 'bg-blue-700' : ''; ?>">
                        Sundut <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 p-10">
        <h2 class="text-3xl font-bold mb-6">Anggota Sundut <?php echo $selectedGen; ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php while ($member = $membersResult->fetch_assoc()): ?>
                <a href="kumpulanProfile.php?id=<?php echo $member['id']; ?>" class="bg-white p-4 rounded shadow hover:shadow-md transition">
                    <?php echo htmlspecialchars($member['nama']); ?>
                </a>
            <?php endwhile; ?>
        </div>
    </main>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>