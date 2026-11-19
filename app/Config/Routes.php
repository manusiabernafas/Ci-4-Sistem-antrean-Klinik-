<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::beranda');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::prosesLogin');
$routes->post('logout', 'Auth::logout');

// Tanpa login: kios ambil nomor & layar TV (dilindungi kunci di alamat), tiket & cek status (token acak)
$routes->get('kios/(:segment)', 'Kios::index/$1');
$routes->post('kios/(:segment)', 'Kios::ambil/$1');
$routes->get('tiket/(:segment)', 'Kios::tiket/$1');
$routes->get('status/(:segment)', 'Kios::status/$1');
$routes->get('layar/(:segment)', 'Layar::index/$1');
$routes->get('layar/(:segment)/data', 'Layar::data/$1');

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('loket', 'Loket::index');
    $routes->post('loket/atur', 'Loket::atur');
    $routes->post('loket/panggil', 'Loket::panggil');
    $routes->post('loket/ulang/(:num)', 'Loket::ulang/$1');
    $routes->post('loket/selesai/(:num)', 'Loket::selesai/$1');
    $routes->post('loket/lewati/(:num)', 'Loket::lewati/$1');
    $routes->get('profil', 'Profil::index');
    $routes->post('profil', 'Profil::password');
});

$routes->group('admin', ['filter' => 'auth:admin'], static function ($routes) {
    $routes->get('/', 'Admin::index');
    $routes->get('poli', 'Admin::poli');
    $routes->post('poli', 'Admin::simpanPoli');
    $routes->get('pengguna', 'Admin::pengguna');
    $routes->post('pengguna', 'Admin::simpanPengguna');
    $routes->get('pengaturan', 'Admin::pengaturan');
    $routes->post('pengaturan', 'Admin::simpanPengaturan');
    $routes->get('laporan', 'Admin::laporan');
    $routes->get('laporan/csv', 'Admin::laporanCsv');
});
