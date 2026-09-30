<?php

use App\Http\Controllers\GuruController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
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

Route::get('/identitas-siswa', function () {
    return view('identitas-siswa');
})->name('identitas.siswa');

Route::get('/absen/siswa/qr', function () {
    return view('absen-siswa-qr');
})->name('absen.siswa.qr');

Route::get('/absen/verifikasi', function () {
    return view('absen-siswa-verifikasi');
})->name('absen.verifikasi');


// Bagian Baru
Route::prefix('dashboard/guru')
    ->name('guru.')
    ->group(function () {
        Route::get('/', function () {
            return view('guru.dashboard');
        })->name('dashboard');

        Route::get('/progres', function () {
            return view('guru.progres');
        })->name('progres');

        Route::get('/kelola', function () {
            return view('guru.kelola');
        })->name('kelola');

        Route::get('/laporan', function () {
            return view('guru.laporan');
        })->name('laporan');

        Route::get('/realtime', function () {
            return view('guru.realtime');
        })->name('realtime');
    });

Route::get('/dashboard', function () {
    return view('cms.dashboard');
})->name('cms.dashboard');

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
