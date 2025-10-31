<?php
// api/tambah_tugas.php
include '../includes/db_config.php';
header('Content-Type: application/json');

// Pastikan manajer sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    echo json_encode(["status" => "error", "message" => "Akses ditolak."]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validasi data yang masuk
if (!isset($data['vehicle_id']) || !isset($data['driver_id']) || !isset($data['alamat_awal']) || !isset($data['alamat_tujuan']) || !isset($data['waktu_dijadwalkan'])) {
    echo json_encode(["status" => "error", "message" => "Semua field wajib diisi."]);
    exit;
}

if (empty($data['vehicle_id']) || empty($data['driver_id']) || empty($data['alamat_awal']) || empty($data['alamat_tujuan']) || empty($data['waktu_dijadwalkan'])) {
    echo json_encode(["status" => "error", "message" => "Data tidak boleh kosong."]);
    exit;
}

try {
    $sql = "INSERT INTO tasks (vehicle_id, driver_id, alamat_awal, alamat_tujuan, waktu_dijadwalkan, status) 
            VALUES (:vehicle_id, :driver_id, :alamat_awal, :alamat_tujuan, :waktu_dijadwalkan, 'pending')";
            
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':vehicle_id', $data['vehicle_id']);
    $stmt->bindParam(':driver_id', $data['driver_id']);
    $stmt->bindParam(':alamat_awal', $data['alamat_awal']);
    $stmt->bindParam(':alamat_tujuan', $data['alamat_tujuan']);
    $stmt->bindParam(':waktu_dijadwalkan', $data['waktu_dijadwalkan']);
    $stmt->execute();
    
    echo json_encode(["status" => "success", "message" => "Tugas berhasil ditambahkan."]);

} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
$conn = null;
?>