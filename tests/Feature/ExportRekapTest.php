<?php

use App\Models\Absensi;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00'));

    // Halaman rekap berada di area CMS yang hanya bisa dibuka admin.
    loginAdmin();
});

afterEach(function () {
    Carbon::setTestNow();
});

/**
 * Buat siswa berikut satu sesi absensi dan catat status kehadiran per tanggal.
 *
 * @param  array<string, array{status: string, tanggal: string}>  $catatan  Kunci tanggal, nilai berisi status absensi.
 */
function rekapSiswa(string $kelas, array $catatan): Siswa
{
    $siswa = Siswa::factory()->create(['kelas' => $kelas]);

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-'.$siswa->id,
        'waktu_mulai' => '2026-09-01 06:30:00',
        'waktu_selesai' => '2026-09-01 08:00:00',
        'status' => 'selesai',
    ]);

    foreach ($catatan as $tanggal => $baris) {
        Absensi::create([
            'siswa_id' => $siswa->id,
            'sesi_absensi_id' => $sesi->id,
            'waktu_absen' => $tanggal.' 07:00:00',
            'status' => $baris['status'],
        ]);
    }

    return $siswa;
}

test('halaman rekap menampilkan agregasi per kelas beserta link export', function () {
    rekapSiswa('X-A', [
        '2026-09-01' => ['status' => 'hadir'],
        '2026-09-02' => ['status' => 'hadir'],
        '2026-09-03' => ['status' => 'hadir'],
        '2026-09-04' => ['status' => 'izin'],
    ]);
    rekapSiswa('XII-A', [
        '2026-09-01' => ['status' => 'hadir'],
        '2026-09-02' => ['status' => 'sakit'],
        '2026-09-03' => ['status' => 'alpha'],
    ]);

    $this->get(route('cms.rekap', ['bulan' => '2026-09']))
        ->assertOk()
        ->assertSee('Rekap Kehadiran')
        ->assertSee('X-A')
        ->assertSee('XII-A')
        ->assertSee('75%')
        ->assertSee('33%')
        ->assertSee('57%')
        ->assertSee(route('cms.rekap.export.excel', ['bulan' => '2026-09']), false)
        ->assertSee(route('cms.rekap.export.pdf', ['bulan' => '2026-09']), false);
});

test('export excel mengirim csv rekap yang bisa dibuka di excel', function () {
    rekapSiswa('X-A', [
        '2026-09-01' => ['status' => 'hadir'],
        '2026-09-02' => ['status' => 'sakit'],
    ]);

    $response = $this->get(route('cms.rekap.export.excel', ['bulan' => '2026-09']));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('content-disposition'))
        ->toContain('attachment')
        ->toContain('rekap-kehadiran-2026-09');

    $csv = $response->streamedContent();

    // BOM UTF-8 supaya huruf beraccent tidak berantakan di Excel.
    expect($csv)->toStartWith("\xEF\xBB\xBF");

    expect($csv)
        ->toContain('REKAP KEHADIRAN SISWA')
        ->toContain('September 2026')
        ->toContain('Kelas;"Jumlah Siswa";Hadir;Izin;Sakit;Alpa;"Total Catatan";"Persentase Kehadiran (%)"')
        ->toContain('X-A;1;1;0;1;0;2;50')
        ->toContain('TOTAL;1;1;0;1;0;2;50');
});

test('export excel memfilter berdasarkan kelas', function () {
    rekapSiswa('X-A', ['2026-09-01' => ['status' => 'hadir']]);
    rekapSiswa('XII-A', ['2026-09-01' => ['status' => 'alpha']]);

    $csv = $this->get(route('cms.rekap.export.excel', ['bulan' => '2026-09', 'kelas' => 'X-A']))->streamedContent();

    expect($csv)->toContain('X-A;1;1')->not->toContain('XII-A');
});

test('export excel hanya menghitung absensi pada periode terpilih', function () {
    rekapSiswa('X-A', [
        '2026-08-15' => ['status' => 'hadir'],
        '2026-09-01' => ['status' => 'hadir'],
        '2026-10-02' => ['status' => 'alpha'],
    ]);

    $csv = $this->get(route('cms.rekap.export.excel', ['bulan' => '2026-09']))->streamedContent();

    expect($csv)->toContain('X-A;1;1;0;0;0;1;100');
});

test('rentang tanggal dipakai ketika bulan tidak dipilih', function () {
    rekapSiswa('X-A', [
        '2026-09-01' => ['status' => 'hadir'],
        '2026-09-02' => ['status' => 'hadir'],
        '2026-09-03' => ['status' => 'alpha'],
    ]);

    $csv = $this->get(route('cms.rekap.export.excel', [
        'dari' => '2026-09-02',
        'sampai' => '2026-09-03',
    ]))->streamedContent();

    expect($csv)
        ->toContain('2 – 3 September 2026')
        ->toContain('X-A;1;1;0;0;1;2;50');
});

test('rentang satu hari dipakai utuh sampai akhir hari', function () {
    rekapSiswa('X-A', [
        '2026-09-01' => ['status' => 'hadir'],
        '2026-09-02' => ['status' => 'alpha'],
    ]);

    $csv = $this->get(route('cms.rekap.export.excel', [
        'dari' => '2026-09-01',
        'sampai' => '2026-09-01',
    ]))->streamedContent();

    expect($csv)
        ->toContain('Periode;"1 September 2026"')
        ->toContain('X-A;1;1;0;0;0;1;100');
});

test('rentang tanggal membatalkan pilihan bulan ketika dipersempit manual', function () {
    rekapSiswa('X-A', [
        '2026-09-01' => ['status' => 'hadir'],
        '2026-09-15' => ['status' => 'alpha'],
    ]);

    $csv = $this->get(route('cms.rekap.export.excel', [
        'bulan' => '2026-09',
        'dari' => '2026-09-01',
        'sampai' => '2026-09-01',
    ]))->streamedContent();

    expect($csv)->toContain('X-A;1;1;0;0;0;1;100');
});

test('rentang tanggal terbalik dinormalisasi', function () {
    rekapSiswa('X-A', [
        '2026-09-01' => ['status' => 'hadir'],
        '2026-09-05' => ['status' => 'izin'],
    ]);

    $csv = $this->get(route('cms.rekap.export.excel', [
        'dari' => '2026-09-05',
        'sampai' => '2026-09-01',
    ]))->streamedContent();

    expect($csv)->toContain('X-A;1;1;1;0;0;2;50');
});

test('filter tidak valid tidak merusak halaman maupun export', function () {
    rekapSiswa('X-A', ['2026-09-01' => ['status' => 'hadir']]);

    $this->get(route('cms.rekap', ['bulan' => 'bukan-bulan', 'dari' => 'salah', 'sampai' => '']))
        ->assertOk()
        ->assertSee('X-A');

    $this->get(route('cms.rekap.export.excel', ['bulan' => '2026-13']))
        ->assertOk();

    $this->get(route('cms.rekap.export.pdf', ['dari' => 'salah']))
        ->assertOk()
        ->assertSee('Rekap Kehadiran per Kelas');
});

test('export pdf menampilkan halaman cetak siap simpan', function () {
    rekapSiswa('X-A', [
        '2026-09-01' => ['status' => 'hadir'],
        '2026-09-02' => ['status' => 'izin'],
    ]);

    $this->get(route('cms.rekap.export.pdf', ['bulan' => '2026-09', 'kelas' => 'X-A']))
        ->assertOk()
        ->assertSee('Rekap Kehadiran per Kelas')
        ->assertSee('September 2026')
        ->assertSee('X-A')
        ->assertSee('50%')
        ->assertSee('TOTAL (1 kelas)')
        ->assertSee('window.print()', false);
});

test('rekap menampilkan baris kosong bila tidak ada data', function () {
    $this->get(route('cms.rekap'))
        ->assertOk()
        ->assertSee('Belum ada data rekap.')
        ->assertDontSee('Total Catatan');
});
