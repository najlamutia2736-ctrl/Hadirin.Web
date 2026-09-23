<?php

use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ============================================
// HALAMAN UTAMA
// ============================================
Route::get('/', function () {
    return view('halaman-awal');
})->name('home');

// ============================================
// AUTENTIKASI
// ============================================
Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/login/konfirmasi', function () {
    return view('login2');
})->name('login2');

// ============================================
// BERANDA (DASHBOARD)
// ============================================
Route::get('/beranda', function () {
    return view('beranda');
})->name('beranda');

// ============================================
// 🔥 HALAMAN IDENTITAS SISWA (BARU)
// ============================================
Route::get('/identitas-siswa', function () {
    return view('identitas-siswa');
})->name('identitas.siswa');

// ============================================
// ABSENSI SISWA
// ============================================
Route::get('/absen/siswa/qr', function () {
    return view('absen-siswa-qr');
})->name('absen.siswa.qr');

Route::get('/absen/verifikasi', function () {
    return view('absen-siswa-verifikasi');
})->name('absen.verifikasi');

// ============================================
// DASHBOARD GURU & ADMIN
// ============================================
Route::get('/dashboard/guru', function () {
    return view('dashboard-guru');
})->name('dashboard.guru');

Route::get('/dashboard/admin', function () {
    return view('dashboard-admin');
})->name('dashboard.admin');

// ============================================
// REKAP & LAPORAN
// ============================================
Route::get('/rekap/laporan', function () {
    return view('rekap-laporan');
})->name('rekap.laporan');

// ============================================
// IDENTITAS GURU (BARU)
// ============================================
Route::get('/identitas-guru', function () {
    return view('identitas-guru');
})->name('identitas.guru');

Route::get('/dashboard', function () {
    return view('cms.dashboard');
})->name('cms.dashboard');

Route::get('/students', [StudentController::class, 'index'])->name('cms.students');
Route::post('/students', [StudentController::class, 'store'])->name('cms.students.store');

Route::get('/teachers', function () {
    return view('cms.teachers');
})->name('cms.teachers');

Route::get('/classes', function () {
    return view('cms.classes');
})->name('cms.classes');

Route::get('/users', [UserController::class, 'index'])->name('cms.users');
Route::post('/users', [UserController::class, 'store'])->name('cms.users.store');

Route::get('/rekap', function () {
    return view('cms.rekap');
})->name('cms.rekap');
