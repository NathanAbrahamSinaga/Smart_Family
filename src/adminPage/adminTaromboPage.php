<?php
session_start();
require_once '../../server/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fungsi untuk mendapatkan semua anggota
function getAllMembers($conn) {
    $sql = "SELECT * FROM anggota ORDER BY generasi, nama";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fungsi untuk mendapatkan generasi tertinggi
function getHighestGeneration($conn) {
    $sql = "SELECT MAX(generasi) as max_gen FROM anggota";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['max_gen'];
}

$members = getAllMembers($conn);
$highestGeneration = getHighestGeneration($conn);

// Handle member addition/editing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_member'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $domisili = $_POST['domisili'];
    $generasi = intval($_POST['generasi']);
    $foto_url = $_POST['foto_url'];
    $id_istri_1 = !empty($_POST['id_istri_1']) ? intval($_POST['id_istri_1']) : null;
    $id_istri_2 = !empty($_POST['id_istri_2']) ? intval($_POST['id_istri_2']) : null;
    $id_orang_tua_1 = !empty($_POST['id_orang_tua_1']) ? intval($_POST['id_orang_tua_1']) : null;
    $id_orang_tua_2 = !empty($_POST['id_orang_tua_2']) ? intval($_POST['id_orang_tua_2']) : null;

    if ($id) {
        // Update existing member
        $sql = "UPDATE anggota SET nama = ?, jenis_kelamin = ?, domisili = ?, generasi = ?, foto_url = ?, 
                id_istri_1 = ?, id_istri_2 = ?, id_orang_tua_1 = ?, id_orang_tua_2 = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssissiiiii", $nama, $jenis_kelamin, $domisili, $generasi, $foto_url, 
                          $id_istri_1, $id_istri_2, $id_orang_tua_1, $id_orang_tua_2, $id);
    } else {
        // Add new member
        $sql = "INSERT INTO anggota (nama, jenis_kelamin, domisili, generasi, foto_url, id_istri_1, id_istri_2, id_orang_tua_1, id_orang_tua_2) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssissiii", $nama, $jenis_kelamin, $domisili, $generasi, $foto_url, 
                          $id_istri_1, $id_istri_2, $id_orang_tua_1, $id_orang_tua_2);
    }

    if ($stmt->execute()) {
        $successMessage = $id ? "Member updated successfully." : "New member added successfully.";
    } else {
        $errorMessage = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle member deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM anggota WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $successMessage = "Member deleted successfully.";
    } else {
        $errorMessage = "Error deleting member: " . $stmt->error;
    }
    $stmt->close();
}

// Handle adding a new generation
if (isset($_POST['add_generation'])) {
    $newGeneration = $highestGeneration + 1;
    $sql = "INSERT INTO anggota (nama, jenis_kelamin, generasi) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $placeholder = "Placeholder Member";
    $gender = "Laki-laki";
    $stmt->bind_param("ssi", $placeholder, $gender, $newGeneration);
    if ($stmt->execute()) {
        $successMessage = "New generation added successfully.";
        $highestGeneration = $newGeneration;
    } else {
        $errorMessage = "Error adding new generation: " . $stmt->error;
    }
    $stmt->close();
}

// Handle deleting the highest generation
if (isset($_POST['delete_generation'])) {
    $sql = "DELETE FROM anggota WHERE generasi = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $highestGeneration);
    if ($stmt->execute()) {
        $successMessage = "Highest generation deleted successfully.";
        $highestGeneration--;
    } else {
        $errorMessage = "Error deleting highest generation: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch member data for editing
$editMember = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $sql = "SELECT * FROM anggota WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editMember = $result->fetch_assoc();
    $stmt->close();
}

$members = getAllMembers($conn);
$highestGeneration = getHighestGeneration($conn);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Tarombo - Smart Family</title>
    <link rel="stylesheet" href="../../assets/css/output.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-semibold mb-6">Admin Tarombo</h1>

        <!-- Add/Edit Member Form -->
        <form action="" method="POST" class="mb-8 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Add/Edit Member</h2>
            <!-- Add form fields for member details here -->
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save Member</button>
        </form>

        <!-- Generation Management -->
        <div class="mb-8 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Manage Generations</h2>
            <p>Current highest generation: <?php echo $highestGeneration; ?></p>
            <form action="" method="POST" class="mt-4">
                <button type="submit" name="add_generation" class="bg-green-500 text-white px-4 py-2 rounded mr-2">Add Generation</button>
                <button type="submit" name="delete_generation" class="bg-red-500 text-white px-4 py-2 rounded">Delete Highest Generation</button>
            </form>
        </div>

        <!-- Members List -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Members List</h2>
            <?php foreach (range(1, $highestGeneration) as $gen): ?>
                <h3 class="text-lg font-semibold mt-4 mb-2">Generation <?php echo $gen; ?></h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($members as $member): ?>
                        <?php if ($member['generasi'] == $gen): ?>
                            <div class="bg-gray-100 p-4 rounded">
                                <h4 class="font-semibold"><?php echo htmlspecialchars($member['nama']); ?></h4>
                                <p>Gender: <?php echo htmlspecialchars($member['jenis_kelamin']); ?></p>
                                <p>Domisili: <?php echo htmlspecialchars($member['domisili']); ?></p>
                                <div class="mt-2">
                                    <a href="?edit=<?php echo $member['id']; ?>" class="text-blue-500 hover:underline mr-2">Edit</a>
                                    <a href="?delete=<?php echo $member['id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure you want to delete this member?');">Delete</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add/Edit Member Form -->
    <form action="" method="POST" class="mb-8 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4"><?php echo $editMember ? 'Edit Member' : 'Add New Member'; ?></h2>
        <?php if ($editMember): ?>
            <input type="hidden" name="id" value="<?php echo $editMember['id']; ?>">
        <?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="nama" class="block mb-1">Name</label>
                <input type="text" id="nama" name="nama" required class="w-full p-2 border rounded" value="<?php echo $editMember ? htmlspecialchars($editMember['nama']) : ''; ?>">
            </div>
            <div>
                <label for="jenis_kelamin" class="block mb-1">Gender</label>
                <select id="jenis_kelamin" name="jenis_kelamin" required class="w-full p-2 border rounded">
                    <option value="Laki-laki" <?php echo $editMember && $editMember['jenis_kelamin'] == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                    <option value="Perempuan" <?php echo $editMember && $editMember['jenis_kelamin'] == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                </select>
            </div>
            <div>
                <label for="domisili" class="block mb-1">Domicile</label>
                <input type="text" id="domisili" name="domisili" class="w-full p-2 border rounded" value="<?php echo $editMember ? htmlspecialchars($editMember['domisili']) : ''; ?>">
            </div>
            <div>
                <label for="generasi" class="block mb-1">Generation</label>
                <input type="number" id="generasi" name="generasi" required class="w-full p-2 border rounded" min="1" max="<?php echo $highestGeneration; ?>" value="<?php echo $editMember ? $editMember['generasi'] : ''; ?>">
            </div>
            <div>
                <label for="foto_url" class="block mb-1">Photo URL</label>
                <input type="text" id="foto_url" name="foto_url" class="w-full p-2 border rounded" value="<?php echo $editMember ? htmlspecialchars($editMember['foto_url']) : ''; ?>">
            </div>
            <!-- Add more fields for id_istri_1, id_istri_2, id_orang_tua_1, id_orang_tua_2 -->
        </div>
        <button type="submit" name="save_member" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">Save Member</button>
    </form>

    <script>
        // You can add JavaScript here for dynamic form handling if needed
    </script>
</body>
</html>

<?php
$conn->close();
?>