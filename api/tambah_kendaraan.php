<?php
// api/tambah_kendaraan.php
include '../includes/db_config.php';
header('Content-Type: application/json');

// Pastikan manajer sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    echo json_encode(["status" => "error", "message" => "Akses ditolak."]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['nama_kendaraan']) || !isset($data['nomor_polisi']) || !isset($data['driver_id'])) {
    echo json_encode(["status" => "error", "message" => "Semua field wajib diisi."]);
    exit;
}

// Validasi dasar
if (empty($data['nama_kendaraan']) || empty($data['nomor_polisi']) || empty($data['driver_id'])) {
    echo json_encode(["status" => "error", "message" => "Data tidak boleh kosong."]);
    exit;
}

try {
    $sql = "INSERT INTO vehicles (driver_id, nama_kendaraan, nomor_polisi) 
            VALUES (:driver_id, :nama_kendaraan, :nomor_polisi)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':driver_id', $data['driver_id']);
    $stmt->bindParam(':nama_kendaraan', $data['nama_kendaraan']);
    $stmt->bindParam(':nomor_polisi', $data['nomor_polisi']);
    $stmt->execute();
    
    echo json_encode(["status" => "success", "message" => "Kendaraan berhasil ditambahkan."]);

} catch(PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        echo json_encode(["status" => "error", "message" => "Nomor Polisi '{$data['nomor_polisi']}' sudah terdaftar."]);
    } else {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
$conn = null;
?>