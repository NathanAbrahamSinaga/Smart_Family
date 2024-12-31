<?php
session_start();
require_once '../../server/config.php';

if (!isset($_SESSION["admin_id"]) || $_SESSION["user_type"] !== "admin") {
    header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $id = $_POST['new_id'];
                $stmt = $conn->prepare("INSERT INTO anggota (id, nama, jenis_kelamin, generasi, domisili, id_ayah, id_ibu, id_istri_1, id_istri_2, id_istri_3, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $nama = $_POST['nama'];
                $jenis_kelamin = $_POST['jenis_kelamin'];
                $generasi = $_POST['generasi'];
                $domisili = $_POST['domisili'];
                $id_ayah = !empty($_POST['id_ayah']) ? $_POST['id_ayah'] : null;
                $id_ibu = !empty($_POST['id_ibu']) ? $_POST['id_ibu'] : null;
                $id_istri_1 = !empty($_POST['id_istri_1']) ? $_POST['id_istri_1'] : null;
                $id_istri_2 = !empty($_POST['id_istri_2']) ? $_POST['id_istri_2'] : null;
                $id_istri_3 = !empty($_POST['id_istri_3']) ? $_POST['id_istri_3'] : null;
                $foto = $_POST['foto'];

                $stmt->bind_param("ississiiiis", $id, $nama, $jenis_kelamin, $generasi, $domisili, $id_ayah, $id_ibu, $id_istri_1, $id_istri_2, $id_istri_3, $foto);
                
                if ($stmt->execute()) {
                    $message = "Anggota baru berhasil ditambahkan.";
                } else {
                    $message = "Error: " . $stmt->error;
                }
                $stmt->close();
                break;
            case 'edit':
                $id = $_POST['id'];
                $stmt = $conn->prepare("UPDATE anggota SET nama=?, jenis_kelamin=?, generasi=?, domisili=?, id_ayah=?, id_ibu=?, id_istri_1=?, id_istri_2=?, id_istri_3=? WHERE id=?");

                $nama = $_POST['nama'];
                $jenis_kelamin = $_POST['jenis_kelamin'];
                $generasi = $_POST['generasi'];
                $domisili = $_POST['domisili'];
                $id_ayah = !empty($_POST['id_ayah']) ? $_POST['id_ayah'] : null;
                $id_ibu = !empty($_POST['id_ibu']) ? $_POST['id_ibu'] : null;
                $id_istri_1 = !empty($_POST['id_istri_1']) ? $_POST['id_istri_1'] : null;
                $id_istri_2 = !empty($_POST['id_istri_2']) ? $_POST['id_istri_2'] : null;
                $id_istri_3 = !empty($_POST['id_istri_3']) ? $_POST['id_istri_3'] : null;

                if ($stmt->bind_param("ssisiiiiii", $nama, $jenis_kelamin, $generasi, $domisili, $id_ayah, $id_ibu, $id_istri_1, $id_istri_2, $id_istri_3, $id)) {
                    if ($stmt->execute()) {
                        $message = "Data anggota berhasil diperbarui.";
                    } else {
                        $message = "Error: " . $stmt->error;
                    }
                } else {
                    $message = "Error: " . $stmt->error;
                }
                $stmt->close();
                break;
            case 'delete':
                $id = $_POST['id'];
                $stmt = $conn->prepare("SELECT foto FROM anggota WHERE id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($foto);
                $stmt->fetch();
                $stmt->close();

                if (!empty($foto)) {
                    $filepath = __DIR__ . '/../../assets/foto/' . basename($foto);
                    if (file_exists($filepath)) {
                        unlink($filepath);
                    }
                }

                $stmt = $conn->prepare("DELETE FROM anggota WHERE id=?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $message = "Anggota berhasil dihapus.";
                } else {
                    $message = "Error: " . $stmt->error;
                }
                $stmt->close();
                break;
        }
    }
}

$result = $conn->query("SELECT * FROM anggota ORDER BY generasi, id");
$members = [];
while ($row = $result->fetch_assoc()) {
    $members[$row['generasi']][] = $row;
}
ksort($members);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Tarombo - Smart Family</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
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

        <form id="memberForm" action="" method="POST" class="bg-white p-4 rounded-lg shadow-md mb-6 max-w-lg mx-auto">
        <h2 id="formTitle" class="text-xl font-semibold mb-4">Tambah Anggota Baru</h2>
        <input type="hidden" name="action" id="formAction" value="add">
        <input type="hidden" name="id" id="memberId">

        <div class="space-y-6">
            <div id="newMemberFields" class="space-y-6" style="display: block;">
                <div>
                    <label for="newIdInput" class="block text-sm font-medium text-gray-700">ID Baru (Harus diisi!!)</label>
                    <input type="number" name="new_id" id="newIdInput" placeholder="ID Baru" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="imageUpload" class="block text-sm font-medium text-gray-700">Foto Profil</label>
                    <input type="file" id="imageUpload" accept="image/*" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <input type="hidden" name="foto" id="fotoInput">
                </div>
            </div>
                
            <div>
                <label for="namaInput" class="block text-sm font-medium text-gray-700">Nama</label>
                <input type="text" name="nama" id="namaInput" placeholder="Nama" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="jenisKelaminInput" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                <select name="jenis_kelamin" id="jenisKelaminInput" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
            </div>
            
            <div>
                <label for="generasiInput" class="block text-sm font-medium text-gray-700">Generasi</label>
                <input type="number" name="generasi" id="generasiInput" placeholder="Generasi" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="domisiliInput" class="block text-sm font-medium text-gray-700">Domisili</label>
                <input type="text" name="domisili" id="domisiliInput" placeholder="Domisili" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="idAyahInput" class="block text-sm font-medium text-gray-700">ID Ayah (Optional)</label>
                <input type="number" name="id_ayah" id="idAyahInput" placeholder="ID Ayah" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="idIbuInput" class="block text-sm font-medium text-gray-700">ID Ibu (Optional)</label>
                <input type="number" name="id_ibu" id="idIbuInput" placeholder="ID Ibu" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="idIstri1Input" class="block text-sm font-medium text-gray-700">ID Istri 1 (Optional)</label>
                <input type="number" name="id_istri_1" id="idIstri1Input" placeholder="ID Istri 1" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="idIstri2Input" class="block text-sm font-medium text-gray-700">ID Istri 2 (Optional)</label>
                <input type="number" name="id_istri_2" id="idIstri2Input" placeholder="ID Istri 2" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="idIstri3Input" class="block text-sm font-medium text-gray-700">ID Istri 3 (Optional)</label>
                <input type="number" name="id_istri_3" id="idIstri3Input" placeholder="ID Istri 3" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" id="submitBtn" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">Tambah</button>
        </div>
    </form>

    <div class="mb-6">
        <label for="generationSelect" class="block text-sm font-medium text-gray-700">Pilih Sundut:</label>
        <select id="generationSelect" class="mt-1 block w-64 p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="">Pilih Sundut</option>
            <?php
            foreach (array_keys($members) as $gen) {
                echo "<option value=\"$gen\">Sundut $gen</option>";
            }
            ?>
        </select>
    </div>

    <?php foreach ($members as $generasi => $list): ?>
        <div id="sundut<?php echo $generasi; ?>" class="generation-table" style="display: none;">
            <h2 class="text-xl font-bold mb-4">Sundut <?php echo $generasi; ?></h2>
            <table class="mb-8">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Jenis Kelamin</th>
                        <th>Domisili</th>
                        <th>Foto</th>
                        <th>Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $member): ?>
                        <tr>
                            <td><?php echo $member['id']; ?></td>
                            <td><?php echo $member['nama']; ?></td>
                            <td><?php echo $member['jenis_kelamin']; ?></td>
                            <td><?php echo $member['domisili']; ?></td>
                            <td>
                                <img src="<?php echo !empty($member['foto']) ? '../../' . $member['foto'] . '?v=' . time() : ($member['jenis_kelamin'] == 'Laki-laki' ? '../../assets/img/default_male.jpg' : '../../assets/img/default_female.jpg'); ?>" alt="Foto" width="50">
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <button onclick="editMember(<?php echo htmlspecialchars(json_encode($member)); ?>)" class="w-22 mb-2 bg-green-500 hover:bg-green-600 text-white font-semibold py-1 px-2 rounded">Edit</button>
                                <form action="" method="POST" class="inline-block">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                                    <button type="submit" class="w-22 bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-2 rounded" onclick="return confirm('Anda yakin ingin menghapus anggota ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>

    <div class="flex items-center space-x-4">
        <a href="adminPage.php" class="ml-5 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-1 px-3 rounded">Kembali</a>
    </div>

    <script>
        function editMember(member) {
            document.getElementById('formTitle').innerText = 'Edit Anggota';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('memberId').value = member.id;
            document.getElementById('newMemberFields').style.display = 'none';
            document.getElementById('newIdInput').removeAttribute('required');
            document.getElementById('namaInput').value = member.nama;
            document.getElementById('jenisKelaminInput').value = member.jenis_kelamin;
            document.getElementById('generasiInput').value = member.generasi;
            document.getElementById('domisiliInput').value = member.domisili;
            document.getElementById('idAyahInput').value = member.id_ayah || '';
            document.getElementById('idIbuInput').value = member.id_ibu || '';
            document.getElementById('idIstri1Input').value = member.id_istri_1 || '';
            document.getElementById('idIstri2Input').value = member.id_istri_2 || '';
            document.getElementById('idIstri3Input').value = member.id_istri_3 || '';
            document.getElementById('fotoInput').value = member.foto || '';
            
            document.getElementById('imageUpload').value = '';

            const container = document.getElementById('imageUpload').parentElement;
            const existingPreview = container.querySelector('img');
            if (existingPreview) {
                container.removeChild(existingPreview);
            }
            
            if (member.foto) {
                const imgPreview = document.createElement('img');
                imgPreview.src = '../../' + member.foto + '?v=' + new Date().getTime();
                imgPreview.style.maxWidth = '200px';
                imgPreview.style.marginTop = '10px';
                container.appendChild(imgPreview);
            }
            
            document.getElementById('submitBtn').innerText = 'Update Anggota';
            document.getElementById('memberForm').scrollIntoView({behavior: 'smooth'});
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('imageUpload').addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    let memberId = document.getElementById('newIdInput').value;
                    
                    if (document.getElementById('formAction').value === 'edit') {
                        memberId = document.getElementById('memberId').value;
                    }
                    
                    if (!memberId) {
                        alert('Harap isi ID anggota terlebih dahulu sebelum mengupload foto');
                        this.value = '';
                        return;
                    }

                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('member_id', memberId);

                    fetch('../../uploads/upload_handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const imgSrc = '../../' + data.link + '?v=' + new Date().getTime();
                            document.getElementById('fotoInput').value = data.link;

                            const container = this.parentElement;
                            const existingPreview = container.querySelector('img');
                            if (existingPreview) {
                                container.removeChild(existingPreview);
                            }

                            const imgPreview = document.createElement('img');
                            imgPreview.src = imgSrc;
                            imgPreview.style.maxWidth = '200px';
                            imgPreview.style.marginTop = '10px';
                            container.appendChild(imgPreview);
                        } else {
                            throw new Error(data.error || 'Upload failed');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Upload gagal: ' + error.message);
                        this.value = '';
                    });
                }
            });

            function resetForm() {
                document.getElementById('formTitle').innerText = 'Tambah Anggota Baru';
                document.getElementById('formAction').value = 'add';
                document.getElementById('memberId').value = '';
                document.getElementById('memberForm').reset();
                
                document.getElementById('newMemberFields').style.display = 'block';
                document.getElementById('newIdInput').setAttribute('required', '');
                
                const container = document.getElementById('imageUpload').parentElement;
                const existingPreview = container.querySelector('img');
                if (existingPreview) {
                    container.removeChild(existingPreview);
                }
                
                document.getElementById('fotoInput').value = '';
                document.getElementById('submitBtn').innerText = 'Tambah';
            }
            
            const resetButton = document.querySelector('button[type="reset"]');
            if (resetButton) {
                resetButton.addEventListener('click', resetForm);
            }
        });

        function showSelectedGeneration(selectedGen) {
            document.querySelectorAll('.generation-table').forEach(table => {
                table.style.display = 'none';
            });
            
            if (selectedGen) {
                const selectedTable = document.getElementById('sundut' + selectedGen);
                if (selectedTable) {
                    selectedTable.style.display = 'block';
                    localStorage.setItem('selectedSundut', selectedGen);
                }
            }
        }

        document.getElementById('generationSelect').addEventListener('change', function() {
            const selectedGen = this.value;
            showSelectedGeneration(selectedGen);
        });

        window.addEventListener('DOMContentLoaded', function() {
            const savedSundut = localStorage.getItem('selectedSundut');
            const generationSelect = document.getElementById('generationSelect');
            
            if (savedSundut) {
                const option = generationSelect.querySelector(`option[value="${savedSundut}"]`);
                if (option) {
                    option.selected = true;
                    showSelectedGeneration(savedSundut);
                } else {
                    const firstGenOption = generationSelect.querySelector('option:nth-child(2)');
                    if (firstGenOption) {
                        firstGenOption.selected = true;
                        showSelectedGeneration(firstGenOption.value);
                    }
                }
            } else {
                const firstGenOption = generationSelect.querySelector('option:nth-child(2)');
                if (firstGenOption) {
                    firstGenOption.selected = true;
                    showSelectedGeneration(firstGenOption.value);
                }
            }
        });

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const selectedGen = document.getElementById('generationSelect').value;
                localStorage.setItem('selectedSundut', selectedGen);
            });
        });
    </script>

</body>
</html>

<?php
$conn->close();
?>