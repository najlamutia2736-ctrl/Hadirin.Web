<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('halaman-awal');
})->name('home');

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/login/konfirmasi', function () {
    return view('login2');
})->name('login2');

Route::get('/beranda', function () {
    return view('beranda');
})->name('beranda');

Route::get('/absen/siswa/qr', function () {
    return view('absen-siswa-qr');
})->name('absen.siswa.qr');

Route::get('/absen/verifikasi', function () {
    return view('absen-siswa-verifikasi');
})->name('absen.verifikasi');

Route::get('/dashboard/guru', function () {
    return view('dashboard-guru');
})->name('dashboard.guru');

Route::get('/rekap/laporan', function () {
    return view('rekap-laporan');
})->name('rekap.laporan');

Route::get('/dashboard/admin', function() {
    return view('dashboard-admin');
})->name('dashboard.admin');