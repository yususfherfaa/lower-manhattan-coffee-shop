<?php
/*
|--------------------------------------------------------------------------
| TAMBAHKAN BARIS INI ke dalam file app/Config/Routes.php
| Letakkan di bagian bawah, sebelum baris terakhir
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| API REKOMENDASI MENU (ML - KNN)
|--------------------------------------------------------------------------
*/

// Rekomendasi berdasarkan jam real-time
$routes->get('/api/rekomendasi', 'RekomendasiAPI::index');

// Rekomendasi berdasarkan jam tertentu (untuk testing)
// Contoh: /api/rekomendasi/jam/8  → rekomendasi pagi
$routes->get('/api/rekomendasi/jam/(:num)', 'RekomendasiAPI::byJam/$1');
