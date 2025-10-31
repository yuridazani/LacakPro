<?php
// histori.php
include 'includes/db_config.php';

// Pastikan hanya manajer yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: login.php");
    exit;
}

// Ambil data dari URL
$vehicle_id = isset($_GET['vehicle_id']) ? $_GET['vehicle_id'] : 0;
$vehicle_name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'Tidak Diketahui';

// Set tanggal hari ini sebagai default
$default_date = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histori Rute - <?php echo $vehicle_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map { height: calc(100vh - 120px); } /* Full tinggi dikurangi header */
    </style>
</head>
<body class="bg-gray-100">

    <header class="bg-white shadow-md w-full">
        <div class="container mx-auto px-6 py-4 flex flex-col md:flex-row items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-blue-600">Histori Rute</h1>
                <p class="text-lg text-gray-700"><?php echo $vehicle_name; ?></p>
            </div>
            <div class="flex items-center space-x-4 mt-4 md:mt-0">
                <input type="date" id="datePicker" value="<?php echo $default_date; ?>" class="border border-gray-300 rounded-md px-3 py-2">
                <button id="showRouteButton" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                    Tampilkan Rute
                </button>
                <a href="dashboard_manager.php" class="text-blue-500 hover:underline">Kembali</a>
            </div>
        </div>
    </header>

    <main class="p-4">
        <div id="map" class="w-full rounded-lg shadow-lg"></div>
        <p id="status" class="text-center text-gray-600 mt-2"></p>
    </main>

    <script>
        // Ambil data dari PHP
        const vehicleId = <?php echo $vehicle_id; ?>;
        
        // Inisialisasi Peta
        const map = L.map('map').setView([-6.200000, 106.816666], 11); // Set ke Jakarta
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        const datePicker = document.getElementById('datePicker');
        const showRouteButton = document.getElementById('showRouteButton');
        const statusEl = document.getElementById('status');
        
        // Layer untuk menyimpan rute (garis) dan marker
        let routeLayer = L.layerGroup().addTo(map);

        // Fungsi untuk mengambil data dan menggambar rute
        async function fetchAndDrawRoute() {
            const selectedDate = datePicker.value;
            if (!selectedDate) {
                alert('Silakan pilih tanggal.');
                return;
            }

            statusEl.textContent = 'Memuat data rute...';
            routeLayer.clearLayers(); // Hapus rute lama (jika ada)

            try {
                const response = await fetch(`api/get_history.php?vehicle_id=${vehicleId}&date=${selectedDate}`);
                const result = await response.json();

                if (result.status === 'error') {
                    throw new Error(result.message);
                }

                const locations = result.data;
                
                // 1. Buat array koordinat untuk garis (Polyline)
                const coordinates = locations.map(loc => [parseFloat(loc.latitude), parseFloat(loc.longitude)]);
                
                if (coordinates.length === 0) {
                    throw new Error('Tidak ada data pada tanggal ini.');
                }

                // 2. Gambar garis rute
                const polyline = L.polyline(coordinates, { color: 'blue', weight: 5 }).addTo(routeLayer);
                
                // 3. Tambah marker Start (Titik pertama)
                const startPoint = coordinates[0];
                L.marker(startPoint).addTo(routeLayer)
                    .bindPopup(`<b>Mulai:</b> ${locations[0].timestamp}`)
                    .openPopup();

                // 4. Tambah marker End (Titik terakhir)
                const endPoint = coordinates[coordinates.length - 1];
                L.marker(endPoint).addTo(routeLayer)
                    .bindPopup(`<b>Selesai:</b> ${locations[locations.length - 1].timestamp}`);
                
                // 5. Zoom peta agar pas dengan rute
                map.fitBounds(polyline.getBounds());
                
                statusEl.textContent = `Menampilkan rute untuk tanggal ${selectedDate}.`;

            } catch (error) {
                statusEl.textContent = `Error: ${error.message}`;
            }
        }

        // Tambah event listener ke tombol
        showRouteButton.addEventListener('click', fetchAndDrawRoute);
        
        // (Opsional) Langsung tampilkan rute untuk hari ini saat halaman dimuat
        document.addEventListener('DOMContentLoaded', fetchAndDrawRoute);
    </script>
</body>
</html>