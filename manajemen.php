<?php
// manajemen.php
include 'includes/db_config.php';

// Pastikan hanya manajer yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: login.php");
    exit;
}

// Set Judul Halaman
$page_title = 'Manajemen Data';

// Panggil Header
include 'includes/header.php';
?>

<div class="min-h-screen">
    <header class="bg-white shadow-md w-full">
        <div class="container mx-auto px-6 py-4 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-blue-600">Manajemen Data</h1>
            <div>
                <a href="dashboard_manager.php" class="text-blue-500 hover:underline mr-4">Kembali ke Peta</a>
                <a href="logout.php" class="ml-4 text-blue-500 hover:underline">Logout</a>
            </div>
        </div>
    </header>

    <main class="container mx-auto p-6">

        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4 border-b pb-2">Buat Tugas Perjalanan Baru</h2>
            <form id="formTambahTugas">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                    <div>
                        <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Pilih Kendaraan</label>
                        <select id="vehicle_id" name="vehicle_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                            <option value="">Memuat kendaraan...</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label for="waktu_dijadwalkan" class="block text-sm font-medium text-gray-700">Waktu Berangkat</label>
                        <input type="datetime-local" id="waktu_dijadwalkan" name="waktu_dijadwalkan" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                    <div>
                        <label for="alamat_awal" class="block text-sm font-medium text-gray-700">Alamat Awal</label>
                        <textarea id="alamat_awal" name="alamat_awal" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required></textarea>
                    </div>
                    <div>
                        <label for="alamat_tujuan" class="block text-sm font-medium text-gray-700">Alamat Tujuan</label>
                        <textarea id="alamat_tujuan" name="alamat_tujuan" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required></textarea>
                    </div>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Buat Tugas</button>
                <p id="statusTugas" class="text-sm mt-2"></p>
            </form>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4 border-b pb-2">Kelola Supir</h2>

                <form id="formTambahSupir" class="mb-6">
                    <h3 class="text-lg font-medium mb-2">Tambah Supir Baru</h3>
                    <div class="mb-3">
                        <label for="nama_supir" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" id="nama_supir" name="nama_lengkap" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-3">
                        <label for="username_supir" class="block text-sm font-medium text-gray-700">Username (untuk login)</label>
                        <input type="text" id="username_supir" name="username" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_supir" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password_supir" name="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Simpan Supir</button>
                    <p id="statusSupir" class="text-sm mt-2"></p>
                </form>

                <div>
                    <h3 class="text-lg font-medium mb-2">Daftar Supir</h3>
                    <div id="daftarSupir" class="max-h-60 overflow-y-auto space-y-2">
                        </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4 border-b pb-2">Kelola Kendaraan</h2>
                
                <form id="formTambahKendaraan" class="mb-6">
                    <h3 class="text-lg font-medium mb-2">Tambah Kendaraan Baru</h3>
                    <div class="mb-3">
                        <label for="nama_kendaraan" class="block text-sm font-medium text-gray-700">Nama Kendaraan (cth: Truk Box 01)</label>
                        <input type="text" id="nama_kendaraan" name="nama_kendaraan" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-3">
                        <label for="nomor_polisi" class="block text-sm font-medium text-gray-700">Nomor Polisi</label>
                        <input type="text" id="nomor_polisi" name="nomor_polisi" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-3">
                        <label for="driver_id" class="block text-sm font-medium text-gray-700">Pilih Supir</label>
                        <select id="driver_id" name="driver_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                            <option value="">Memuat supir...</option>
                            </select>
                    </div>
                    <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">Simpan Kendaraan</button>
                    <p id="statusKendaraan" class="text-sm mt-2"></p>
                </form>
                
                <div>
                    <h3 class="text-lg font-medium mb-2">Daftar Kendaraan</h3>
                    <div id="daftarKendaraan" class="max-h-60 overflow-y-auto space-y-2">
                        </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // --- Variabel Elemen ---
    const daftarSupirEl = document.getElementById('daftarSupir');
    const daftarKendaraanEl = document.getElementById('daftarKendaraan');
    const selectDriverEl = document.getElementById('driver_id');
    const selectVehicleEl = document.getElementById('vehicle_id'); // Baru
    
    const formTambahSupir = document.getElementById('formTambahSupir');
    const formTambahKendaraan = document.getElementById('formTambahKendaraan');
    const formTambahTugas = document.getElementById('formTambahTugas'); // Baru
    
    const statusSupirEl = document.getElementById('statusSupir');
    const statusKendaraanEl = document.getElementById('statusKendaraan');
    const statusTugasEl = document.getElementById('statusTugas'); // Baru

    // --- Fungsi Load Data (Di-UPDATE) ---
    async function loadData() {
        try {
            const response = await fetch('api/get_data_manajemen.php');
            const result = await response.json();

            if (result.status !== 'success') throw new Error(result.message);

            // Reset semua list
            daftarSupirEl.innerHTML = '';
            daftarKendaraanEl.innerHTML = '';
            selectDriverEl.innerHTML = '<option value="">Pilih Supir</option>';
            selectVehicleEl.innerHTML = '<option value="">Pilih Kendaraan</option>';
            
            // 1. Isi Daftar Supir (Form Kendaraan)
            result.data.supir.forEach(supir => {
                const div = document.createElement('div');
                div.className = 'p-2 border rounded';
                div.innerHTML = `<strong>${supir.nama_lengkap}</strong> (${supir.username})`;
                daftarSupirEl.appendChild(div);
                
                const option = document.createElement('option');
                option.value = supir.id;
                option.textContent = `${supir.nama_lengkap} (${supir.username})`;
                selectDriverEl.appendChild(option);
            });

            // 2. Isi Daftar Kendaraan (Form Tugas)
            result.data.kendaraan.forEach(v => {
                const div = document.createElement('div');
                div.className = 'p-2 border rounded';
                div.innerHTML = `<strong>${v.nama_kendaraan}</strong> (${v.nomor_polisi})<br><span class="text-sm text-gray-500">Supir: ${v.nama_lengkap}</span>`;
                daftarKendaraanEl.appendChild(div);

                // --- BARU: Isi dropdown di form TUGAS ---
                const option = document.createElement('option');
                option.value = v.vehicle_id;
                option.textContent = `${v.nama_kendaraan} (${v.nomor_polisi})`;
                option.dataset.driverId = v.driver_id; // Simpan driver_id di sini
                selectVehicleEl.appendChild(option);
            });

        } catch (error) {
            daftarSupirEl.innerHTML = `<p class="text-red-500">${error.message}</p>`;
            daftarKendaraanEl.innerHTML = `<p class="text-red-500">${error.message}</p>`;
        }
    }

    // --- Event Listener Form Tambah Supir (Tidak Berubah) ---
    formTambahSupir.addEventListener('submit', async (e) => {
        e.preventDefault();
        statusSupirEl.textContent = 'Menyimpan...';
        const formData = new FormData(formTambahSupir);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('api/tambah_supir.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.status === 'success') {
                statusSupirEl.textContent = 'Sukses! Supir ditambahkan.';
                statusSupirEl.className = 'text-sm mt-2 text-green-600';
                formTambahSupir.reset();
                loadData(); // Muat ulang semua data
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            statusSupirEl.textContent = `Error: ${error.message}`;
            statusSupirEl.className = 'text-sm mt-2 text-red-600';
        }
    });
    
    // --- Event Listener Form Tambah Kendaraan (Tidak Berubah) ---
    formTambahKendaraan.addEventListener('submit', async (e) => {
        e.preventDefault();
        statusKendaraanEl.textContent = 'Menyimpan...';
        const formData = new FormData(formTambahKendaraan);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('api/tambah_kendaraan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.status === 'success') {
                statusKendaraanEl.textContent = 'Sukses! Kendaraan ditambahkan.';
                statusKendaraanEl.className = 'text-sm mt-2 text-green-600';
                formTambahKendaraan.reset();
                loadData(); // Muat ulang semua data
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            statusKendaraanEl.textContent = `Error: ${error.message}`;
            statusKendaraanEl.className = 'text-sm mt-2 text-red-600';
        }
    });

    // --- BARU: Event Listener Form Tambah Tugas ---
    formTambahTugas.addEventListener('submit', async (e) => {
        e.preventDefault();
        statusTugasEl.textContent = 'Membuat tugas...';

        const formData = new FormData(formTambahTugas);
        const data = Object.fromEntries(formData.entries());

        // Ambil driver_id dari data-attribute <option> yang dipilih
        const selectedOption = selectVehicleEl.options[selectVehicleEl.selectedIndex];
        if (selectedOption) {
            data.driver_id = selectedOption.dataset.driverId;
        }

        try {
            const response = await fetch('api/tambah_tugas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.status === 'success') {
                statusTugasEl.textContent = 'Sukses! Tugas baru telah dibuat.';
                statusTugasEl.className = 'text-sm mt-2 text-green-600';
                formTambahTugas.reset();
                // Tidak perlu loadData() di sini
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            statusTugasEl.textContent = `Error: ${error.message}`;
            statusTugasEl.className = 'text-sm mt-2 text-red-600';
        }
    });

    // Panggil fungsi loadData() saat halaman pertama kali dibuka
    document.addEventListener('DOMContentLoaded', loadData);
</script>

<?php
// Panggil Footer
include 'includes/footer.php'; 
?>