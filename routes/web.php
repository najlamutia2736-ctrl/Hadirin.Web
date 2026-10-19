<?php

use App\Http\Controllers\AbsensiMahasiswaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\DashboardDosenController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
 * Halaman depan aplikasi. Nama routenya tetap `beranda` supaya seluruh
 * `route('beranda')` yang sudah dipakai di navbar, halaman absensi, dan
 * dashboard otomatis menunjuk ke `/`.
 *
 * Halaman ini berlaku untuk pengunjung umum maupun pengguna yang sudah masuk;
 * navbar dan tombol hero yang menyesuaikan diri mengikuti sesi masing-masing.
 * Halaman ini tidak pernah mengakhiri sesi.
 */
Route::get('/', function () {
    return view('beranda');
})->name('beranda');

/*
 * `/beranda` lama sekarang jadi alamat lain dari halaman depan yang sama, jadi
 * hanya mengalihkan. Bookmark dan tautan lama yang masih tersebar tidak ikut
 * rusak.
 */
Route::get('/beranda', fn () => redirect()->route('beranda'))->name('home');

/*
 * `guest` dipakai juga di halaman login (bukan hanya saat submit), supaya
 * orang yang sesinya masih hidup langsung diarahkan ke berandanya. Tanpa itu,
 * navbar di halaman login menampilkan nama akun dan tombol Logout padahal
 * halaman itu bukan untuk pengguna yang sudah masuk.
 */
Route::get('/login', function () {
    return view('login');
})->middleware('guest')->name('login');

Route::post('/login', [AuthController::class, 'store'])
    ->middleware('guest')
    ->name('login.store');

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Halaman Absensi Mahasiswa.
Route::prefix('/absensi')
    ->name('absensi.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/index', [AbsensiMahasiswaController::class, 'index'])
            ->name('index');

        Route::get('/identitas', [AbsensiMahasiswaController::class, 'identitas'])
            ->name('identitas');

        Route::post('/identitas', [AbsensiMahasiswaController::class, 'updateIdentitas'])
            ->name('identitas.update');

        Route::get('/metode', [AbsensiMahasiswaController::class, 'metode'])
            ->name('metode');

        Route::get('/scan-qr', function () {
            return view('absensi.scan-qr');
        })->name('scan-qr');

        Route::get('/id-unik', [AbsensiMahasiswaController::class, 'idUnik'])
            ->name('id-unik');

        Route::post('/id-unik', [AbsensiMahasiswaController::class, 'storeIdUnik'])
            ->name('id-unik.store');

        Route::get('/izin-sakit', [AbsensiMahasiswaController::class, 'izinSakit'])
            ->name('izin-sakit');

        Route::post('/izin-sakit', [AbsensiMahasiswaController::class, 'storeIzinSakit'])
            ->name('izin-sakit.store');

        Route::get('/notifikasi', [AbsensiMahasiswaController::class, 'notifikasi'])
            ->name('notifikasi');
    });

// Dashboard Dosen. Wajib login dan hanya untuk akun dosen.
Route::prefix('dashboard/dosen')
    ->name('dosen.')
    ->controller(DashboardDosenController::class)
    ->middleware(['auth', 'role:Dosen'])
    ->group(function () {
        Route::get('/', 'dashboard')
            ->name('dashboard');

        Route::get('/kelola', 'kelola')
            ->name('kelola');

        Route::post('/kelola/mahasiswa', 'storeMahasiswa')
            ->name('kelola.mahasiswa.store');

        Route::put('/kelola/mahasiswa/{mahasiswa}', 'updateMahasiswa')
            ->name('kelola.mahasiswa.update');

        Route::delete('/kelola/mahasiswa/{mahasiswa}', 'destroyMahasiswa')
            ->name('kelola.mahasiswa.destroy');

        Route::get('/laporan', 'laporan')
            ->name('laporan');

        Route::get('/laporan/export', 'exportLaporan')
            ->name('laporan.export');
    });

/*
 * `/dashboard/dosen/realtime` dulu jadi halaman tersendiri untuk memantau
 * absensi yang masuk. Isinya sekarang bagian dari dashboard dosen, jadi alamat
 * lama hanya mengalihkan supaya bookmark dan tautan yang sudah tersebar tidak
 * ikut rusak. Ditulis di luar group di atas karena group itu mengikat
 * controller, sedangkan rute ini memang tidak memanggil method apa pun.
 */
Route::get('/dashboard/dosen/realtime', fn () => redirect()->route('dosen.dashboard'))
    ->middleware(['auth', 'role:Dosen'])
    ->name('dosen.realtime.lama');

// Dashboard CMS dan seluruh halaman lainnya. Wajib login dan hanya untuk akun
// admin. Operator diperlakukan sama karena perannya juga mengelola data kampus.
Route::middleware(['auth', 'role:Admin,Operator'])->group(function () {
    Route::controller(DashboardAdminController::class)->group(function () {
        Route::get('/dashboard-admin', 'index')
            ->name('cms.dashboard');
    });

    Route::controller(MataKuliahController::class)->group(function () {
        Route::get('/mata-kuliah', 'index')
            ->name('cms.mata-kuliah');

        Route::get('/tambahmatakuliah', 'create')
            ->name('cms.mata-kuliah.create');

        Route::post('/mata-kuliah', 'store')
            ->name('cms.mata-kuliah.store');

        Route::get('/mata-kuliah/{mata_kuliah}/edit', 'edit')
            ->name('cms.mata-kuliah.edit');

        Route::put('/mata-kuliah/{mata_kuliah}', 'update')
            ->name('cms.mata-kuliah.update');

        Route::delete('/mata-kuliah/{mata_kuliah}', 'destroy')
            ->name('cms.mata-kuliah.destroy');
    });

    Route::controller(MahasiswaController::class)->group(function () {
        Route::get('/mahasiswa', 'index')
            ->name('cms.mahasiswa');

        Route::post('/mahasiswa', 'store')
            ->name('cms.mahasiswa.store');

        Route::get('/tambahmahasiswa', 'tambahmahasiswa')
            ->name('cms.mahasiswa.create');

        Route::get('/mahasiswa/{mahasiswa}/edit', 'edit')
            ->name('cms.mahasiswa.edit');

        Route::put('/mahasiswa/{mahasiswa}', 'update')
            ->name('cms.mahasiswa.update');

        Route::delete('/mahasiswa/{mahasiswa}', 'destroy')
            ->name('cms.mahasiswa.destroy');
    });

    Route::controller(DosenController::class)->group(function () {
        Route::get('/dosens', 'index')
            ->name('cms.dosens');

        Route::post('/dosens', 'store')
            ->name('cms.dosens.store');

        Route::get('/tambahdosen', 'tambahdosen')
            ->name('cms.dosens.create');

        Route::get('/dosens/{dosen}/edit', 'edit')
            ->name('cms.dosens.edit');

        Route::put('/dosens/{dosen}', 'update')
            ->name('cms.dosens.update');

        Route::delete('/dosens/{dosen}', 'destroy')
            ->name('cms.dosens.destroy');
    });

    Route::controller(KelasController::class)->group(function () {
        Route::get('/kelas', 'index')
            ->name('cms.kelas');

        Route::post('/kelas', 'store')
            ->name('cms.kelas.store');

        Route::get('/tambahkelas', 'tambahkelas')
            ->name('cms.kelas.create');

        Route::get('/kelas/{kelas}/edit', 'edit')
            ->name('cms.kelas.edit');

        Route::put('/kelas/{kelas}', 'update')
            ->name('cms.kelas.update');

        Route::delete('/kelas/{kelas}', 'destroy')
            ->name('cms.kelas.destroy');
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
});
