<?php
// api/tambah_supir.php
include '../includes/db_config.php';
header('Content-Type: application/json');

// Pastikan manajer sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    echo json_encode(["status" => "error", "message" => "Akses ditolak."]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username']) || !isset($data['password']) || !isset($data['nama_lengkap'])) {
    echo json_encode(["status" => "error", "message" => "Semua field wajib diisi."]);
    exit;
}

// Validasi dasar
if (empty($data['username']) || empty($data['password']) || empty($data['nama_lengkap'])) {
    echo json_encode(["status" => "error", "message" => "Data tidak boleh kosong."]);
    exit;
}

try {
    // Hash password sebelum disimpan
    $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, password_hash, nama_lengkap, role) 
            VALUES (:username, :password_hash, :nama_lengkap, 'supir')";
            
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $data['username']);
    $stmt->bindParam(':password_hash', $password_hash);
    $stmt->bindParam(':nama_lengkap', $data['nama_lengkap']);
    $stmt->execute();
    
    echo json_encode(["status" => "success", "message" => "Supir berhasil ditambahkan."]);

} catch(PDOException $e) {
    // Cek jika error karena username duplikat
    if ($e->errorInfo[1] == 1062) {
        echo json_encode(["status" => "error", "message" => "Username '{$data['username']}' sudah digunakan."]);
    } else {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
$conn = null;
?>