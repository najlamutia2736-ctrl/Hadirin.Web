<?php

use App\Http\Controllers\AbsensiSiswaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardGuruController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MataPelajaranController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/home', function () {
    return view('halaman-awal');
})->name('home');

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'store'])
    ->middleware('guest')
    ->name('login.store');

Route::post('/logout', [AuthController::class, 'destroy'])
    ->name('logout');

Route::get('/beranda', function () {
    return view('beranda');
})->name('beranda');

// Halaman Absensi Siswa.
Route::prefix('/absensi')
    ->name('absensi.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/', [AbsensiSiswaController::class, 'index'])
            ->name('index');

        Route::get('/identitas', [AbsensiSiswaController::class, 'identitas'])
            ->name('identitas');

        Route::post('/identitas', [AbsensiSiswaController::class, 'updateIdentitas'])
            ->name('identitas.update');

        Route::get('/metode', [AbsensiSiswaController::class, 'metode'])
            ->name('metode');

        Route::get('/scan-qr', function () {
            return view('absensi.scan-qr');
        })->name('scan-qr');

        Route::get('/id-unik', [AbsensiSiswaController::class, 'idUnik'])
            ->name('id-unik');

        Route::post('/id-unik', [AbsensiSiswaController::class, 'storeIdUnik'])
            ->name('id-unik.store');

        Route::get('/izin-sakit', [AbsensiSiswaController::class, 'izinSakit'])
            ->name('izin-sakit');

        Route::post('/izin-sakit', [AbsensiSiswaController::class, 'storeIzinSakit'])
            ->name('izin-sakit.store');

        Route::get('/notifikasi', [AbsensiSiswaController::class, 'notifikasi'])
            ->name('notifikasi');
    });

// Dashboard Guru.
Route::prefix('dashboard/guru')
    ->name('guru.')
    ->controller(DashboardGuruController::class)
    ->group(function () {
        Route::get('/', 'dashboard')
            ->name('dashboard');

        Route::get('/progres', 'progres')
            ->name('progres');

        Route::get('/kelola', 'kelola')
            ->name('kelola');

        Route::post('/kelola/siswa', 'storeSiswa')
            ->name('kelola.siswa.store');

        Route::put('/kelola/siswa/{siswa}', 'updateSiswa')
            ->name('kelola.siswa.update');

        Route::delete('/kelola/siswa/{siswa}', 'destroySiswa')
            ->name('kelola.siswa.destroy');

        Route::get('/laporan', 'laporan')
            ->name('laporan');

        Route::get('/laporan/export', 'exportLaporan')
            ->name('laporan.export');

        Route::get('/realtime', 'realtime')
            ->name('realtime');
    });

// Dashboard CMS.
Route::get('/dashboard', function () {
    return view('cms.dashboard');
})->name('cms.dashboard');

Route::controller(MataPelajaranController::class)->group(function () {
    Route::get('/mata-pelajaran', 'index')
        ->name('cms.mata-pelajaran');

    Route::get('/tambahmata-pelajaran', 'create')
        ->name('cms.mata-pelajaran.create');

    Route::post('/mata-pelajaran', 'store')
        ->name('cms.mata-pelajaran.store');

    Route::get('/mata-pelajaran/{mata_pelajaran}/edit', 'edit')
        ->name('cms.mata-pelajaran.edit');

    Route::put('/mata-pelajaran/{mata_pelajaran}', 'update')
        ->name('cms.mata-pelajaran.update');

    Route::delete('/mata-pelajaran/{mata_pelajaran}', 'destroy')
        ->name('cms.mata-pelajaran.destroy');
});

Route::controller(StudentController::class)->group(function () {
    Route::get('/students', 'index')
        ->name('cms.student');

    Route::post('/students', 'store')
        ->name('cms.student.store');

    Route::get('/tambahsiswa', 'tambahsiswa')
        ->name('cms.student.create');

    Route::get('/students/{siswa}/edit', 'edit')
        ->name('cms.student.edit');

    Route::put('/students/{siswa}', 'update')
        ->name('cms.student.update');

    Route::delete('/students/{siswa}', 'destroy')
        ->name('cms.student.destroy');
});

Route::controller(GuruController::class)->group(function () {
    Route::get('/teachers', 'index')
        ->name('cms.teachers');

    Route::post('/teachers', 'store')
        ->name('cms.teachers.store');

    Route::get('/tambahguru', 'tambahguru')
        ->name('cms.teachers.create');

    Route::get('/teachers/{guru}/edit', 'edit')
        ->name('cms.teachers.edit');

    Route::put('/teachers/{guru}', 'update')
        ->name('cms.teachers.update');

    Route::delete('/teachers/{guru}', 'destroy')
        ->name('cms.teachers.destroy');
});

Route::controller(KelasController::class)->group(function () {
    Route::get('/classes', 'index')
        ->name('cms.classes');

    Route::post('/classes', 'store')
        ->name('cms.classes.store');

    Route::get('/tambahkelas', 'tambahkelas')
        ->name('cms.classes.create');

    Route::get('/classes/{kelas}/edit', 'edit')
        ->name('cms.classes.edit');

    Route::put('/classes/{kelas}', 'update')
        ->name('cms.classes.update');

    Route::delete('/classes/{kelas}', 'destroy')
        ->name('cms.classes.destroy');
});

Route::controller(JadwalController::class)->group(function () {
    Route::get('/jadwal', 'index')
        ->name('cms.jadwal');

    // `/jadwal/tambah` ditulis sebelum route `{jadwal}` supaya kata "tambah"
    // tidak pernah tertangkap sebagai id jadwal.
    Route::get('/jadwal/tambah', 'create')
        ->name('cms.jadwal.create');

    Route::post('/jadwal', 'store')
        ->name('cms.jadwal.store');

    Route::get('/jadwal/{jadwal}/edit', 'edit')
        ->name('cms.jadwal.edit');

    Route::put('/jadwal/{jadwal}', 'update')
        ->name('cms.jadwal.update');

    Route::delete('/jadwal/{jadwal}', 'destroy')
        ->name('cms.jadwal.destroy');
});

Route::controller(UserController::class)->group(function () {
    Route::get('/users', 'index')
        ->name('cms.users');

    Route::post('/users', 'store')
        ->name('cms.users.store');

    Route::get('/tambahuser', 'tambahuser')
        ->name('cms.users.tambah');

    Route::get('/users/{user}/edit', 'edit')
        ->name('cms.users.edit');

    Route::put('/users/{user}', 'update')
        ->name('cms.users.update');

    Route::delete('/users/{user}', 'destroy')
        ->name('cms.users.destroy');
});

Route::controller(RekapController::class)->group(function () {
    Route::get('/rekap', 'index')
        ->name('cms.rekap');

    Route::get('/rekap/export/excel', 'exportExcel')
        ->name('cms.rekap.export.excel');

    Route::get('/rekap/export/pdf', 'exportPdf')
        ->name('cms.rekap.export.pdf');
});
