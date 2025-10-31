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

    <main class="container mx-auto p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
    
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
    </main>
</div>

<script>
    // --- SCRIPT UNTUK HALAMAN MANAJEMEN ---

    const daftarSupirEl = document.getElementById('daftarSupir');
    const daftarKendaraanEl = document.getElementById('daftarKendaraan');
    const selectDriverEl = document.getElementById('driver_id');
    const formTambahSupir = document.getElementById('formTambahSupir');
    const formTambahKendaraan = document.getElementById('formTambahKendaraan');
    const statusSupirEl = document.getElementById('statusSupir');
    const statusKendaraanEl = document.getElementById('statusKendaraan');

    // Fungsi untuk memuat semua data (supir & kendaraan) saat halaman dibuka
    async function loadData() {
        try {
            const response = await fetch('api/get_data_manajemen.php');
            const result = await response.json();

            if (result.status !== 'success') throw new Error(result.message);

            // 1. Isi Daftar Supir di Kolom 1
            daftarSupirEl.innerHTML = '';
            selectDriverEl.innerHTML = '<option value="">Pilih Supir</option>'; // Reset dropdown
            
            result.data.supir.forEach(supir => {
                // Tampilkan di daftar
                const div = document.createElement('div');
                div.className = 'p-2 border rounded';
                div.innerHTML = `<strong>${supir.nama_lengkap}</strong> (${supir.username})`;
                daftarSupirEl.appendChild(div);
                
                // Masukkan ke pilihan <select> di form kendaraan
                const option = document.createElement('option');
                option.value = supir.id;
                option.textContent = `${supir.nama_lengkap} (${supir.username})`;
                selectDriverEl.appendChild(option);
            });

            // 2. Isi Daftar Kendaraan di Kolom 2
            daftarKendaraanEl.innerHTML = '';
            result.data.kendaraan.forEach(v => {
                const div = document.createElement('div');
                div.className = 'p-2 border rounded';
                div.innerHTML = `<strong>${v.nama_kendaraan}</strong> (${v.nomor_polisi})<br><span class="text-sm text-gray-500">Supir: ${v.nama_lengkap}</span>`;
                daftarKendaraanEl.appendChild(div);
            });

        } catch (error) {
            daftarSupirEl.innerHTML = `<p class="text-red-500">${error.message}</p>`;
            daftarKendaraanEl.innerHTML = `<p class="text-red-500">${error.message}</p>`;
        }
    }

    // Event listener untuk form Tambah Supir
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
                formTambahSupir.reset(); // Kosongkan form
                loadData(); // Muat ulang semua data
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            statusSupirEl.textContent = `Error: ${error.message}`;
            statusSupirEl.className = 'text-sm mt-2 text-red-600';
        }
    });
    
    // Event listener untuk form Tambah Kendaraan
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
                formTambahKendaraan.reset(); // Kosongkan form
                loadData(); // Muat ulang semua data
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            statusKendaraanEl.textContent = `Error: ${error.message}`;
            statusKendaraanEl.className = 'text-sm mt-2 text-red-600';
        }
    });


    // Panggil fungsi loadData() saat halaman pertama kali dibuka
    document.addEventListener('DOMContentLoaded', loadData);
</script>

<?php
// Panggil Footer
include 'includes/footer.php'; 
?>