<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Dashboard admin.
 *
 * Semua angka di halaman ini diambil dari tabel yang sama dengan halaman
 * pengelolaan masing-masing: kartu statistik menghitung baris yang benar
 * dari `siswas`, `gurus`, `kelas`, `mata_pelajaran`, `jadwals`, dan `users`,
 * sedangkan rekap kehadiran memakai `absensis` yang juga dipakai halaman Rekap
 * dan dashboard guru.
 *
 * Angka di sini sengaja tidak dihitung ulang dengan rumus lain, supaya kartu
 * dashboard dan isi halaman tujuan selalu cocok. Kalau ada perbedaan, itu bug,
 * bukan beda definisi.
 */
class DashboardAdminController extends Controller
{
    /**
     * Nilai enum `absensis.status` ke kolom rekap.
     *
     * Di database status alpa disimpan sebagai `alpha`.
     *
     * @var array<string, string>
     */
    private const PETA_STATUS = [
        'hadir' => 'hadir',
        'izin' => 'izin',
        'sakit' => 'sakit',
        'alpha' => 'alpa',
    ];

    /**
     * Label status absensi dalam bahasa Indonesia.
     *
     * Di database alpa tersimpan sebagai `alpha`, sedangkan halaman Rekap dan
     * dashboard guru menampilkannya sebagai "Alpa".
     *
     * @var array<string, string>
     */
    private const LABEL_STATUS = [
        'hadir' => 'Hadir',
        'izin' => 'Izin',
        'sakit' => 'Sakit',
        'alpa' => 'Alpa',
    ];

    /**
     * Warna tiap status, dipakai titik legenda pada donut rekap harian.
     *
     * Nilainya sama dengan warna yang dipakai halaman Rekap dan dashboard guru.
     *
     * @var array<string, array<string, string>>
     */
    private const WARNA_STATUS = [
        'hadir' => ['dot' => 'bg-green-500', 'hex' => '#22c55e'],
        'izin' => ['dot' => 'bg-blue-500', 'hex' => '#3b82f6'],
        'sakit' => ['dot' => 'bg-amber-500', 'hex' => '#f59e0b'],
        'alpa' => ['dot' => 'bg-red-500', 'hex' => '#ef4444'],
    ];

    /**
     * Berapa banyak bulan ke belakang untuk grafik bulanan.
     */
    private const BULAN_TREN = 12;

    /**
     * Berapa banyak tahun ke belakang untuk grafik tahunan.
     */
    private const TAHUN_TREN = 5;

    /**
     * Berapa banyak jadwal ditampilkan di daftar "Mengajar Hari Ini".
     */
    private const BATAS_JADWAL_HARI_INI = 5;

    public function index(Request $request): View
    {
        $tren = $this->tabTrenValid($request->query('tren'));

        // Jadwal hari ini diambil sekali lalu dipakai di dua tempat: kartu
        // "Jadwal" dan daftar jadwal di bawah halaman.
        $jadwalHariIni = $this->jadwalHariIni();

        return view('cms.dashboard', [
            'pageTitle' => 'Dashboard',
            'stats' => $this->kartuStatistik($jadwalHariIni->count()),
            'rekapHariIni' => $this->rekapHariIni(),
            'tren' => $tren,
            'trenBulanan' => $this->trenKehadiran('bulanan', $tren === 'bulanan'),
            'trenTahunan' => $this->trenKehadiran('tahunan', $tren === 'tahunan'),
            'jadwalHariIni' => $jadwalHariIni,
            'kelasTeramai' => $this->kelasTeramai(),
        ]);
    }

    /**
     * Kartu statistik: satu kartu untuk tiap halaman di menu Manajemen.
     *
     * Masing-masing menyimpan `route` supaya kartunya jadi pintu masuk ke
     * halaman yang mengelola datanya, bukan angka yang berdiri sendiri.
     *
     * @param  int  $jadwalHariIni  jumlah slot jadwal untuk hari ini
     * @return list<array<string, mixed>>
     */
    private function kartuStatistik(int $jadwalHariIni): array
    {
        return [
            [
                'label' => 'Siswa',
                'value' => Siswa::query()->count(),
                'icon' => 'fas fa-user-graduate',
                'tone' => 'blue',
                'route' => 'cms.student',
                'detail' => $this->ringkasanSiswa(),
            ],
            [
                'label' => 'Guru',
                'value' => Guru::query()->count(),
                'icon' => 'fas fa-chalkboard-teacher',
                'tone' => 'green',
                'route' => 'cms.teachers',
                'detail' => Guru::query()->where('status', 'Aktif')->count().' aktif',
            ],
            [
                'label' => 'Kelas',
                // Menghitung semua kelas, sama seperti pagination di halaman
                // Classes yang tidak menyembunyikan kelas berstatus Arsip.
                // Jumlah aktifnya ditulis di detail, bukan dipisah ke kartu lain.
                'value' => Kelas::query()->count(),
                'icon' => 'fas fa-book-open',
                'tone' => 'purple',
                'route' => 'cms.classes',
                'detail' => $this->ringkasanKelas(),
            ],
            [
                'label' => 'Mata Pelajaran',
                'value' => MataPelajaran::query()->count(),
                'icon' => 'fas fa-layer-group',
                'tone' => 'violet',
                'route' => 'cms.mata-pelajaran',
                'detail' => null,
            ],
            [
                'label' => 'Jadwal',
                'value' => Jadwal::query()->where('status', 'Aktif')->count(),
                'icon' => 'fas fa-calendar-days',
                'tone' => 'rose',
                'route' => 'cms.jadwal',
                'detail' => $jadwalHariIni.' slot hari ini',
            ],
            [
                'label' => 'Pengguna',
                'value' => User::query()->count(),
                'icon' => 'fas fa-users-cog',
                'tone' => 'amber',
                'route' => 'cms.users',
                'detail' => $this->ringkasanPengguna(),
            ],
        ];
    }

    /**
     * Ringkasan siswa untuk detail kartu: jumlah laki-laki dan perempuan.
     */
    private function ringkasanSiswa(): string
    {
        $baris = Siswa::query()
            ->selectRaw("count(case when jenis_kelamin = 'L' then 1 end) as laki")
            ->selectRaw("count(case when jenis_kelamin = 'P' then 1 end) as perempuan")
            ->first();

        return sprintf('%s laki-laki · %s perempuan', number_format((int) $baris->laki, 0, ',', '.'), number_format((int) $baris->perempuan, 0, ',', '.'));
    }

    /**
     * Ringkasan kelas: berapa yang aktif dan berapa yang diarsipkan.
     */
    private function ringkasanKelas(): string
    {
        $aktif = Kelas::query()->where('status', 'Aktif')->count();
        $arsip = Kelas::query()->where('status', '!=', 'Aktif')->count();

        return $aktif.' aktif'
            .($arsip > 0 ? ' · '.$arsip.' diarsipkan' : '');
    }

    /**
     * Ringkasan pengguna per peran, mengikuti nilai `users.role`.
     */
    private function ringkasanPengguna(): string
    {
        $perRole = User::query()
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->orderBy('role')
            ->pluck('total', 'role');

        return $perRole
            ->map(fn ($jumlah, $role): string => $jumlah.' '.$role)
            ->implode(' · ');
    }

    /**
     * Rekap kehadiran hari ini, dihitung dari tabel `absensis`.
     *
     * Siswa yang belum absen hari ini sengaja tidak dihitung sebagai alpa,
     * supaya angka di sini sama dengan dashboard guru: yang dihitung adalah
     * catatan absensi yang benar-benar ada.
     *
     * @return array<string, mixed>
     */
    private function rekapHariIni(): array
    {
        $jumlah = Absensi::query()
            ->whereDate('waktu_absen', today())
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $baris = [];

        foreach (self::PETA_STATUS as $status => $kolom) {
            $baris[$kolom] = [
                'label' => self::LABEL_STATUS[$kolom],
                'value' => (int) ($jumlah[$status] ?? 0),
                'dot' => self::WARNA_STATUS[$kolom]['dot'],
                'hex' => self::WARNA_STATUS[$kolom]['hex'],
            ];
        }

        $total = array_sum(array_column($baris, 'value'));
        $hadir = $baris['hadir']['value'];

        return [
            'baris' => $baris,
            'total' => $total,
            'persentase' => $total > 0 ? (int) round($hadir / $total * 100) : 0,
            'tanggal' => now()->locale('id')->translatedFormat('l, d F Y'),
            'siswaTerpantau' => Siswa::query()->count(),
        ];
    }

    /**
     * Deret tren kehadiran sesuai tab yang dipilih.
     *
     * Hanya tab aktif yang benar-benar dihitung supaya dashboard tidak
     * menjalankan dua agregasi untuk data yang tampilnya cuma satu.
     *
     * @return list<array<string, int|string>>
     */
    private function trenKehadiran(string $mode, bool $aktif): array
    {
        if (! $aktif) {
            return [];
        }

        // Tahun memakai penambahan tahun, bukan penambahan bulan. Menambah 4 bulan
        // berulang dari "startOfYear" akan menyeberang ke tahun sebelumnya dan
        // membuat label tahunan terulang.
        $tahunan = $mode === 'tahunan';

        $deret = $this->agregatAbsensi(
            $tahunan ? now()->startOfYear()->subYears(self::TAHUN_TREN - 1) : now()->startOfMonth()->subMonths(self::BULAN_TREN - 1),
            $tahunan ? 4 : 7,
        );

        $mulai = $tahunan ? now()->startOfYear()->subYears(self::TAHUN_TREN - 1) : now()->startOfMonth()->subMonths(self::BULAN_TREN - 1);
        $jumlah = $tahunan ? self::TAHUN_TREN : self::BULAN_TREN;

        $label = $tahunan
            ? static fn (Carbon $awal): string => (string) $awal->year
            : static fn (Carbon $awal): string => $awal->locale('id')->translatedFormat('M');

        $kunci = $tahunan
            ? static fn (Carbon $awal): string => $awal->format('Y')
            : static fn (Carbon $awal): string => $awal->format('Y-m');

        $tren = [];

        // Seluruh periode dilewati, bukan hanya yang ada catatannya, supaya
        // batang grafik tidak "lompat" dan bulan tanpa absensi tetap terlihat
        // sebagai batang kosong, bukan dianggap hilang.
        for ($i = 0; $i < $jumlah; $i++) {
            $awal = $mulai->copy()->{$tahunan ? 'addYears' : 'addMonthsNoOverflow'}($i);
            $baris = $deret[$kunci($awal)] ?? null;

            $tren[] = [
                'label' => $label($awal),
                'hadir' => $baris['hadir'] ?? 0,
                'izin' => $baris['izin'] ?? 0,
                'sakit' => $baris['sakit'] ?? 0,
                'alpa' => $baris['alpa'] ?? 0,
                'total' => $baris['total'] ?? 0,
                'persentase' => ($baris['total'] ?? 0) > 0
                    ? (int) round($baris['hadir'] / $baris['total'] * 100)
                    : 0,
            ];
        }

        return $tren;
    }

    /**
     * Agregasi absensi per bulan atau per tahun dalam satu query.
     *
     * Pengelompokan memakai `substr` atas kolom `waktu_absen`, bukan
     * `DATE_FORMAT` (MySQL) atau `strftime` (SQLite). Kolomnya disimpan sebagai
     * teks `YYYY-MM-DD HH:MM:SS`, jadi potongan 7 karakter pertama selalu
     * menghasilkan bulan dan 4 karakter pertama selalu menghasilkan tahun,
     * apa pun driver database yang dipakai.
     *
     * @return array<string, array<string, int>>
     */
    private function agregatAbsensi(Carbon $dari, int $panjangKunci): array
    {
        return Absensi::query()
            ->where('waktu_absen', '>=', $dari)
            ->selectRaw('substr(waktu_absen, 1, ?) as periode', [$panjangKunci])
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as hadir', ['hadir'])
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as izin', ['izin'])
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as sakit', ['sakit'])
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as alpa', ['alpha'])
            ->groupBy('periode')
            ->get()
            ->mapWithKeys(fn ($baris): array => [
                (string) $baris->periode => [
                    'hadir' => (int) $baris->hadir,
                    'izin' => (int) $baris->izin,
                    'sakit' => (int) $baris->sakit,
                    'alpa' => (int) $baris->alpa,
                    'total' => (int) $baris->total,
                ],
            ])
            ->all();
    }

    /**
     * Tab tren yang sedang dibuka, default ke bulanan.
     */
    private function tabTrenValid(mixed $nilai): string
    {
        $nilai = is_string($nilai) ? trim($nilai) : '';

        return $nilai === 'tahunan' ? 'tahunan' : 'bulanan';
    }

    /**
     * Jadwal mengajar yang jatuh pada hari ini.
     *
     * `jadwals.hari` menyimpan nama hari dalam bahasa Indonesia, jadi eher
     * dicocokkan di sisi PHP daripada lewat `whereDate` yang tidak berlaku di
     * sini.
     *
     * @return Collection<int, Jadwal>
     */
    private function jadwalHariIni(): Collection
    {
        $hari = now()->locale('id')->translatedFormat('l');

        return Jadwal::query()
            ->with(['kelas:id,nama_kelas', 'guru.user:id,name'])
            ->where('hari', $hari)
            ->where('status', 'Aktif')
            ->orderBy('jam_mulai')
            ->limit(self::BATAS_JADWAL_HARI_INI)
            ->get();
    }

    /**
     * Kelas dengan siswa terbanyak, untuk memberi konteks jumlah siswa.
     *
     * @return list<array<string, mixed>>
     */
    private function kelasTeramai(): array
    {
        return Siswa::query()
            ->whereNotNull('kelas')
            ->where('kelas', '<>', '')
            ->selectRaw('kelas, count(*) as total')
            ->groupBy('kelas')
            ->orderByDesc('total')
            ->orderBy('kelas')
            ->limit(5)
            ->get()
            ->map(fn ($baris): array => [
                'kelas' => (string) $baris->kelas,
                'siswa' => (int) $baris->total,
            ])
            ->all();
    }
}
