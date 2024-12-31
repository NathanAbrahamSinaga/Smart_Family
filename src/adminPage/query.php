<?php
// Memuat konfigurasi database
require_once '../../server/config.php';

// Fungsi untuk melakukan backup database
function backupDatabase() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Header untuk download file
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="backup_' . DB_NAME . '_' . date('Y-m-d_H-i-s') . '.sql"');

    // Mendapatkan semua tabel
    $tables = array();
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }

    $return = '';

    // Proses setiap tabel
    foreach ($tables as $table) {
        $result = $conn->query("SHOW CREATE TABLE $table");
        $row = $result->fetch_row();
        
        $return .= "\n\n" . $row[1] . ";\n\n";
        
        $result = $conn->query("SELECT * FROM $table");
        
        while ($row = $result->fetch_row()) {
            $return .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < count($row); $j++) {
                $row[$j] = addslashes($row[$j]);
                if (isset($row[$j])) {
                    $return .= '"' . $row[$j] . '"';
                } else {
                    $return .= '""';
                }
                if ($j < (count($row) - 1)) {
                    $return .= ',';
                }
            }
            $return .= ");\n";
        }
    }
    
    $conn->close();
    
    echo $return;
    exit;
}

// Mengecek apakah request adalah untuk backup
if (isset($_POST['action']) && $_POST['action'] === 'backup') {
    backupDatabase();
}

// Memproses form SQL seperti sebelumnya
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sql'])) {
    $sql = $_POST['sql'] ?? '';

    // Koneksi ke database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Mengecek koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Menjalankan perintah SQL jika tidak kosong
    if (!empty($sql)) {
        if ($result = $conn->query($sql)) {
            if ($result->num_rows > 0) {
                $message = "Perintah SQL berhasil dijalankan. Hasil query:";
                $message .= "<table class='table table-striped'>";
                $message .= "<thead><tr>";
                for ($i = 0; $i < $result->field_count; $i++) {
                    $field = $result->fetch_field();
                    $message .= "<th>" . $field->name . "</th>";
                }
                $message .= "</tr></thead><tbody>";
                while ($row = $result->fetch_assoc()) {
                    $message .= "<tr>";
                    foreach ($row as $value) {
                        $message .= "<td>" . $value . "</td>";
                    }
                    $message .= "</tr>";
                }
                $message .= "</tbody></table>";
            } else {
                $message = "Perintah SQL berhasil dijalankan, tapi tidak ada hasil yang dikembalikan.";
            }
        } else {
            $message = "Error: " . $conn->error;
        }
    } else {
        $message = "Masukkan perintah SQL.";
    }

    // Menutup koneksi
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eksekusi SQL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 d-flex align-items-center justify-content-center min-vh-100">
    <div class="bg-white shadow-lg rounded-lg p-4 max-w-md w-100">
        <h2 class="text-2xl font-bold mb-4 text-center text-gray-700">Eksekusi SQL</h2>

        <!-- Tombol Backup Database -->
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" class="mb-4">
            <input type="hidden" name="action" value="backup">
            <button type="submit" class="btn btn-success w-100">Download Backup Database</button>
        </form>

        <!-- Pesan Hasil Eksekusi -->
        <?php if ($message): ?>
            <div class="alert <?= strpos($message, 'Error') === false ? 'alert-success' : 'alert-danger' ?> mb-4">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- Form Input SQL -->
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <div class="form-group">
                <label for="sql" class="form-label">Masukkan Perintah SQL:</label>
                <textarea name="sql" id="sql" rows="5" class="form-control" placeholder="Contoh: ALTER TABLE anggota MODIFY COLUMN id INT PRIMARY KEY;"></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-3">Jalankan Perintah SQL</button>
        </form>
    </div>
</body>
</html>