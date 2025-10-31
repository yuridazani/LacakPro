<?php
// dashboard_supir.php
include 'includes/db_config.php';

// Pastikan hanya supir yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'supir') {
    header("Location: login.php");
    exit;
}

// --- LOGIKA BARU: AMBIL TUGAS AKTIF ---
// Kita cari tugas hari ini yang statusnya 'pending' (belum dimulai) atau 'active' (sedang berjalan)
$stmt = $conn->prepare("SELECT * FROM tasks 
                        WHERE driver_id = :driver_id 
                        AND status IN ('pending', 'active')
                        ORDER BY waktu_dijadwalkan ASC 
                        LIMIT 1");
$stmt->bindParam(':driver_id', $_SESSION['user_id']);
$stmt->execute();
$task = $stmt->fetch(PDO::FETCH_ASSOC);

// Simpan task_id di session agar bisa dipakai api/update_location.php
$_SESSION['current_task_id'] = $task ? $task['id'] : null;

// Set Judul Halaman
$page_title = 'Dashboard Supir';

// Panggil Header
include 'includes/header.php';
?>

<div class="h-screen flex flex-col items-center justify-center p-4">
    <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-md text-center">
        <h1 class="text-xl font-bold">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        
        <?php if ($task): ?>
            <div class="text-left my-6 p-4 bg-gray-50 rounded-lg border">
                <h2 class="text-lg font-semibold text-blue-600 mb-3">Tugas Anda Berikutnya:</h2>
                
                <div class="mb-2">
                    <span class="text-sm text-gray-500">Dari:</span>
                    <p class="text-md font-medium"><?php echo htmlspecialchars($task['alamat_awal']); ?></p>
                </div>
                <div class="mb-2">
                    <span class="text-sm text-gray-500">Tujuan:</span>
                    <p class="text-md font-medium"><?php echo htmlspecialchars($task['alamat_tujuan']); ?></p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Jadwal:</span>
                    <p class="text-md font-medium"><?php echo date('d M Y, H:i', strtotime($task['waktu_dijadwalkan'])); ?></p>
                </div>
            </div>

            <button id="startJobButton" class="mt-4 w-full bg-green-500 text-white py-3 px-4 rounded-md text-lg font-semibold"
                <?php echo ($task['status'] == 'active') ? 'style="display:none;"' : ''; ?>>
                Mulai Perjalanan
            </button>
            <button id="completeJobButton" class="mt-2 w-full bg-red-500 text-white py-3 px-4 rounded-md text-lg font-semibold"
                <?php echo ($task['status'] == 'pending') ? 'style="display:none;"' : ''; ?>>
                Selesaikan Perjalanan
            </button>
            <p id="status" class="mt-4 text-sm text-gray-500">
                Status: <?php echo ($task['status'] == 'active') ? 'Perjalanan sedang berlangsung...' : 'Belum dimulai'; ?>
            </p>

        <?php else: ?>
            <p class="text-green-600 mt-4 text-lg">Tidak ada tugas aktif. Selamat beristirahat!</p>
        <?php endif; ?>

        <a href="logout.php" class="text-blue-500 text-sm mt-6 inline-block">Logout</a>
    </div>
</div>

<script>
    // Ambil elemen tombol baru
    const startJobButton = document.getElementById('startJobButton');
    const completeJobButton = document.getElementById('completeJobButton');
    const statusEl = document.getElementById('status');
    let watchId = null; // Tetap dipakai untuk menyimpan ID pelacak

    // Ambil task_id dari PHP
    const currentTaskId = <?php echo $_SESSION['current_task_id'] ?? 'null'; ?>;

    // --- LOGIKA KIRIM LOKASI (MASIH SAMA) ---
    async function sendLocation(position) {
        const { latitude, longitude } = position.coords;
        statusEl.textContent = `Status: Mengirim... (Lat: ${latitude.toFixed(4)}, Lng: ${longitude.toFixed(4)})`;

        try {
            // Kita modifikasi API-nya nanti agar bisa menerima task_id
            const response = await fetch('api/update_location.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    lat: latitude, 
                    lng: longitude,
                    task_id: currentTaskId // KIRIM TASK ID
                })
            });
            const result = await response.json();
            if(result.status === 'success') {
                statusEl.textContent = `Status: Lokasi terkirim! (${new Date().toLocaleTimeString()})`;
            } else {
                statusEl.textContent = `Status: Gagal mengirim. (${result.message})`;
            }
        } catch (error) {
            statusEl.textContent = `Status: Error. Periksa koneksi internet.`;
        }
    }

    // --- FUNGSI BARU UNTUK MEMULAI PELACAKAN OTOMATIS ---
    function startGeolocationTracking() {
        if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung Geolocation.');
            return;
        }
        watchId = navigator.geolocation.watchPosition(
            sendLocation,
            (error) => { statusEl.textContent = `Status: Error GPS (${error.message})`; },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
        statusEl.textContent = 'Status: Pelacakan AKTIF. Mencari sinyal GPS...';
    }

    // --- LOGIKA TOMBOL BARU ---

    // 1. Saat tombol "Mulai Perjalanan" diklik
    if (startJobButton) {
        startJobButton.addEventListener('click', async () => {
            if (!confirm('Anda yakin ingin memulai perjalanan ini?')) return;

            // Panggil API baru untuk update status TUGAS
            // (Kamu harus buat file api/start_task.php ini)
            try {
                const response = await fetch(`api/start_task.php?task_id=${currentTaskId}`);
                const result = await response.json();
                if (result.status !== 'success') throw new Error(result.message);

                // Jika sukses, baru nyalakan pelacakan
                startGeolocationTracking();

                // Ubah tampilan tombol
                startJobButton.style.display = 'none';
                completeJobButton.style.display = 'block';

            } catch (error) {
                alert(`Gagal memulai tugas: ${error.message}`);
            }
        });
    }

    // 2. Saat tombol "Selesaikan Perjalanan" diklik
    if (completeJobButton) {
        completeJobButton.addEventListener('click', async () => {
            if (!confirm('Anda yakin sudah sampai tujuan dan ingin menyelesaikan tugas?')) return;

            // Hentikan pelacakan
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }

            // Panggil API baru untuk update status TUGAS
            // (Kamu harus buat file api/complete_task.php ini)
            try {
                const response = await fetch(`api/complete_task.php?task_id=${currentTaskId}`);
                const result = await response.json();
                if (result.status !== 'success') throw new Error(result.message);

                // Sukses! Reload halaman untuk dapat tugas baru (jika ada)
                alert('Tugas selesai!');
                window.location.reload();

            } catch (error) {
                alert(`Gagal menyelesaikan tugas: ${error.message}`);
            }
        });
    }

    // 3. Cek jika tugas sudah 'active' saat halaman di-load
    // (Misal supir refresh halaman di tengah perjalanan)
    <?php if ($task && $task['status'] == 'active'): ?>
        console.log('Melanjutkan pelacakan untuk tugas yang sedang berjalan...');
        startGeolocationTracking();
    <?php endif; ?>

</script>

<?php
// Panggil Footer
include 'includes/footer.php'; 
?>