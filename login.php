<?php
// login.php
include 'includes/db_config.php';

// Jika sudah login, lempar ke dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'manager') {
        header("Location: dashboard_manager.php");
    } else {
        header("Location: dashboard_supir.php");
    }
    exit;
}

// Set Judul Halaman
$page_title = 'Login';

// Panggil Header
include 'includes/header.php';
?>

<div class="flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
        <h1 class="text-2xl font-bold text-center text-blue-600 mb-6">LacakPro</h1>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                Username atau password salah.
            </div>
        <?php endif; ?>

        <form action="auth.php" method="POST">
            </form>
    </div>
</div>

<?php
// Panggil Footer
include 'includes/footer.php'; 
?>