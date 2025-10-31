<?php
// api/get_history.php
include '../includes/db_config.php';
header('Content-Type: application/json');

// Pastikan manajer sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    echo json_encode(["status" => "error", "message" => "Akses ditolak."]);
    exit;
}

// Ambil parameter dari request GET
if (!isset($_GET['vehicle_id']) || !isset($_GET['date'])) {
    echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap (vehicle_id, date)."]);
    exit;
}

$vehicle_id = $_GET['vehicle_id'];
$date = $_GET['date']; // Format: YYYY-MM-DD

try {
    // Ambil semua data lokasi untuk kendaraan dan tanggal tersebut, urutkan berdasarkan waktu
    $sql = "SELECT latitude, longitude, timestamp 
            FROM locations 
            WHERE vehicle_id = :vehicle_id 
              AND DATE(timestamp) = :date
            ORDER BY timestamp ASC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':vehicle_id', $vehicle_id);
    $stmt->bindParam(':date', $date);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($results)) {
        echo json_encode(["status" => "error", "message" => "Tidak ada data pelacakan pada tanggal tersebut."]);
    } else {
        echo json_encode(["status" => "success", "data" => $results]);
    }

} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
$conn = null;
?>