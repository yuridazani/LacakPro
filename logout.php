<?php
// logout.php
include 'includes/db_config.php'; // Memulai session

// Hapus semua data session
session_unset();
session_destroy();

// Arahkan kembali ke halaman login
header("Location: login.php");
exit;
?>