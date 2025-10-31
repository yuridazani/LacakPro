<?php
// (Mungkin ada kode session check di sini nanti)

// Set judul halaman default jika tidak ada
if (!isset($page_title)) {
    $page_title = 'LacakPro';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="manifest" href="/LacakPro/manifest.json">
    <meta name="theme-color" content="#4A90E2"> 
    
    <title><?php echo htmlspecialchars($page_title); ?> - LacakPro</title> 

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

</head>
<body class="bg-gray-100">