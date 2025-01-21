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
                $stmt = $conn->prepare("INSERT INTO anggota (id, nama, jenis_kelamin, generasi, domisili, id_ayah, id_ibu, id_istri_1, id_istri_2, id_istri_3, foto, query_boolean) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $nama = $_POST['nama'];
                $jenis_kelamin = $_POST['jenis_kelamin'];
                $generasi = $_POST['generasi'];
                $domisili = $_POST['domisili'];
                $query_boolean = $_POST['query_boolean'];
                $id_ayah = !empty($_POST['id_ayah']) ? $_POST['id_ayah'] : null;
                $id_ibu = !empty($_POST['id_ibu']) ? $_POST['id_ibu'] : null;
                $id_istri_1 = !empty($_POST['id_istri_1']) ? $_POST['id_istri_1'] : null;
                $id_istri_2 = !empty($_POST['id_istri_2']) ? $_POST['id_istri_2'] : null;
                $id_istri_3 = !empty($_POST['id_istri_3']) ? $_POST['id_istri_3'] : null;
                $foto = $_POST['foto'];

                $stmt->bind_param("ississiiiiss", $id, $nama, $jenis_kelamin, $generasi, $domisili, $id_ayah, $id_ibu, $id_istri_1, $id_istri_2, $id_istri_3, $foto, $query_boolean);
                
                if ($stmt->execute()) {
                    $message = "Anggota baru berhasil ditambahkan.";
                } else {
                    $message = "Error: " . $stmt->error;
                }
                $stmt->close();
                break;
            case 'edit':
                $id = $_POST['id'];
                $stmt = $conn->prepare("UPDATE anggota SET nama=?, jenis_kelamin=?, generasi=?, domisili=?, id_ayah=?, id_ibu=?, id_istri_1=?, id_istri_2=?, id_istri_3=?, query_boolean=? WHERE id=?");


                $nama = $_POST['nama'];
                $jenis_kelamin = $_POST['jenis_kelamin'];
                $generasi = $_POST['generasi'];
                $domisili = $_POST['domisili'];
                $query_boolean = $_POST['query_boolean'];
                $id_ayah = !empty($_POST['id_ayah']) ? $_POST['id_ayah'] : null;
                $id_ibu = !empty($_POST['id_ibu']) ? $_POST['id_ibu'] : null;
                $id_istri_1 = !empty($_POST['id_istri_1']) ? $_POST['id_istri_1'] : null;
                $id_istri_2 = !empty($_POST['id_istri_2']) ? $_POST['id_istri_2'] : null;
                $id_istri_3 = !empty($_POST['id_istri_3']) ? $_POST['id_istri_3'] : null;

                if ($stmt->bind_param("ssisiiiiisi", $nama, $jenis_kelamin, $generasi, $domisili, $id_ayah, $id_ibu, $id_istri_1, $id_istri_2, $id_istri_3, $query_boolean, $id)) {
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

$conn->close();
?>