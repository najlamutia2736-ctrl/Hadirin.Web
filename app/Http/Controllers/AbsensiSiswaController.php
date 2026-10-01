<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbsensiSiswaController extends Controller
{
    /**
     * Halaman awal absensi siswa.
     *
     * Berisi dua hal: status kehadiran hari ini, lalu menu untuk memilih cara
     * absen (scan QR, ID unik, atau izin/sakit). Semua angka diambil dari tabel
     * `absensis` yang sama dengan dashboard guru dan rekap admin, jadi absensi
     * yang tercatat di sini langsung muncul di kedua sisi tanpa langkah tambahan.
     */
    public function index(Request $request): View
    {
        $siswa = $request->user()?->siswa;

        // Cuma akun siswa yang punya halaman ini. Guru dan admin yang salah
        // klik link dari navbar diarahkan keluar, bukan melihat data kosong.
        abort_unless($siswa !== null, 403, 'Halaman ini hanya untuk akun siswa.');

        return view('absensi.index', [
            'siswa' => $siswa,
            'hariIni' => $this->absensiHariIni($siswa),
            'rekap' => $this->rekapBulanIni($siswa),
        ]);
    }

    /**
     * Catatan absensi siswa hari ini, atau null kalau belum absen.
     *
     * Absensi diambil paling akhir supaya kalau ada dua catatan di hari yang
     * sama, yang tampil adalah yang paling baru.
     */
    protected function absensiHariIni(Siswa $siswa): ?Absensi
    {
        return $siswa->absensis()
            ->with('sesiAbsensi:id,kode_sesi,waktu_mulai')
            ->whereDate('waktu_absen', today())
            ->latest('waktu_absen')
            ->first();
    }

    /**
     * Rekap kehadiran bulan berjalan untuk siswa ini.
     *
     * Dihitung dengan satu query agregat, bukan mengambil seluruh baris
     * absensi ke memori lalu dihitung di PHP.
     *
     * @return array{hadir:int,izin:int,sakit:int,alpha:int,total:int,persentase:int}
     */
    protected function rekapBulanIni(Siswa $siswa): array
    {
        $dari = now()->startOfMonth();
        $sampai = $dari->copy()->endOfMonth()->endOfDay();

        $jumlah = $siswa->absensis()
            ->whereBetween('waktu_absen', [$dari, $sampai])
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $hadir = (int) ($jumlah['hadir'] ?? 0);
        $izin = (int) ($jumlah['izin'] ?? 0);
        $sakit = (int) ($jumlah['sakit'] ?? 0);
        $alpha = (int) ($jumlah['alpha'] ?? 0);
        $total = $hadir + $izin + $sakit + $alpha;

        return [
            'hadir' => $hadir,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpha' => $alpha,
            'total' => $total,
            'persentase' => $total > 0 ? (int) round($hadir / $total * 100) : 0,
        ];
    }
}
