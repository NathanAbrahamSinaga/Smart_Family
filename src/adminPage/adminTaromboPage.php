<?php
session_start();
require_once '../../server/adminTaromboBackend.php';

$successMessage = $message ?? null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Tarombo - Smart Family</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">
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
<body class="bg-gray-100 dark:bg-gray-900 p-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-900 dark:text-white">Admin Tarombo</h1>
    
    <?php if ($successMessage): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-600 dark:text-green-200" role="alert">
            <span class="block sm:inline"><?php echo $successMessage; ?></span>
        </div>
    <?php endif; ?>

    <form id="memberForm" action="" method="POST" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md mb-6 max-w-lg mx-auto">
        <h2 id="formTitle" class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Tambah Anggota Baru</h2>
        <input type="hidden" name="action" id="formAction" value="add">
        <input type="hidden" name="id" id="memberId">

        <div class="space-y-6">
            <div id="newMemberFields" class="space-y-6" style="display: block;">
                <div>
                    <label for="newIdInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Baru (Harus diisi!!)</label>
                    <input type="number" name="new_id" id="newIdInput" placeholder="ID Baru" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                </div>
                <div>
                    <label for="imageUpload" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Foto Profil</label>
                    <input type="file" id="imageUpload" accept="image/*" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    <input type="hidden" name="foto" id="fotoInput">
                </div>
            </div>
                
            <div>
                <label for="namaInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama</label>
                <input type="text" name="nama" id="namaInput" placeholder="Nama" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </div>
            
            <div>
                <label for="jenisKelaminInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Kelamin</label>
                <select name="jenis_kelamin" id="jenisKelaminInput" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
            </div>
            
            <div>
                <label for="generasiInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Generasi</label>
                <select name="query_boolean" id="queryBooleanInput" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    <option value="sundut">Sundut</option>
                    <option value="bere">Bere</option>
                </select>
                <input type="number" name="generasi" id="generasiInput" placeholder="Generasi" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </div>
            
            <div>
                <label for="domisiliInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Domisili</label>
                <input type="text" name="domisili" id="domisiliInput" placeholder="Domisili" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </div>
            
            <div>
                <label for="idAyahInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Ayah (Optional)</label>
                <input type="number" name="id_ayah" id="idAyahInput" placeholder="ID Ayah" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </div>
            
            <div>
                <label for="idIbuInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Ibu (Optional)</label>
                <input type="number" name="id_ibu" id="idIbuInput" placeholder="ID Ibu" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </div>
            
            <div>
                <label for="idIstri1Input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Istri 1 (Optional)</label>
                <input type="number" name="id_istri_1" id="idIstri1Input" placeholder="ID Istri 1" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </div>
            
            <div>
                <label for="idIstri2Input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Istri 2 (Optional)</label>
                <input type="number" name="id_istri_2" id="idIstri2Input" placeholder="ID Istri 2" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </div>
            
            <div>
                <label for="idIstri3Input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Istri 3 (Optional)</label>
                <input type="number" name="id_istri_3" id="idIstri3Input" placeholder="ID Istri 3" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" id="submitBtn" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">Tambah</button>
        </div>
    </form>

    <div class="mb-6">
        <label for="generationSelect" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sundut:</label>
        <select id="generationSelect" class="mt-1 block w-64 p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
            <option value="">Sundut</option>
            <?php
            foreach (array_keys($members) as $gen) {
                echo "<option value=\"$gen\">$gen</option>";
            }
            ?>
        </select>
    </div>

    <?php
    $sundutMembers = [];
    $bereMembers = [];
    foreach ($members as $generasi => $list) {
        $sundutMembers[$generasi] = array_filter($list, function($member) {
            return $member['query_boolean'] === 'sundut';
        });
        $bereMembers[$generasi] = array_filter($list, function($member) {
            return $member['query_boolean'] === 'bere';
        });
    }
    ?>

    <?php foreach ($members as $generasi => $list): ?>
        <!-- Tabel Sundut -->
        <div id="sundut<?php echo $generasi; ?>" class="generation-table" style="display: none;">
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Sundut <?php echo $generasi; ?></h2>
            <table class="mb-8">
                <thead>
                    <tr>
                        <th class="bg-gray-200 dark:bg-gray-500">ID</th>
                        <th class="bg-gray-200 dark:bg-gray-500">Nama</th>
                        <th class="bg-gray-200 dark:bg-gray-500">Jenis Kelamin</th>
                        <th class="bg-gray-200 dark:bg-gray-500">Domisili</th>
                        <th class="bg-gray-200 dark:bg-gray-500">Foto</th>
                        <th class="bg-gray-200 dark:bg-gray-500">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sundutMembers[$generasi] as $member): ?>
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="text-gray-900 dark:text-white"><?php echo $member['id']; ?></td>
                            <td class="text-gray-900 dark:text-white"><?php echo $member['nama']; ?></td>
                            <td class="text-gray-900 dark:text-white"><?php echo $member['jenis_kelamin']; ?></td>
                            <td class="text-gray-900 dark:text-white"><?php echo $member['domisili']; ?></td>
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

        <!-- Tabel Bere -->
        <div id="bere<?php echo $generasi; ?>" class="generation-table" style="display: none;">
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Bere <?php echo $generasi; ?></h2>
            <table class="mb-8">
                <thead>
                    <tr>
                        <th class="bg-gray-200 dark:bg-gray-500">ID</th>
                        <th class="bg-gray-200 dark:bg-gray-500">Nama</th>
                        <th class="bg-gray-200 dark:bg-gray-500">Jenis Kelamin</th>
                        <th class="bg-gray-200 dark:bg-gray-500">Domisili</th>
                        <th class="bg-gray-200 dark:bg-gray-500">Foto</th>
                        <th class="bg-gray-200 dark:bg-gray-500">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bereMembers[$generasi] as $member): ?>
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="text-gray-900 dark:text-white"><?php echo $member['id']; ?></td>
                            <td class="text-gray-900 dark:text-white"><?php echo $member['nama']; ?></td>
                            <td class="text-gray-900 dark:text-white"><?php echo $member['jenis_kelamin']; ?></td>
                            <td class="text-gray-900 dark:text-white"><?php echo $member['domisili']; ?></td>
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

    <button onclick="toggleDarkMode()" class="fixed bottom-4 right-4 p-3 bg-gray-200 dark:bg-gray-700 rounded-full hover:scale-110 transition-transform duration-200">
        <span class="dark:hidden">🌙</span>
        <span class="hidden dark:inline">☀️</span>
    </button>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                }
            }
        }

        function initializeTheme() {
            const savedTheme = localStorage.getItem('theme');
            
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }

        function toggleDarkMode() {
            const html = document.documentElement;
            
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }

        document.addEventListener('DOMContentLoaded', initializeTheme);

        function editMember(member) {
            document.getElementById('formTitle').innerText = 'Edit Anggota';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('queryBooleanInput').value = member.query_boolean || 'sundut';
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
                const sundutTable = document.getElementById('sundut' + selectedGen);
                const bereTable = document.getElementById('bere' + selectedGen);
                
                if (sundutTable) {
                    sundutTable.style.display = 'block';
                }
                if (bereTable) {
                    bereTable.style.display = 'block';
                }
                
                localStorage.setItem('selectedSundut', selectedGen);
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