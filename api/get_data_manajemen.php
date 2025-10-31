<?php
// api/get_data_manajemen.php
include '../includes/db_config.php';
header('Content-Type: application/json');

// Pastikan manajer sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    echo json_encode(["status" => "error", "message" => "Akses ditolak."]);
    exit;
}

try {
    // 1. Ambil daftar supir (tidak berubah)
    $stmt_supir = $conn->prepare("SELECT id, username, nama_lengkap FROM users WHERE role = 'supir'");
    $stmt_supir->execute();
    $supir = $stmt_supir->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Ambil daftar kendaraan (DI-UPDATE untuk menyertakan ID)
    $stmt_kendaraan = $conn->prepare("
        SELECT 
            v.id AS vehicle_id, 
            v.nama_kendaraan, 
            v.nomor_polisi, 
            u.id AS driver_id,
            u.nama_lengkap 
        FROM vehicles v
        JOIN users u ON v.driver_id = u.id
        ORDER BY v.id DESC
    ");
    $stmt_kendaraan->execute();
    $kendaraan = $stmt_kendaraan->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success", 
        "data" => [
            "supir" => $supir,
            "kendaraan" => $kendaraan
        ]
    ]);

} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
$conn = null;
?>