<?php

use App\Http\Controllers\GuruController;
use App\Http\Controllers\KelasController;
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

Route::get('/students', [StudentController::class, 'index'])->name('cms.student');
Route::post('/students', [StudentController::class, 'store'])->name('cms.student.store');
Route::get('/tambahsiswa', [StudentController::class, 'tambahsiswa'])->name('cms.student.create');
Route::get('/students/{siswa}/edit', [StudentController::class, 'edit'])->name('cms.student.edit');
Route::put('/students/{siswa}', [StudentController::class, 'update'])->name('cms.student.update');
Route::delete('/students/{siswa}', [StudentController::class, 'destroy'])->name('cms.student.destroy');

Route::get('/teachers', [GuruController::class, 'index'])->name('cms.teachers');
Route::post('/teachers', [GuruController::class, 'store'])->name('cms.teachers.store');
Route::get('/tambahguru', [GuruController::class, 'tambahguru'])->name('cms.teachers.create');
Route::get('/teachers/{guru}/edit', [GuruController::class, 'edit'])->name('cms.teachers.edit');
Route::put('/teachers/{guru}', [GuruController::class, 'update'])->name('cms.teachers.update');
Route::delete('/teachers/{guru}', [GuruController::class, 'destroy'])->name('cms.teachers.destroy');

Route::get('/classes', [KelasController::class, 'index'])->name('cms.classes');
Route::post('/classes', [KelasController::class, 'store'])->name('cms.classes.store');
Route::get('/tambahkelas', [KelasController::class, 'tambahkelas'])->name('cms.classes.create');
Route::get('/classes/{kelas}/edit', [KelasController::class, 'edit'])->name('cms.classes.edit');
Route::put('/classes/{kelas}', [KelasController::class, 'update'])->name('cms.classes.update');
Route::delete('/classes/{kelas}', [KelasController::class, 'destroy'])->name('cms.classes.destroy');

Route::get('/users', [UserController::class, 'index'])->name('cms.users');
Route::post('/users', [UserController::class, 'store'])->name('cms.users.store');
Route::get('/tambahuser', [UserController::class, 'tambahuser'])->name('cms.users.tambah');
Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('cms.users.edit');
Route::put('/users/{user}', [UserController::class, 'update'])->name('cms.users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('cms.users.destroy');

Route::get('/rekap', function () {
    return view('cms.rekap');
})->name('cms.rekap');
