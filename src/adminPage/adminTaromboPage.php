<?php
session_start();
require_once '../../server/config.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["username"])) {
    header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get all generations
function getGenerations($conn) {
    $sql = "SELECT DISTINCT generasi FROM anggota ORDER BY generasi";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to get members of a specific generation
function getMembersByGeneration($conn, $generation) {
    $sql = "SELECT id, nama FROM anggota WHERE generasi = ? ORDER BY nama";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $generation);
    $stmt->execute();
    $result = $stmt->get_result();
    $members = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $members;
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_generation'])) {
        $newGeneration = $conn->real_escape_string($_POST['new_generation']);
        $sql = "INSERT INTO anggota (nama, jenis_kelamin, generasi) VALUES ('Placeholder', 'Laki-laki', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $newGeneration);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete_generation'])) {
        $generationToDelete = $conn->real_escape_string($_POST['generation_to_delete']);
        $sql = "DELETE FROM anggota WHERE generasi = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $generationToDelete);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['add_member'])) {
        $nama = $conn->real_escape_string($_POST['nama']);
        $jenis_kelamin = $conn->real_escape_string($_POST['jenis_kelamin']);
        $domisili = $conn->real_escape_string($_POST['domisili']);
        $generasi = $conn->real_escape_string($_POST['generasi']);
        $foto_url = $conn->real_escape_string($_POST['foto_url']);
        $id_istri_1 = $conn->real_escape_string($_POST['id_istri_1']);
        $id_istri_2 = $conn->real_escape_string($_POST['id_istri_2']);
        $id_orang_tua_1 = $conn->real_escape_string($_POST['id_orang_tua_1']);
        $id_orang_tua_2 = $conn->real_escape_string($_POST['id_orang_tua_2']);

        $sql = "INSERT INTO anggota (nama, jenis_kelamin, domisili, generasi, foto_url, id_istri_1, id_istri_2, id_orang_tua_1, id_orang_tua_2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssissiii", $nama, $jenis_kelamin, $domisili, $generasi, $foto_url, $id_istri_1, $id_istri_2, $id_orang_tua_1, $id_orang_tua_2);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['edit_member'])) {
        $id = $conn->real_escape_string($_POST['id']);
        $nama = $conn->real_escape_string($_POST['nama']);
        $jenis_kelamin = $conn->real_escape_string($_POST['jenis_kelamin']);
        $domisili = $conn->real_escape_string($_POST['domisili']);
        $generasi = $conn->real_escape_string($_POST['generasi']);
        $foto_url = $conn->real_escape_string($_POST['foto_url']);
        $id_istri_1 = $conn->real_escape_string($_POST['id_istri_1']);
        $id_istri_2 = $conn->real_escape_string($_POST['id_istri_2']);
        $id_orang_tua_1 = $conn->real_escape_string($_POST['id_orang_tua_1']);
        $id_orang_tua_2 = $conn->real_escape_string($_POST['id_orang_tua_2']);

        $sql = "UPDATE anggota SET nama=?, jenis_kelamin=?, domisili=?, generasi=?, foto_url=?, id_istri_1=?, id_istri_2=?, id_orang_tua_1=?, id_orang_tua_2=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssissiiii", $nama, $jenis_kelamin, $domisili, $generasi, $foto_url, $id_istri_1, $id_istri_2, $id_orang_tua_1, $id_orang_tua_2, $id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete_member'])) {
        $id = $conn->real_escape_string($_POST['id']);
        $sql = "DELETE FROM anggota WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

$generations = getGenerations($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Tarombo - Smart Family</title>
    <link rel="stylesheet" href="../../assets/css/output.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100" x-data="{ currentGeneration: null }">
    <header class="bg-blue-500 text-white py-4">
        <div class="container mx-auto">
            <h1 class="text-3xl font-bold">Admin Tarombo</h1>
        </div>
    </header>

    <main class="container mx-auto mt-8 px-4">
        <!-- Generation Management -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Manage Generations</h2>
            <form method="POST" class="mb-4">
                <input type="number" name="new_generation" placeholder="New Generation Number" required class="p-2 border rounded mr-2">
                <button type="submit" name="add_generation" class="bg-green-500 text-white px-4 py-2 rounded">Add Generation</button>
            </form>
            <form method="POST" class="mb-4">
                <select name="generation_to_delete" required class="p-2 border rounded mr-2">
                    <?php foreach ($generations as $gen): ?>
                        <option value="<?php echo $gen['generasi']; ?>">Generation <?php echo $gen['generasi']; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="delete_generation" class="bg-red-500 text-white px-4 py-2 rounded" onclick="return confirm('Are you sure you want to delete this generation and all its members?');">Delete Generation</button>
            </form>
        </section>

        <!-- Member Management -->
        <section>
            <h2 class="text-2xl font-semibold mb-4">Manage Members</h2>
            <?php foreach ($generations as $gen): ?>
                <div x-data="{ open: false }" class="mb-4">
                    <button @click="open = !open; currentGeneration = <?php echo $gen['generasi']; ?>" class="bg-blue-500 text-white px-4 py-2 rounded w-full text-left">
                        Generation <?php echo $gen['generasi']; ?>
                    </button>
                    <div x-show="open" class="mt-2">
                        <?php
                        $members = getMembersByGeneration($conn, $gen['generasi']);
                        foreach ($members as $member):
                        ?>
                            <div class="bg-white p-4 rounded shadow mb-2">
                                <h3 class="font-semibold"><?php echo htmlspecialchars($member['nama']); ?></h3>
                                <button @click="$dispatch('open-modal', {id: <?php echo $member['id']; ?>, action: 'edit'})" class="text-blue-500">Edit</button>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                                    <button type="submit" name="delete_member" class="text-red-500" onclick="return confirm('Are you sure you want to delete this member?');">Delete</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                        <button @click="$dispatch('open-modal', {action: 'add', generation: currentGeneration})" class="bg-green-500 text-white px-4 py-2 rounded mt-2">Add Member</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    </main>

    <!-- Modal for Add/Edit Member -->
    <div x-data="{ showModal: false, modalAction: '', modalId: null, modalGeneration: null }" 
         @open-modal.window="showModal = true; modalAction = $event.detail.action; modalId = $event.detail.id; modalGeneration = $event.detail.generation"
         x-show="showModal" 
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg max-w-2xl w-full" @click.away="showModal = false">
            <h2 x-text="modalAction === 'add' ? 'Add New Member' : 'Edit Member'" class="text-2xl font-bold mb-4"></h2>
            <form method="POST">
                <input type="hidden" name="id" x-bind:value="modalId">
                <div class="mb-4">
                    <label class="block mb-2">Name:</label>
                    <input type="text" name="nama" required class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label class="block mb-2">Gender:</label>
                    <select name="jenis_kelamin" required class="w-full p-2 border rounded">
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-2">Domicile:</label>
                    <input type="text" name="domisili" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label class="block mb-2">Generation:</label>
                    <input type="number" name="generasi" x-bind:value="modalGeneration" required class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label class="block mb-2">Photo URL:</label>
                    <input type="text" name="foto_url" class="w-full p-2 border rounded">
                </div>
                <!-- Add more fields for wives and parents as needed -->
                <button type="submit" x-bind:name="modalAction === 'add' ? 'add_member' : 'edit_member'" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
                <button type="button" @click="showModal = false" class="bg-gray-500 text-white px-4 py-2 rounded ml-2">Cancel</button>
            </form>
        </div>
    </div>

    <footer class="bg-blue-500 text-white py-4 mt-8">
        <div class="container mx-auto text-center">
            <p>&copy; 2024 Smart Family. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

<?php
$conn->close();
?>