// Impor library Workbox dari CDN
importScripts('https://storage.googleapis.com/workbox-cdn/releases/6.4.1/workbox-sw.js');

if (workbox) {
  console.log(`Workbox LacakPro berhasil dimuat!`);

  // --- STRATEGI CACHING ASET STATIS ---
  // (Tailwind CSS, JS kustom, Font, Gambar)
  // Strategi: StaleWhileRevalidate (Cepat, tapi tetap update di background)
  workbox.routing.registerRoute(
    ({ request }) => request.destination === 'style' ||
                     request.destination === 'script' ||
                     request.destination === 'image' ||
                     request.destination === 'font',
    new workbox.strategies.StaleWhileRevalidate({
      cacheName: 'lacakpro-static-assets',
    })
  );

  // --- STRATEGI CACHING HALAMAN PHP ---
  // (dashboard_manager.php, histori.php, dll)
  // Strategi: NetworkFirst (Coba ambil data baru dulu, kalau gagal/offline baru pakai cache)
  workbox.routing.registerRoute(
    ({ request }) => request.destination === 'document',
    new workbox.strategies.NetworkFirst({
      cacheName: 'lacakpro-pages',
    })
  );

  // --- STRATEGI CACHING API (PENTING!) ---
  // Ini untuk semua file di C:\xampp\htdocs\LacakPro\api\
  // Khususnya yang GET data (get_locations.php, get_history.php)
  // Strategi: NetworkFirst (Sama seperti halaman, utamakan data baru)
  workbox.routing.registerRoute(
    ({ url }) => url.pathname.startsWith('/LacakPro/api/'),
    new workbox.strategies.NetworkFirst({
      cacheName: 'lacakpro-api-data',
    })
  );

} else {
  console.log(`Workbox gagal dimuat.`);
}