<?php
// auth.php
include 'includes/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password_hash, role FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifikasi password
    if ($user && password_verify($password, $user['password_hash'])) {
        // Password benar! Simpan data ke session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user['role'];

        // Arahkan ke dashboard yang sesuai
        if ($user['role'] == 'manager') {
            header("Location: dashboard_manager.php");
        } else {
            header("Location: dashboard_supir.php");
        }
        exit;
    } else {
        // Password salah
        header("Location: login.php?error=1");
        exit;
    }
}
$conn = null;
?>