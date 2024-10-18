<?php
session_start();
require_once '../../server/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Add new member
                $stmt = $conn->prepare("INSERT INTO anggota (nama, jenis_kelamin, generasi, domisili, id_ayah, id_ibu, id_istri_1, id_istri_2, id_istri_3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                // Prepare the parameters
                $nama = $_POST['nama'];
                $jenis_kelamin = $_POST['jenis_kelamin'];
                $generasi = $_POST['generasi'];
                $domisili = $_POST['domisili'];
                $id_ayah = !empty($_POST['id_ayah']) ? $_POST['id_ayah'] : null;
                $id_ibu = !empty($_POST['id_ibu']) ? $_POST['id_ibu'] : null;
                $id_istri_1 = !empty($_POST['id_istri_1']) ? $_POST['id_istri_1'] : null;
                $id_istri_2 = !empty($_POST['id_istri_2']) ? $_POST['id_istri_2'] : null;
                $id_istri_3 = !empty($_POST['id_istri_3']) ? $_POST['id_istri_3'] : null;

                $stmt->bind_param("ssissiiii", $nama, $jenis_kelamin, $generasi, $domisili, $id_ayah, $id_ibu, $id_istri_1, $id_istri_2, $id_istri_3);
                
                if ($stmt->execute()) {
                    $message = "Anggota baru berhasil ditambahkan.";
                } else {
                    $message = "Error: " . $stmt->error;
                }
                $stmt->close();
                break;
            case 'edit':
                // Edit existing member
                $stmt = $conn->prepare("UPDATE anggota SET nama=?, jenis_kelamin=?, generasi=?, domisili=?, id_ayah=?, id_ibu=?, id_istri_1=?, id_istri_2=?, id_istri_3=? WHERE id=?");
                
                // Prepare the parameters
                $nama = $_POST['nama'];
                $jenis_kelamin = $_POST['jenis_kelamin'];
                $generasi = $_POST['generasi'];
                $domisili = $_POST['domisili'];
                $id_ayah = !empty($_POST['id_ayah']) ? $_POST['id_ayah'] : null;
                $id_ibu = !empty($_POST['id_ibu']) ? $_POST['id_ibu'] : null;
                $id_istri_1 = !empty($_POST['id_istri_1']) ? $_POST['id_istri_1'] : null;
                $id_istri_2 = !empty($_POST['id_istri_2']) ? $_POST['id_istri_2'] : null;
                $id_istri_3 = !empty($_POST['id_istri_3']) ? $_POST['id_istri_3'] : null;
                $id = $_POST['id'];

                $stmt->bind_param("ssissiiiii", $nama, $jenis_kelamin, $generasi, $domisili, $id_ayah, $id_ibu, $id_istri_1, $id_istri_2, $id_istri_3, $id);
                
                if ($stmt->execute()) {
                    $message = "Data anggota berhasil diperbarui.";
                } else {
                    $message = "Error: " . $stmt->error;
                }
                $stmt->close();
                break;
        }
    }
}

// Fetch all members grouped by generation
$result = $conn->query("SELECT * FROM anggota ORDER BY generasi, nama");
$members = [];
while ($row = $result->fetch_assoc()) {
    $members[$row['generasi']][] = $row;
}
ksort($members); // Sort generations in ascending order
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Tarombo - Smart Family</title>
    <link rel="stylesheet" href="../../assets/css/output.css">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <h1 class="text-3xl font-bold mb-6">Admin Tarombo</h1>
    
    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo $message; ?></span>
        </div>
    <?php endif; ?>

    <!-- Add New Member Form -->
    <form action="" method="POST" class="bg-white p-6 rounded shadow mb-8">
        <h2 class="text-xl font-bold mb-4">Tambah Anggota Baru</h2>
        <input type="hidden" name="action" value="add">
        <div class="grid grid-cols-2 gap-4">
            <input type="text" name="nama" placeholder="Nama" required class="p-2 border rounded">
            <select name="jenis_kelamin" required class="p-2 border rounded">
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
            </select>
            <input type="number" name="generasi" placeholder="Generasi" required class="p-2 border rounded">
            <input type="text" name="domisili" placeholder="Domisili" class="p-2 border rounded">
            <input type="number" name="id_ayah" placeholder="ID Ayah" class="p-2 border rounded">
            <input type="number" name="id_ibu" placeholder="ID Ibu" class="p-2 border rounded">
            <input type="number" name="id_istri_1" placeholder="ID Istri 1" class="p-2 border rounded">
            <input type="number" name="id_istri_2" placeholder="ID Istri 2" class="p-2 border rounded">
            <input type="number" name="id_istri_3" placeholder="ID Istri 3" class="p-2 border rounded">
        </div>
        <button type="submit" class="mt-4 bg-blue-500 text-white p-2 rounded">Tambah Anggota</button>
    </form>

    <!-- List of Members -->
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Daftar Anggota</h2>
        <?php foreach ($members as $generation => $generationMembers): ?>
            <h3 class="text-lg font-semibold mt-6 mb-2">Generasi <?php echo $generation; ?></h3>
            <table class="w-full mb-4">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Jenis Kelamin</th>
                        <th>Domisili</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($generationMembers as $member): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['id']); ?></td>
                            <td><?php echo htmlspecialchars($member['nama']); ?></td>
                            <td><?php echo htmlspecialchars($member['jenis_kelamin']); ?></td>
                            <td><?php echo htmlspecialchars($member['domisili']); ?></td>
                            <td>
                                <button onclick="editMember(<?php echo $member['id']; ?>)" class="bg-yellow-500 text-white p-1 rounded">Edit</button>
                                <form action="" method="POST" class="inline-block">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                                    <button type="submit" class="bg-red-500 text-white p-1 rounded" onclick="return confirm('Anda yakin ingin menghapus anggota ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    </div>

    <!-- Edit Member Modal (hidden by default) -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold mb-4">Edit Anggota</h3>
            <form id="editForm" action="" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-4">
                    <input type="text" name="nama" id="edit_nama" placeholder="Nama" required class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <select name="jenis_kelamin" id="edit_jenis_kelamin" required class="w-full p-2 border rounded">
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
                <div class="mb-4">
                    <input type="number" name="generasi" id="edit_generasi" placeholder="Generasi" required class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <input type="text" name="domisili" id="edit_domisili" placeholder="Domisili" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <input type="number" name="id_ayah" id="edit_id_ayah" placeholder="ID Ayah" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <input type="number" name="id_ibu" id="edit_id_ibu" placeholder="ID Ibu" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <input type="number" name="id_istri_1" id="edit_id_istri_1" placeholder="ID Istri 1" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <input type="number" name="id_istri_2" id="edit_id_istri_2" placeholder="ID Istri 2" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <input type="number" name="id_istri_3" id="edit_id_istri_3" placeholder="ID Istri 3" class="w-full p-2 border rounded">
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-500 text-white p-2 rounded mr-2">Batal</button>
                    <button type="submit" class="bg-blue-500 text-white p-2 rounded">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editMember(id) {
            // Fetch member data and populate the form
            fetch(`get_member.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_nama').value = data.nama;
                    document.getElementById('edit_jenis_kelamin').value = data.jenis_kelamin;
                    document.getElementById('edit_generasi').value = data.generasi;
                    document.getElementById('edit_domisili').value = data.domisili;
                    document.getElementById('edit_id_ayah').value = data.id_ayah;
                    document.getElementById('edit_id_ibu').value = data.id_ibu;
                    document.getElementById('edit_id_istri_1').value = data.id_istri_1;
                    document.getElementById('edit_id_istri_2').value = data.id_istri_2;
                    document.getElementById('edit_id_istri_3').value = data.id_istri_3;
                    document.getElementById('editModal').style.display = 'block';
                });
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>