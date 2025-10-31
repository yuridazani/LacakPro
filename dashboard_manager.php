<?php
// dashboard_manager.php
include 'includes/db_config.php';

// Pastikan hanya manajer yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Manajer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map { height: 100%; }
        /* Fix layout responsif */
        html, body { height: 100%; margin: 0; padding: 0; }
        .content-wrapper { height: calc(100% - 64px); } /* 100% tinggi dikurangi header */
    </style>
</head>
<body class="flex flex-col h-screen">
    <header class="bg-white shadow-md w-full h-16 flex items-center justify-between px-6 z-10">
        <h1 class="text-2xl font-bold text-blue-600">Dashboard Manajer</h1>
        <div>
            <a href="manajemen.php" class="text-blue-500 hover:underline mr-4">Manajemen</a>
            
            <span class="text-gray-700">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <a href="logout.php" class="ml-4 text-blue-500 hover:underline">Logout</a>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden content-wrapper">
        <aside class="w-1/4 max-w-xs bg-gray-50 p-4 overflow-y-auto">
            <h2 class="text-lg font-semibold mb-4">Daftar Kendaraan Aktif</h2>
            <div id="vehicle-list" class="space-y-3">
                <p class="text-gray-500">Memuat data...</p>
            </div>
        </aside>

        <main id="map" class="flex-1 h-full"></main>
    </div>

    <script>
        // 1. Inisialisasi Peta
        const map = L.map('map').setView([-6.200000, 106.816666], 11); // Set ke Jakarta
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        // Objek untuk menyimpan semua marker, agar bisa di-update
        let markers = {}; 
        const vehicleListEl = document.getElementById('vehicle-list');

        // 2. Fungsi untuk MENGAMBIL lokasi SEMUA kendaraan
        async function fetchAllLocations() {
            try {
                const response = await fetch('api/get_locations.php');
                const result = await response.json();

                if (result.status === 'success') {
                    vehicleListEl.innerHTML = ''; // Kosongkan daftar
                    const vehicles = result.data;

                    if (vehicles.length === 0) {
                        vehicleListEl.innerHTML = '<p class="text-gray-500">Belum ada data lokasi.</p>';
                    }

                    vehicles.forEach(vehicle => {
                        const newPosition = [parseFloat(vehicle.latitude), parseFloat(vehicle.longitude)];
                        const popupContent = `
                            <b>${vehicle.nama_kendaraan}</b><br>
                            ${vehicle.nomor_polisi} (${vehicle.nama_lengkap})<br>
                            Terakhir update: ${vehicle.timestamp}
                        `;

                        // Cek apakah marker sudah ada
                        if (markers[vehicle.vehicle_id]) {
                            // Jika sudah ada, pindahkan marker
                            markers[vehicle.vehicle_id].setLatLng(newPosition);
                            markers[vehicle.vehicle_id].setPopupContent(popupContent);
                        } else {
                            // Jika belum ada, buat marker baru
                            markers[vehicle.vehicle_id] = L.marker(newPosition)
                                .addTo(map)
                                .bindPopup(popupContent);
                        }
                        
                        // Tambahkan ke daftar sidebar
const listItem = document.createElement('div');
                        listItem.className = 'bg-white p-3 rounded shadow border-l-4 border-blue-500'; // Hapus cursor-pointer
                        
                        // --- MODIFIKASI DI SINI ---
                        listItem.innerHTML = `
                            <div class="flex justify-between items-center">
                                <h3 class="font-semibold">${vehicle.nama_kendaraan}</h3>
                                <a href="histori.php?vehicle_id=${vehicle.vehicle_id}&name=${encodeURIComponent(vehicle.nama_kendaraan)}" 
                                   title="Lihat Histori" 
                                   class="text-xs text-blue-500 hover:underline">
                                   Histori
                                </a>
                            </div>
                            <p class="text-sm text-gray-600">${vehicle.nama_lengkap}</p>
                            <p class="text-xs text-gray-400">Update: ${vehicle.timestamp}</p>
                        `;
                        
                        // Tambah aksi klik untuk zoom ke marker (TETAP ADA)
                        listItem.addEventListener('click', (e) => {
                            // Cek agar klik pada link "Histori" tidak ikut men-trigger zoom
                            if (e.target.tagName !== 'A') { 
                                map.setView(newPosition, 16); // Zoom level 16
                                markers[vehicle.vehicle_id].openPopup();
                            }
                        });
                        vehicleListEl.appendChild(listItem);
                    });
                } else {
                     vehicleListEl.innerHTML = `<p class="text-red-500">Gagal memuat: ${result.message}</p>`;
                }
            } catch (error) {
                console.error("Error fetching locations:", error);
                vehicleListEl.innerHTML = '<p class="text-red-500">Error! Cek koneksi.</p>';
            }
        }

        // 3. Auto-Refresh (Kunci Otomatis untuk Manajer)
        // Panggil fungsi pertama kali
        fetchAllLocations();
        
        // Panggil fungsi setiap 10 detik (10000 ms)
        setInterval(fetchAllLocations, 10000); 

    </script>
</body>
</html>