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

function getTables($conn) {
    $tables = array();
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    return $tables;
}

function deleteRecord($conn, $table, $value) {
    $result = false;
    
    try {
        if ($table === 'kode') {
            $sql = "DELETE FROM kode WHERE kode = ?";
            error_log("Deleting from kode table with value: " . $value);
        } else {
            $sql = "DELETE FROM " . $table . " WHERE id = ?";
            error_log("Deleting from " . $table . " table with id: " . $value);
        }
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("i", $value);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
        
        $result = ($stmt->affected_rows > 0);
        $stmt->close();
        
        if (!$result) {
            error_log("No rows were affected");
        }
    } catch (Exception $e) {
        error_log("Error deleting record: " . $e->getMessage());
        return false;
    }
    
    return $result;
}

function addAdmin($conn, $username, $password) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashedPassword);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

function addCode($conn, $kode) {
    if (!is_numeric($kode)) {
        error_log("Invalid kode value: " . $kode);
        return false;
    }
    
    $kode = (int)$kode;
    
    $stmt = $conn->prepare("SELECT kode FROM kode WHERE kode = ?");
    $stmt->bind_param("i", $kode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        return false;
    }
    
    $stmt->close();
    
    $stmt = $conn->prepare("INSERT INTO kode (kode) VALUES (?)");
    $stmt->bind_param("i", $kode);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete'])) {
        $table = $_POST['table'];
        $value = (int)$_POST['value'];
        
        error_log("Attempting to delete - Table: " . $table . ", Value: " . $value);
        
        if (deleteRecord($conn, $table, $value)) {
            $successMessage = "Record deleted successfully from " . $table;
        } else {
            $errorMessage = "Error deleting record from " . $table;
        }
    } elseif (isset($_POST['add_admin'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            $errorMessage = "Username and password are required";
        } else {
            if (addAdmin($conn, $username, $password)) {
                $successMessage = "Admin added successfully";
            } else {
                $errorMessage = "Error adding admin. Username might already exist.";
            }
        }
    } elseif (isset($_POST['add_code'])) {
        $kode = trim($_POST['kode']);
        
        if (empty($kode) || !is_numeric($kode)) {
            $errorMessage = "Valid code number is required";
        } else {
            if (addCode($conn, $kode)) {
                $successMessage = "Code added successfully";
            } else {
                $errorMessage = "Error adding code. Code might already exist.";
            }
        }
    }
}

$selectedTable = isset($_GET['table']) ? $_GET['table'] : '';
$tableData = null;
$columns = array();

if ($selectedTable) {
    $allowedTables = getTables($conn);
    if (!in_array($selectedTable, $allowedTables)) {
        die("Invalid table selected");
    }
    
    $result = $conn->query("DESCRIBE " . $selectedTable);
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    $tableData = $conn->query("SELECT * FROM " . $selectedTable);
}

$tables = getTables($conn);

function getUploadedImages($uploadDir) {
    $images = [];
    $allowedExtensions = ['webp'];
    
    if (is_dir($uploadDir)) {
        $files = scandir($uploadDir);
        foreach ($files as $file) {
            $filePath = $uploadDir . DIRECTORY_SEPARATOR . $file;
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            
            if (is_file($filePath) && in_array($extension, $allowedExtensions)) {
                $images[] = [
                    'filename' => $file,
                    'path' => '/uploads/' . $file,
                    'size' => filesize($filePath),
                    'uploaded' => date('Y-m-d H:i:s', filemtime($filePath))
                ];
            }
        }
    }
    
    usort($images, function($a, $b) {
        return strtotime($b['uploaded']) - strtotime($a['uploaded']);
    });
    
    return $images;
}

function deleteUploadedImage($filename, $uploadDir) {
    $filePath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
    
    if (file_exists($filePath) && is_writable($filePath)) {
        if (unlink($filePath)) {
            return ['success' => true, 'message' => 'Image deleted successfully'];
        } else {
            return ['success' => false, 'error' => 'Failed to delete image'];
        }
    }
    
    return ['success' => false, 'error' => 'File not found or not writable'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_image'])) {
    $uploadDir = __DIR__ . '/../../uploads';
    $filename = $_POST['image_filename'];
    
    $deleteResult = deleteUploadedImage($filename, $uploadDir);
    
    if ($deleteResult['success']) {
        $successMessage = $deleteResult['message'];
    } else {
        $errorMessage = $deleteResult['error'];
    }
}

$uploadDir = __DIR__ . '/../../uploads';
$uploadedImages = getUploadedImages($uploadDir);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Management - Smart Family</title>
    <link rel="stylesheet" href="assets/css/output.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-500 text-white py-4">
        <div class="container mx-auto flex justify-between items-center px-4">
            <div class="flex items-center space-x-4">
                <a href="../../index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-1 px-3 rounded">Kembali</a>
                <h1 class="text-xl font-semibold">Database Management</h1>
            </div>
            <div class="flex items-center">
                <span class="mr-4">Admin <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <a href="../loginPage/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded">Logout</a>
            </div>
        </div>
    </header>

    <div class="container mx-auto mt-8 px-4">
        <?php if (isset($successMessage)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <div class="grid md:grid-cols-2 gap-4 mb-8">
            <div class="bg-white shadow rounded-lg p-4">
                <h2 class="text-lg font-semibold mb-4">Add New Admin</h2>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <button type="submit" name="add_admin" 
                            class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                        Add Admin
                    </button>
                </form>
            </div>

            <div class="bg-white shadow rounded-lg p-4">
                <h2 class="text-lg font-semibold mb-4">Add New Code</h2>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Code Number</label>
                        <input type="number" name="kode" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <button type="submit" name="add_code" 
                            class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                        Add Code
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold">Select Table</h2>
                <div class="flex flex-wrap gap-2 mt-2">
                    <?php foreach ($tables as $table): ?>
                        <a href="?table=<?php echo htmlspecialchars($table); ?>" 
                           class="<?php echo $selectedTable === $table ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'; ?> px-3 py-1 rounded hover:bg-blue-600 hover:text-white transition">
                            <?php echo htmlspecialchars($table); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($selectedTable && $tableData): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <?php foreach ($columns as $column): ?>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <?php echo htmlspecialchars($column); ?>
                                    </th>
                                <?php endforeach; ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($row = $tableData->fetch_assoc()): ?>
                                <tr>
                                    <?php foreach ($columns as $column): ?>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php 
                                            if ($column === 'foto' && !empty($row[$column])) {
                                                echo '<img src="uploads/' . htmlspecialchars($row[$column]) . '" alt="Profile" class="h-10 w-10 rounded-full">';
                                            } elseif ($column === 'password') {
                                                echo '[HIDDEN]';
                                            } else {
                                                echo htmlspecialchars($row[$column]); 
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this record?');" class="inline">
                                            <input type="hidden" name="table" value="<?php echo htmlspecialchars($selectedTable); ?>">
                                            <?php 
                                            $deleteValue = ($selectedTable === 'kode') ? $row['kode'] : $row['id'];
                                            ?>
                                            <input type="hidden" name="value" value="<?php echo (int)$deleteValue; ?>">
                                            <button type="submit" name="delete" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($selectedTable): ?>
                <div class="p-4">
                    <p class="text-gray-500">No data available in table.</p>
                </div>
            <?php else: ?>
                <div class="p-4">
                    <p class="text-gray-500">Please select a table to view its data.</p>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow rounded-lg overflow-hidden mb-8">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold">Uploaded Images</h2>
            </div>
            
            <?php if (!empty($uploadedImages)): ?>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4">
                    <?php foreach ($uploadedImages as $image): ?>
                        <div class="bg-gray-100 rounded-lg overflow-hidden shadow-md">
                            <img src="<?php echo htmlspecialchars($image['path']); ?>" 
                                 alt="Uploaded Image" 
                                 class="w-full h-48 object-cover">
                            <p class="text-sm text-gray-700">
                                <strong>Filename:</strong> 
                                <?php echo htmlspecialchars($image['path']); ?>
                            </p>
                            <div class="p-2">
                                <p class="text-xs text-gray-600 truncate">
                                    <?php echo htmlspecialchars($image['filename']); ?>
                                </p>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-xs text-gray-500">
                                        <?php echo htmlspecialchars($image['uploaded']); ?>
                                    </span>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                        <input type="hidden" name="image_filename" value="<?php echo htmlspecialchars($image['filename']); ?>">
                                        <button type="submit" name="delete_image" 
                                                class="text-red-500 hover:text-red-700 text-sm">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="p-4">
                    <p class="text-gray-500">No images have been uploaded yet.</p>
                </div>
            <?php endif; ?>
        </div>
        </div>


    </div>

    <footer id="footer-fixed" class="bg-blue-500 text-white py-4 fixed bottom-0 left-0 right-0 flex justify-center items-center">
        <p class="text-center">&copy; 2024 Smart Family. All rights reserved.</p>
    </footer>

    <script>
        function toggleFooter() {
            const footerStatic = document.getElementById('footer-static');
            const footerFixed = document.getElementById('footer-fixed');
            const isScrollable = document.body.scrollHeight > window.innerHeight;
            
            if (isScrollable) {
                footerStatic.classList.remove('hidden');
                footerFixed.classList.add('hidden');
            } else {
                footerStatic.classList.add('hidden');
                footerFixed.classList.remove('hidden');
            }
        }

        window.addEventListener('load', toggleFooter);

        window.addEventListener('resize', toggleFooter);
    </script>
</body>
</html>

<?php
$conn->close();
?>