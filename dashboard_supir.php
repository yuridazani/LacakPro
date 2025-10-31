<?php
// dashboard_supir.php
include 'includes/db_config.php';

// Pastikan hanya supir yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'supir') {
    header("Location: login.php");
    exit;
}

// Ambil data kendaraan supir
$stmt = $conn->prepare("SELECT id, nama_kendaraan, nomor_polisi FROM vehicles WHERE driver_id = :driver_id");
$stmt->bindParam(':driver_id', $_SESSION['user_id']);
$stmt->execute();
$vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

// Simpan vehicle_id di session agar mudah diakses API
$_SESSION['vehicle_id'] = $vehicle ? $vehicle['id'] : null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Supir</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex flex-col items-center justify-center p-4">
    <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-md text-center">
        <h1 class="text-xl font-bold">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        
        <?php if ($vehicle): ?>
            <p class="text-gray-700 mt-2">Anda mengemudikan:</p>
            <p class="text-lg font-semibold text-blue-600"><?php echo htmlspecialchars($vehicle['nama_kendaraan']); ?> (<?php echo htmlspecialchars($vehicle['nomor_polisi']); ?>)</p>

            <button id="startButton" class="mt-6 w-full bg-green-500 text-white py-3 px-4 rounded-md text-lg font-semibold">
                Mulai Pelacakan
            </button>
            <button id="stopButton" class="mt-2 w-full bg-red-500 text-white py-3 px-4 rounded-md text-lg font-semibold" style="display:none;">
                Hentikan Pelacakan
            </button>
            <p id="status" class="mt-4 text-sm text-gray-500">Status: Tidak Aktif</p>
        <?php else: ?>
            <p class="text-red-500 mt-4">Anda tidak terhubung dengan kendaraan manapun. Hubungi manajer.</p>
        <?php endif; ?>

        <a href="logout.php" class="text-blue-500 text-sm mt-6 inline-block">Logout</a>
    </div>

    <script>
        const startButton = document.getElementById('startButton');
        const stopButton = document.getElementById('stopButton');
        const statusEl = document.getElementById('status');
        let watchId = null; // Menyimpan ID dari pelacak

        // 1. Fungsi untuk MENGIRIM lokasi ke server (PHP)
        async function sendLocation(position) {
            const { latitude, longitude } = position.coords;
            statusEl.textContent = `Status: Mengirim... (Lat: ${latitude.toFixed(4)}, Lng: ${longitude.toFixed(4)})`;

            try {
                // Kita gunakan method POST untuk mengirim data
                const response = await fetch('api/update_location.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ lat: latitude, lng: longitude })
                });
                const result = await response.json();
                if(result.status === 'success') {
                    statusEl.textContent = `Status: Lokasi terkirim! (${new Date().toLocaleTimeString()})`;
                } else {
                    statusEl.textContent = `Status: Gagal mengirim. (${result.message})`;
                }
            } catch (error) {
                statusEl.textContent = `Status: Error. Periksa koneksi internet.`;
                console.error('Error sending location:', error);
            }
        }

        // 2. Fungsi saat tombol START diklik
        startButton.addEventListener('click', () => {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung Geolocation.');
                return;
            }

            // Ini adalah kunci pelacakan otomatis: watchPosition
            // Akan memanggil sendLocation SETIAP KALI HP mendeteksi pergerakan
            watchId = navigator.geolocation.watchPosition(
                sendLocation, // Sukses: panggil fungsi sendLocation
                (error) => { // Gagal
                    statusEl.textContent = `Status: Error GPS (${error.message})`;
                },
                { // Opsi
                    enableHighAccuracy: true, // GPS Akurat
                    timeout: 10000,           // Waktu tunggu 10 detik
                    maximumAge: 0             // Jangan pakai cache lokasi
                }
            );

            // Ubah tampilan tombol
            startButton.style.display = 'none';
            stopButton.style.display = 'block';
            statusEl.textContent = 'Status: Pelacakan AKTIF. Mencari sinyal GPS...';
        });

        // 3. Fungsi saat tombol STOP diklik
        stopButton.addEventListener('click', () => {
            if (watchId) {
                navigator.geolocation.clearWatch(watchId); // Hentikan pelacakan
                watchId = null;
            }
            
            // Ubah tampilan tombol
            startButton.style.display = 'block';
            stopButton.style.display = 'none';
            statusEl.textContent = 'Status: Tidak Aktif';
        });
    </script>
</body>
</html>