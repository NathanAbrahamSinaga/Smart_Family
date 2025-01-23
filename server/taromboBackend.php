<?php
session_start();
require_once '../../server/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$generation = isset($_GET['generation']) ? $_GET['generation'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Pagination
$records_per_page = 8;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Base query
$query = "
    SELECT a.*,
           ayah.nama as nama_ayah,
           ibu.nama as nama_ibu,
           istri1.nama as nama_istri_1,
           istri2.nama as nama_istri_2,
           istri3.nama as nama_istri_3,
           (SELECT GROUP_CONCAT(DISTINCT suami.nama ORDER BY suami.nama ASC SEPARATOR ', ')
            FROM anggota suami 
            WHERE (suami.id_istri_1 = a.id 
               OR suami.id_istri_2 = a.id 
               OR suami.id_istri_3 = a.id)) as nama_suami,
           GROUP_CONCAT(DISTINCT anak.nama ORDER BY anak.id ASC SEPARATOR ', ') as nama_anak
    FROM anggota a
    LEFT JOIN anggota ayah ON a.id_ayah = ayah.id
    LEFT JOIN anggota ibu ON a.id_ibu = ibu.id
    LEFT JOIN anggota istri1 ON a.id_istri_1 = istri1.id
    LEFT JOIN anggota istri2 ON a.id_istri_2 = istri2.id
    LEFT JOIN anggota istri3 ON a.id_istri_3 = istri3.id
    LEFT JOIN anggota anak ON anak.id_ayah = a.id OR anak.id_ibu = a.id
";

// WHERE conditions
$whereClauses = [];
$params = [];
$types = '';

if (!empty($search)) {
    $whereClauses[] = "a.nama LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

if (!empty($generation)) {
    $whereClauses[] = "a.generasi = ?";
    $params[] = $generation;
    $types .= 'i';
}

if (!empty($type)) {
    $whereClauses[] = "a.query_boolean = ?";
    $params[] = $type;
    $types .= 's';
}

if (!empty($whereClauses)) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
}

$query .= " GROUP BY a.id ORDER BY a.generasi DESC, a.query_boolean, a.id LIMIT ?, ?";
$params[] = $offset;
$params[] = $records_per_page;
$types .= 'ii';

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$countQuery = "SELECT COUNT(DISTINCT a.id) as total FROM anggota a";
if (!empty($whereClauses)) {
    $countQuery .= " WHERE " . implode(" AND ", $whereClauses);
}
$countStmt = $conn->prepare($countQuery);
if ($params) {
    array_pop($params);
    array_pop($params);
    $types = substr($types, 0, -2);
    if ($types) {
        $countStmt->bind_param($types, ...$params);
    }
}
$countStmt->execute();
$total_records = $countStmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

$generationQuery = "SELECT DISTINCT generasi FROM anggota ORDER BY generasi DESC";
$generationResult = $conn->query($generationQuery);
$generations = [];
while ($row = $generationResult->fetch_assoc()) {
    $generations[] = $row['generasi'];
}

$conn->close();
?>