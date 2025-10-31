<?php
// api/get_locations.php
include '../includes/db_config.php';
header('Content-Type: application/json');

// Pastikan manajer sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    echo json_encode(["status" => "error", "message" => "Akses ditolak."]);
    exit;
}

try {
    // Kueri ini sedikit kompleks:
    // 1. Mengambil lokasi TERBARU dari SETIAP kendaraan
    // 2. Menggunakan Subquery untuk mendapatkan ID lokasi terbaru (l2.id)
    // 3. JOIN dengan tabel vehicles dan users untuk data lengkap
    
    $sql = "SELECT 
                l.vehicle_id, 
                l.latitude, 
                l.longitude, 
                l.timestamp,
                v.nama_kendaraan,
                v.nomor_polisi,
                u.nama_lengkap
            FROM locations l
            INNER JOIN (
                SELECT vehicle_id, MAX(id) AS max_id
                FROM locations
                GROUP BY vehicle_id
            ) l2 ON l.vehicle_id = l2.vehicle_id AND l.id = l2.max_id
            JOIN vehicles v ON l.vehicle_id = v.id
            JOIN users u ON v.driver_id = u.id";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["status" => "success", "data" => $results]);

} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
$conn = null;
?>