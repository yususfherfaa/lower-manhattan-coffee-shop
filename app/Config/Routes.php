<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

$routes->get('/', 'Auth::index', ['filter' => 'honeypot']);
$routes->post('/login/process', 'Auth::processLogin', ['filter' => 'honeypot']);
$routes->get('/logout', 'Auth::logout');


/*
|--------------------------------------------------------------------------
| ORDER CUSTOMER
|--------------------------------------------------------------------------
*/

$routes->get('/order/(:any)', 'Order::index/$1');

$routes->get('/cart/add/(:any)/(:any)', 'Cart::add/$1/$2');

$routes->post('/cart/note/(:any)/(:any)', 'Cart::note/$1/$2');

$routes->get('/cart/qty/(:any)/(:any)/(:any)', 'Cart::qty/$1/$2/$3');

$routes->get('/cart/remove/(:any)/(:any)', 'Cart::remove/$1/$2');

$routes->get('cart/reset/(:segment)', 'Cart::reset/$1');

$routes->get('/cart/(:any)', 'Cart::index/$1', ['filter' => 'honeypot']);

$routes->post('/checkout/(:any)', 'Checkout::index/$1', ['filter' => 'honeypot']);

$routes->get('/payment-qris/(:segment)/(:segment)','PaymentQris::index/$1/$2');

$routes->get('/order-success/(:any)/(:any)', 'OrderSuccess::index/$1/$2');

$routes->get('/cek-status/(:segment)', 'OrderStatus::cek/$1');

/*
|--------------------------------------------------------------------------
| LAPORAN
|--------------------------------------------------------------------------
*/

$routes->get('/laporan', 'Kasir\Laporan::index', ['filter' => ['auth', 'role:admin,kasir']]);

$routes->get('/laporan/pdf', 'Kasir\Laporan::pdf', ['filter' => ['auth', 'role:admin,kasir']]);

/*
|--------------------------------------------------------------------------
| LAPORAN STOK & AKTIVITAS PRODUK
|--------------------------------------------------------------------------
*/

$routes->get('/laporan-stok', 'Admin\LaporanStok::index', ['filter' => ['auth', 'role:admin']]);

$routes->get('/laporan-stok/pdf', 'Admin\LaporanStok::pdf', ['filter' => ['auth', 'role:admin']]);

/*
|--------------------------------------------------------------------------
| ADMIN AND KASIR ROUTES
|--------------------------------------------------------------------------
*/

$routes->group('admin', ['filter' => ['auth', 'role:admin']], static function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');
    $routes->get('recommendation-acceptance', 'Admin\RecommendationAcceptance::index');
    $routes->get('analitik-penjualan', 'Admin\AnalitikPenjualan::index');
    $routes->get('evaluasi-model', 'Admin\EvaluasiModel::index');

    $routes->get('kasir', 'Admin\Kasir::index');
    $routes->get('kasir/list', 'Admin\Kasir::index');
    $routes->get('kasir/add', 'Admin\Kasir::add');
    $routes->post('kasir/save', 'Admin\Kasir::save');
    $routes->get('kasir/edit/(:segment)', 'Admin\Kasir::edit/$1');
    $routes->post('kasir/update/(:segment)', 'Admin\Kasir::update/$1');
    $routes->get('kasir/delete/(:segment)', 'Admin\Kasir::delete/$1');

    $routes->get('qr-meja', 'Admin\Setting::qrMeja', ['filter' => ['auth', 'role:admin']]);
    $routes->get('settings', 'Admin\Setting::index');
    $routes->post('settings/update', 'Admin\Setting::update');
});

$routes->group('kasir', ['filter' => ['auth', 'role:kasir']], static function ($routes) {
    $routes->get('dashboard', 'Kasir\Dashboard::index');

    $routes->get('menu', 'Kasir\Menu::index');
    $routes->post('menu/add', 'Kasir\Menu::add');
    $routes->get('menu/edit/(:segment)', 'Kasir\Menu::edit/$1');
    $routes->post('menu/update/(:segment)', 'Kasir\Menu::update/$1');
    $routes->get('menu/delete/(:segment)', 'Kasir\Menu::delete/$1');
    $routes->post('menu/options/add/(:segment)', 'Kasir\ProductOption::add/$1', ['filter' => 'csrf']);
    $routes->post('menu/options/update/(:segment)', 'Kasir\ProductOption::update/$1', ['filter' => 'csrf']);
    $routes->post('menu/options/delete/(:segment)', 'Kasir\ProductOption::delete/$1', ['filter' => 'csrf']);
    $routes->post('menu/options/activate/(:segment)', 'Kasir\ProductOption::activate/$1', ['filter' => 'csrf']);

    $routes->get('pesanan', 'Kasir\Pesanan::index');
    $routes->post('pesanan/batalkan/(:segment)', 'Kasir\Pesanan::cancel/$1', ['filter' => 'csrf']);
    $routes->get('detail-pesanan/(:segment)', 'Kasir\DetailPesanan::index/$1');
    $routes->get('bayar/(:segment)', 'Kasir\Bayar::index/$1');
    $routes->post('bayar/process/(:segment)', 'Kasir\Bayar::process/$1');
    $routes->get('struk/(:segment)', 'Kasir\Struk::index/$1');

});

$routes->get('/api/notifications', 'NotificationAPI::getNotifications', ['filter' => ['auth', 'role:kasir']]);
$routes->post('/api/notifications/mark-read', 'NotificationAPI::markAsRead', ['filter' => ['auth', 'role:kasir']]);
$routes->post('/api/notifications/mark-all-read', 'NotificationAPI::markAllAsRead', ['filter' => ['auth', 'role:kasir']]);

// Rekomendasi berdasarkan jam real-time
$routes->get('/api/rekomendasi', 'RekomendasiAPI::index');

// Rekomendasi berdasarkan jam tertentu (untuk testing)
// Contoh: /api/rekomendasi/jam/8  → rekomendasi pagi
$routes->get('/api/rekomendasi/jam/(:num)', 'RekomendasiAPI::byJam/$1');
$routes->post('/recommendation-log/select', 'RecommendationLog::select', ['filter' => 'csrf']);
