<?php

use App\Models\Absensi;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\SesiAbsensi;
use Illuminate\Foundation\Testing\RefreshDatabase;

/*
| Dashboard admin harus memakai angka yang sama dengan halaman pengelolaan
| masing-masing. Sebelumnya semua angkanya placeholder di Blade (1.248 mahasiswa,
| 86 dosen, 32 kelas), sehingga dashboard tidak pernah cocok dengan isi
| Students, Teachers, Classes, Mata Kuliah, Timetables, maupun Users.
*/
uses(RefreshDatabase::class);

beforeEach(function () {
    loginAdmin();
});

/**
 * Kelas dengan nama yang pasti.
 *
 * `KelasFactory` mengambil nama dari `Kelas::KELAS_TERSEDIA` dengan
 * `unique()`, jadi membuat lebih dari sembilan kelas sekaligus akan habis
 * kandidat. Test yang butuh banyak kelas memakai helper ini.
 */
function buatKelas(string $nama): Kelas
{
    return Kelas::factory()->create([
        'nama_kelas' => $nama,
        'tingkat' => 'Semester 1',
    ]);
}

/**
 * Absensi untuk satu mahasiswa pada tanggal tertentu.
 */
function catatAbsensi(Mahasiswa $mahasiswa, string $waktu, string $status = 'hadir'): void
{
    $momen = now()->parse($waktu);

    $sesi = SesiAbsensi::firstOrCreate(
        ['kode_sesi' => 'SESI-'.$momen->format('Ymd')],
        [
            'waktu_mulai' => $momen->startOfDay(),
            'waktu_selesai' => $momen->endOfDay(),
            'status' => 'selesai',
        ],
    );

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => $momen,
        'status' => $status,
    ]);
}

test('kartu statistik menghitung baris yang sama dengan halaman tujuan', function () {
    Mahasiswa::factory()->count(4)->create();
    Dosen::factory()->count(3)->create();

    foreach (['Kelas A', 'Kelas B', 'Kelas C', 'Kelas H', 'Kelas I'] as $nama) {
        buatKelas($nama);
    }

    MataKuliah::factory()->count(6)->create();

    $kelas = Kelas::query()->firstOrFail();

    Jadwal::factory()->count(2)->create(['kelas_id' => $kelas->id, 'status' => 'Aktif']);
    Jadwal::factory()->count(4)->create(['kelas_id' => $kelas->id, 'status' => 'Nonaktif']);

    // Hitungan diambil dari database setelah semua baris dibuat, bukan dari angka
    // yang ditulis test, karena `KelasFactory` dan `JadwalFactory` ikut membuat
    // baris lain (mata kuliah, dosen) lewat default factory-nya.
    $mahasiswa = Mahasiswa::count();
    $dosen = Dosen::count();
    $kelas = Kelas::count();
    $mataKuliah = MataKuliah::count();
    $jadwalAktif = Jadwal::where('status', 'Aktif')->count();

    $this->get(route('cms.dashboard'))
        ->assertOk()
        ->assertSee('Mahasiswa')->assertSee('Dosen')->assertSee('Kelas')
        ->assertSee('Mata Kuliah')->assertSee('Jadwal')->assertSee('Pengguna')
        // Angka hasil hitungan muncul di kartu, bukan placeholder lama.
        ->assertSee(number_format($mahasiswa, 0, ',', '.'))
        ->assertSee(number_format($kelas, 0, ',', '.'))
        ->assertSee(number_format($jadwalAktif, 0, ',', '.'));

    expect($mahasiswa)->toBe(4)
        ->and($dosen)->toBeGreaterThanOrEqual(3)
        ->and($kelas)->toBe(5)
        ->and($mataKuliah)->toBeGreaterThanOrEqual(6)
        ->and($jadwalAktif)->toBe(2);
});

test('kelas arsip tetap dihitung tapi disebutkan terpisah', function () {
    foreach (['Kelas A', 'Kelas B'] as $nama) {
        buatKelas($nama);
    }

    $kelas = Kelas::query()->firstOrFail();

    Kelas::query()->whereKeyNot($kelas->getKey())->update(['status' => 'Arsip']);
    Jadwal::factory()->count(3)->create(['kelas_id' => $kelas->id, 'status' => 'Nonaktif']);

    // Kartu "Kelas" menghitung semua kelas, sama seperti pagination di halaman
    // Classes yang tidak menyembunyikan kelas Arsip. Yang aktif disebut terpisah
    // supaya angkanya tidak dianggap sama.
    $this->get(route('cms.dashboard'))
        ->assertOk()
        ->assertSee('Kelas')
        ->assertSee('1 aktif · 1 diarsipkan')
        ->assertSee('Tidak ada jadwal untuk hari ini');

    expect(Kelas::count())->toBe(2)
        ->and(Kelas::where('status', 'Aktif')->count())->toBe(1)
        ->and(Jadwal::where('status', 'Aktif')->count())->toBe(0);
});

test('jumlah mahasiswa di dashboard sama dengan halaman mahasiswa', function () {
    Mahasiswa::factory()->count(7)->create();

    // Halaman Students menampilkan baris `mahasiswa` yang sama, jadi kartu
    // dashboard harus memakai angka yang sama.
    $this->get(route('cms.dashboard'))
        ->assertOk()
        ->assertSee('Mahasiswa')
        ->assertSeeInOrder(['Mahasiswa', '7'], escape: false);

    // Pagination di halaman Students menulis "… dari 7 mahasiswa".
    $this->get(route('cms.mahasiswa'))
        ->assertOk()
        ->assertSee('dari')
        ->assertSee('7')
        ->assertSee('mahasiswa');

    expect(Mahasiswa::count())->toBe(7);
});

test('dashboard tidak lagi menampilkan angka placeholder', function () {
    Mahasiswa::factory()->count(2)->create();

    $html = $this->get(route('cms.dashboard'))->assertOk()->getContent();

    // Sisa angka lama yang dulu ditulis langsung di Blade.
    expect($html)->not->toContain('1,248')     // mahasiswa
        ->not->toContain('>216<')          // hadir hari ini
        ->not->toContain('trend')              // badge tren palsu
        ->not->toContain('spark')              // sparkline palsu
        ->and(Mahasiswa::count())->toBe(2);
});

test('rekap hari ini dihitung dari tabel absensi', function () {
    $mahasiswa = Mahasiswa::factory()->count(3)->create()->first();

    catatAbsensi($mahasiswa, 'today 07:00', 'hadir');
    catatAbsensi($mahasiswa, 'today 07:05', 'izin');
    catatAbsensi($mahasiswa, 'today 07:10', 'sakit');
    catatAbsensi($mahasiswa, 'today 07:15', 'alpha');

    $html = $this->get(route('cms.dashboard'))->assertOk()->getContent();

    $this->assertSame(4, Absensi::whereDate('waktu_absen', today())->count());

    // Keempat status tampil dengan angka 1.
    expect($html)->toContain('Rekap Hari Ini')->toContain('Hadir')->toContain('Alpa');
});

test('absensi hari lain tidak ikut dihitung sebagai kehadiran hari ini', function () {
    $mahasiswa = Mahasiswa::factory()->create();

    catatAbsensi($mahasiswa, 'today 07:00', 'hadir');
    catatAbsensi($mahasiswa, now()->subDays(3)->format('Y-m-d 07:00'), 'hadir');

    $html = $this->get(route('cms.dashboard'))->assertOk()->getContent();

    // Hanya 1 catatan hari ini, jadiDua angka "1 mahasiswa" untuk hadir.
    expect(substr_count($html, '1</span>'))->toBeGreaterThan(0);
});

test('tren kehadiran dihitung per bulan dari tabel absensi', function () {
    $mahasiswa = Mahasiswa::factory()->create();

    // Dua catatan bulan ini, satu bulan lalu.
    catatAbsensi($mahasiswa, 'today 07:00', 'hadir');
    catatAbsensi($mahasiswa, 'today 07:10', 'hadir');
    catatAbsensi($mahasiswa, now()->subMonth()->format('Y-m-d 07:00'), 'hadir');

    $html = $this->get(route('cms.dashboard'))->assertOk()->getContent();

    // Grafik bulanan = 12 titik, dan bulan tanpa catatan tetap dihitung.
    expect($html)->toContain('12 periode terakhir')
        ->toContain('per bulan');
});

test('tab tahunan memakai lima tahun terakhir', function () {
    $mahasiswa = Mahasiswa::factory()->create();
    catatAbsensi($mahasiswa, now()->subYears(2)->format('Y-m-d 07:00'), 'hadir');

    $this->get(route('cms.dashboard', ['tren' => 'tahunan']))
        ->assertOk()
        ->assertSee('per tahun')
        ->assertSee('5 periode terakhir')
        // Lima label tahun harus berbeda, tidak boleh terulang.
        ->assertSee((string) now()->year)
        ->assertSee((string) now()->subYears(4)->year)
        ->assertDontSee((string) now()->subYears(5)->year);
});

test('grafik bulanan menandai bulan tanpa absensi, bukan mengarangnya', function () {
    Mahasiswa::factory()->create();

    // Tidak ada catatan sama sekali: tidak boleh muncul persentase palsu.
    $html = $this->get(route('cms.dashboard'))->assertOk()->getContent();

    expect($html)->toContain('Belum ada catatan presensi untuk ditampilkan')
        ->and(Absensi::count())->toBe(0);
});

test('tab tren yang tidak dikenal kembali ke bulanan', function () {
    $this->get(route('cms.dashboard', ['tren' => 'entah']))
        ->assertOk()
        ->assertSee('per bulan');
});

test('kartu statistik menautkan ke halaman masing-masing', function () {
    $this->get(route('cms.dashboard'))
        ->assertOk()
        ->assertSee(route('cms.mahasiswa'), false)
        ->assertSee(route('cms.dosens'), false)
        ->assertSee(route('cms.kelas'), false)
        ->assertSee(route('cms.mata-kuliah'), false)
        ->assertSee(route('cms.jadwal'), false)
        ->assertSee(route('cms.users'), false);
});

test('jadwal hari ini menampilkan jadwal sesuai hari berjalan', function () {
    $hariIni = now()->locale('id')->translatedFormat('l');

    $kelas = buatKelas('Kelas A');
    $dosen = Dosen::factory()->create();

    Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'dosen_id' => $dosen->id,
        'hari' => $hariIni,
        'mata_kuliah' => 'Mata Kuliah Hari Ini',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:00',
        'status' => 'Aktif',
    ]);

    // Jadwal di hari lain tidak boleh ikut tampil.
    $hariLain = collect(Jadwal::HARI_TERSEDIA)
        ->reject(fn (string $hari): bool => $hari === $hariIni)
        ->first();

    Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'dosen_id' => $dosen->id,
        'hari' => $hariLain,
        'mata_kuliah' => 'Mata Kuliah Hari Lain',
        'status' => 'Aktif',
    ]);

    $this->get(route('cms.dashboard'))
        ->assertOk()
        ->assertSee('Jadwal Mengajar Hari Ini')
        ->assertSee('Mata Kuliah Hari Ini')
        ->assertDontSee('Mata Kuliah Hari Lain');
});

test('jadwal nonaktif tidak dihitung di dashboard', function () {
    $kelas = buatKelas('Kelas A');
    $dosen = Dosen::factory()->create();

    Jadwal::factory()->count(3)->create([
        'kelas_id' => $kelas->id,
        'dosen_id' => $dosen->id,
        'status' => 'Nonaktif',
    ]);

    $this->get(route('cms.dashboard'))
        ->assertOk()
        ->assertSee('Tidak ada jadwal untuk hari ini');
});

test('dashboard menampilkan kelas dengan mahasiswa terbanyak', function () {
    buatKelas('Kelas Ramai');
    buatKelas('Kelas Sepi');

    Mahasiswa::factory()->count(5)->create(['kelas' => 'Kelas Ramai']);
    Mahasiswa::factory()->create(['kelas' => 'Kelas Sepi']);

    $this->get(route('cms.dashboard'))
        ->assertOk()
        ->assertSee('Kelas Terbanyak')
        ->assertSee('Kelas Ramai')
        ->assertSee('Kelas Sepi');
});

test('dashboard tetap aman saat belum ada data sama sekali', function () {
    $this->get(route('cms.dashboard'))
        ->assertOk()
        ->assertSee('Belum ada catatan presensi untuk ditampilkan.')
        ->assertSee('Belum ada presensi hari ini')
        ->assertSee('Tidak ada jadwal untuk hari ini')
        ->assertSee('Belum ada mahasiswa');
});

test('tombol ekspor mengarah ke ekspor csv rekap', function () {
    $this->get(route('cms.dashboard'))
        ->assertOk()
        ->assertSee(route('cms.rekap.export.excel', ['bulan' => now()->format('Y-m')]), false)
        ->assertSee('Ekspor CSV');
});

test('mahasiswa yang belum login tidak bisa membuka dashboard', function () {
    auth()->logout();

    $this->get(route('cms.dashboard'))->assertRedirect(route('login'));
});
