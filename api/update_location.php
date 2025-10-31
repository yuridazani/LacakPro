<?php
// api/update_location.php
include '../includes/db_config.php';
header('Content-Type: application/json');

// Pastikan supir sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'supir') {
    echo json_encode(["status" => "error", "message" => "Otentikasi gagal."]);
    exit;
}

// Ambil vehicle_id dari session (diset saat di dashboard_supir.php)
$vehicle_id = $_SESSION['vehicle_id'];

if (!$vehicle_id) {
    echo json_encode(["status" => "error", "message" => "Supir tidak terhubung ke kendaraan."]);
    exit;
}

// Ambil data JSON yang dikirim dari JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['lat']) && isset($data['lng'])) {
    try {
        $sql = "INSERT INTO locations (vehicle_id, latitude, longitude, timestamp) 
                VALUES (:vehicle_id, :latitude, :longitude, NOW())";
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':vehicle_id', $vehicle_id);
        $stmt->bindParam(':latitude', $data['lat']);
        $stmt->bindParam(':longitude', $data['lng']);
        $stmt->execute();
        
        echo json_encode(["status" => "success", "message" => "Lokasi diperbarui."]);

    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap."]);
}
$conn = null;
?>