<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Mahasiswa;
use App\Models\SesiAbsensi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RekapController extends Controller
{
    /**
     * Nama bulan dalam bahasa Indonesia untuk label periode.
     *
     * @var array<int, string>
     */
    private const NAMA_BULAN = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Pemetaan nilai enum `absensis.status` ke kolom rekap.
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
     * Pemetaan kolom rekap ke judul kolom pada hasil export.
     *
     * @var array<string, string>
     */
    private const JUDUL_KOLOM = [
        'kelas' => 'Kelas',
        'mahasiswa' => 'Jumlah Mahasiswa',
        'hadir' => 'Hadir',
        'izin' => 'Izin',
        'sakit' => 'Sakit',
        'alpa' => 'Alpa',
        'total' => 'Total Catatan',
        'persentase' => 'Persentase Kehadiran (%)',
    ];

    public function index(Request $request): View
    {
        $periode = $this->periode($request);
        $rekap = $this->barisRekap($periode);
        $rekapMataKuliah = $this->barisRekapMataKuliah($periode);

        return view('cms.rekap.index', [
            'rekap' => $rekap,
            'rekapMataKuliah' => $rekapMataKuliah,
            'total' => $this->ringkasan($rekap),
            'totalMataKuliah' => $this->ringkasanMataKuliah($rekapMataKuliah),
            'periode' => $periode,
            'pilihanBulan' => $this->pilihanBulan(),
            'pilihanKelas' => $this->pilihanKelas(),
            'pilihanMataKuliah' => $this->pilihanMataKuliah(),
        ]);
    }

    /**
     * Export rekap ke berkas CSV yang langsung dapat dibuka di Microsoft Excel.
     *
     * Dipakai titik koma sebagai pemisah kolom karena itu standart bawaan
     * Excel versi Indonesia, dan diawali BOM UTF-8 agar karakter huruf
     * beraksen tidak berantakan.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $periode = $this->periode($request);
        $rekap = $this->barisRekap($periode);
        $rekapMataKuliah = $this->barisRekapMataKuliah($periode);
        $total = $this->ringkasan($rekap);
        $totalMataKuliah = $this->ringkasanMataKuliah($rekapMataKuliah);

        $namaBerkas = sprintf('rekap-kehadiran-%s-%s.csv', $periode['kunci'], now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($rekap, $rekapMataKuliah, $total, $totalMataKuliah, $periode): void {
            $keluaran = fopen('php://output', 'wb');

            fwrite($keluaran, "\xEF\xBB\xBF");

            fputcsv($keluaran, ['REKAP KEHADIRAN MAHASISWA'], ';');
            fputcsv($keluaran, ['Kampus', config('app.name')], ';');
            fputcsv($keluaran, ['Periode', $periode['label']], ';');
            fputcsv($keluaran, ['Kelas', $periode['kelas'] ?? 'Semua Kelas'], ';');
            fputcsv($keluaran, ['Filter Mata Kuliah', $periode['mata_kuliah'] ?? 'Semua Mata Kuliah'], ';');
            fputcsv($keluaran, ['Dicetak', $periode['dicetak']], ';');
            fputcsv($keluaran, [], ';');

            fputcsv($keluaran, array_values(self::JUDUL_KOLOM), ';');

            foreach ($rekap as $baris) {
                fputcsv($keluaran, [
                    $baris['kelas'],
                    $baris['mahasiswa'],
                    $baris['hadir'],
                    $baris['izin'],
                    $baris['sakit'],
                    $baris['alpa'],
                    $baris['total'],
                    $baris['persentase'],
                ], ';');
            }

            fputcsv($keluaran, [
                'TOTAL',
                $total['mahasiswa'],
                $total['hadir'],
                $total['izin'],
                $total['sakit'],
                $total['alpa'],
                $total['total'],
                $total['persentase'],
            ], ';');

            fputcsv($keluaran, [], ';');
            fputcsv($keluaran, ['REKAP PER MATA KULIAH'], ';');
            fputcsv($keluaran, [
                'Mata Kuliah',
                'Sesi',
                'Hadir',
                'Izin',
                'Sakit',
                'Alpa',
                'Total Catatan',
                'Persentase Kehadiran (%)',
            ], ';');

            foreach ($rekapMataKuliah as $baris) {
                fputcsv($keluaran, [
                    $baris['mata_kuliah'],
                    $baris['sesi'],
                    $baris['hadir'],
                    $baris['izin'],
                    $baris['sakit'],
                    $baris['alpa'],
                    $baris['total'],
                    $baris['persentase'],
                ], ';');
            }

            fputcsv($keluaran, [
                'TOTAL',
                $totalMataKuliah['sesi'],
                $totalMataKuliah['hadir'],
                $totalMataKuliah['izin'],
                $totalMataKuliah['sakit'],
                $totalMataKuliah['alpa'],
                $totalMataKuliah['total'],
                $totalMataKuliah['persentase'],
            ], ';');

            fclose($keluaran);
        }, $namaBerkas, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * Export rekap ke halaman cetak (print-friendly) yang disimpan pengguna
     * sebagai PDF lewat dialog "Save as PDF" pada browser.
     */
    public function exportPdf(Request $request): View
    {
        $periode = $this->periode($request);
        $rekap = $this->barisRekap($periode);
        $rekapMataKuliah = $this->barisRekapMataKuliah($periode);

        return view('cms.rekap.pdf', [
            'rekap' => $rekap,
            'rekapMataKuliah' => $rekapMataKuliah,
            'total' => $this->ringkasan($rekap),
            'totalMataKuliah' => $this->ringkasanMataKuliah($rekapMataKuliah),
            'periode' => $periode,
        ]);
    }

    /**
     * Normalisasi filter dari request menjadi rentang periode yang siap dipakai query.
     *
     * `bulan` yang terisi menentukan periode. Exception-nya: bila `dari`/`sampai`
     * yang dikirim berbeda dari rentang penuh bulan tersebut, berarti pengguna
     * mempersempit periode secara manual dan rentang tanggal itu yang dipakai.
     *
     * `mata_kuliah` menyaring kedua rekap (per kelas dan per mata kuliah) ke satu
     * nama mata kuliah saja; nilai apa pun diterima apa adanya karena hasilnya
     * cukup berupa tabel kosong bila namanya tidak ada di `jadwals`.
     *
     * @return array{bulan:?string,kelas:?string,mata_kuliah:?string,q:?string,dari:Carbon,sampai:Carbon,label:string,kunci:string,dicetak:string}
     */
    private function periode(Request $request): array
    {
        $bulan = $this->bulan($request->query('bulan'));
        $kelas = $this->teks($request->query('kelas'));
        $mataKuliah = $this->teks($request->query('mata_kuliah'));
        $q = $this->teks($request->query('q'));

        // Tanggal dari form hanya berisi tanggal tanpa jam, jadi batas tengah malam
        // dibuat eksplisit agar `whereBetween` mengikutsertakan seluruh hari tersebut.
        $dari = $this->parseTanggal($request->query('dari'))?->startOfDay();
        $sampai = $this->parseTanggal($request->query('sampai'))?->endOfDay();

        $awalBulan = $bulan !== null
            ? Carbon::createFromFormat('Y-m-d', $bulan.'-01')->startOfDay()
            : null;
        $akhirBulan = $awalBulan?->copy()->endOfMonth()->endOfDay();

        $rentangBulanPenuh = $dari === null || $sampai === null || $awalBulan === null
            || ($dari->equalTo($awalBulan) && $sampai->equalTo($akhirBulan));

        if ($bulan !== null && $rentangBulanPenuh) {
            $awal = $awalBulan;
            $akhir = $akhirBulan;
        } else {
            $awal = $dari ?? now()->startOfMonth()->startOfDay();
            $akhir = $sampai ?? $awal->copy()->endOfDay();
        }

        if ($awal->gt($akhir)) {
            [$awal, $akhir] = [$akhir->copy()->startOfDay(), $awal->copy()->endOfDay()];
        }

        return [
            'bulan' => $bulan,
            'kelas' => $kelas,
            'mata_kuliah' => $mataKuliah,
            'q' => $q,
            'dari' => $awal,
            'sampai' => $akhir,
            'label' => $this->labelPeriode($awal, $akhir),
            'kunci' => $bulan ?? $awal->format('Ymd'),
            'dicetak' => $this->tanggal(now(), denganWaktu: true),
        ];
    }

    /**
     * Agregasi rekap kehadiran per kelas untuk periode tertentu.
     *
     * Absensi ditautkan ke sesi lalu jadwal supaya filter `mata_kuliah` bisa
     * membatasi hitungan per kelas ke satu mata kuliah saja. Jumlah mahasiswa
     * tetap dihitung dari pendaftaran kelas, karena sistem tidak menyimpan
     * pendaftaran per mata kuliah.
     *
     * @param  array{bulan:?string,kelas:?string,mata_kuliah:?string,q:?string,dari:Carbon,sampai:Carbon,label:string,kunci:string,dicetak:string}  $periode
     * @return list<array{kelas:string,mahasiswa:int,hadir:int,izin:int,sakit:int,alpa:int,total:int,persentase:int}>
     */
    private function barisRekap(array $periode): array
    {
        $jumlahMahasiswa = Mahasiswa::query()
            ->when($periode['kelas'] !== null, fn ($query) => $query->where('kelas', $periode['kelas']))
            ->when($periode['q'] !== null, fn ($query) => $query->where('kelas', 'like', '%'.$periode['q'].'%'))
            ->selectRaw('kelas, count(*) as total')
            ->groupBy('kelas')
            ->pluck('total', 'kelas');

        $catatanAbsensi = Absensi::query()
            ->join('mahasiswa', 'absensis.mahasiswa_id', '=', 'mahasiswa.id')
            ->join('sesi_absensis', 'absensis.sesi_absensi_id', '=', 'sesi_absensis.id')
            ->leftJoin('jadwals', 'sesi_absensis.jadwal_id', '=', 'jadwals.id')
            ->whereBetween('absensis.waktu_absen', [$periode['dari'], $periode['sampai']])
            ->when($periode['kelas'] !== null, fn ($query) => $query->where('mahasiswa.kelas', $periode['kelas']))
            ->when($periode['mata_kuliah'] !== null, fn ($query) => $query->where('jadwals.mata_kuliah', $periode['mata_kuliah']))
            ->when($periode['q'] !== null, fn ($query) => $query->where('mahasiswa.kelas', 'like', '%'.$periode['q'].'%'))
            ->selectRaw('mahasiswa.kelas as kelas, absensis.status as status, count(*) as total')
            ->groupBy('mahasiswa.kelas', 'absensis.status')
            ->get();

        $kosong = fn (string $kelas, int $mahasiswa): array => [
            'kelas' => $kelas,
            'mahasiswa' => $mahasiswa,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
            'total' => 0,
            'persentase' => 0,
        ];

        $baris = $jumlahMahasiswa
            ->mapWithKeys(fn ($total, $kelas) => [$kelas => $kosong((string) $kelas, (int) $total)])
            ->all();

        foreach ($catatanAbsensi as $catatan) {
            $kelas = (string) $catatan->kelas;
            $kolom = self::PETA_STATUS[$catatan->status] ?? null;

            if ($kolom === null) {
                continue;
            }

            $baris[$kelas] ??= $kosong($kelas, 0);
            $baris[$kelas][$kolom] += (int) $catatan->total;
            $baris[$kelas]['total'] += (int) $catatan->total;
        }

        uksort($baris, 'strnatcmp');

        $rekap = array_values($baris);

        foreach ($rekap as $indeks => $baris) {
            $rekap[$indeks]['persentase'] = $baris['total'] > 0
                ? (int) round($baris['hadir'] / $baris['total'] * 100)
                : 0;
        }

        return $rekap;
    }

    /**
     * Agregasi rekap kehadiran per mata kuliah untuk periode tertentu.
     *
     * Satu baris per nama mata kuliah: jumlah sesi yang tercatat beserta
     * rincian status kehadirannya. Sesi adalah satuan kuliah yang sesungguhnya,
     * jadi rekap ini yang dipakai sebagai laporan utama kehadiran per mata
     * kuliah — bukan per hari atau per kelas.
     *
     * Baris absensi lama tanpa jadwal (`jadwal_id` null) digabung ke grup
     * `SesiAbsensi::TANPA_JADWAL` supaya riwayat lama tidak hilang dari rekap.
     *
     * @param  array{bulan:?string,kelas:?string,mata_kuliah:?string,q:?string,dari:Carbon,sampai:Carbon,label:string,kunci:string,dicetak:string}  $periode
     * @return list<array{mata_kuliah:string,sesi:int,hadir:int,izin:int,sakit:int,alpa:int,total:int,persentase:int}>
     */
    private function barisRekapMataKuliah(array $periode): array
    {
        $catatanAbsensi = Absensi::query()
            ->join('mahasiswa', 'absensis.mahasiswa_id', '=', 'mahasiswa.id')
            ->join('sesi_absensis', 'absensis.sesi_absensi_id', '=', 'sesi_absensis.id')
            ->leftJoin('jadwals', 'sesi_absensis.jadwal_id', '=', 'jadwals.id')
            ->whereBetween('absensis.waktu_absen', [$periode['dari'], $periode['sampai']])
            ->when($periode['kelas'] !== null, fn ($query) => $query->where('mahasiswa.kelas', $periode['kelas']))
            ->when($periode['mata_kuliah'] !== null, fn ($query) => $query->where('jadwals.mata_kuliah', $periode['mata_kuliah']))
            // Pencarian pada tabel ini menyoroti nama mata kuliah, sedangkan
            // pencarian tabel per kelas menyoroti nama kelas; satu kotak `q`
            // dipakai keduanya karena isinya memang berbeda tabel.
            ->when($periode['q'] !== null, fn ($query) => $query->where('jadwals.mata_kuliah', 'like', '%'.$periode['q'].'%'))
            ->selectRaw('jadwals.mata_kuliah as mata_kuliah, absensis.status as status, count(*) as total')
            ->groupBy('jadwals.mata_kuliah', 'absensis.status')
            ->get();

        // Jumlah sesi per mata kuliah dihitung terpisah dengan distinct id
        // sesi, karena satu sesi punya banyak baris absensi (satu per mahasiswa).
        $sesiPerMataKuliah = Absensi::query()
            ->join('mahasiswa', 'absensis.mahasiswa_id', '=', 'mahasiswa.id')
            ->join('sesi_absensis', 'absensis.sesi_absensi_id', '=', 'sesi_absensis.id')
            ->leftJoin('jadwals', 'sesi_absensis.jadwal_id', '=', 'jadwals.id')
            ->whereBetween('absensis.waktu_absen', [$periode['dari'], $periode['sampai']])
            ->when($periode['kelas'] !== null, fn ($query) => $query->where('mahasiswa.kelas', $periode['kelas']))
            ->when($periode['mata_kuliah'] !== null, fn ($query) => $query->where('jadwals.mata_kuliah', $periode['mata_kuliah']))
            ->when($periode['q'] !== null, fn ($query) => $query->where('jadwals.mata_kuliah', 'like', '%'.$periode['q'].'%'))
            ->selectRaw('jadwals.mata_kuliah as mata_kuliah, count(distinct absensis.sesi_absensi_id) as sesi')
            ->groupBy('jadwals.mata_kuliah')
            ->pluck('sesi', 'mata_kuliah');

        $kosong = fn (string $mataKuliah): array => [
            'mata_kuliah' => $mataKuliah,
            'sesi' => 0,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
            'total' => 0,
            'persentase' => 0,
        ];

        $baris = [];

        foreach ($catatanAbsensi as $catatan) {
            $mataKuliah = (string) ($catatan->mata_kuliah ?? SesiAbsensi::TANPA_JADWAL);
            $kolom = self::PETA_STATUS[$catatan->status] ?? null;

            if ($kolom === null) {
                continue;
            }

            $baris[$mataKuliah] ??= $kosong($mataKuliah);
            $baris[$mataKuliah][$kolom] += (int) $catatan->total;
            $baris[$mataKuliah]['total'] += (int) $catatan->total;
        }

        foreach ($sesiPerMataKuliah as $mataKuliah => $sesi) {
            // Kunci null pada hasil groupBy berubah jadi string kosong di PHP,
            // jadi dinormalkan kembali ke nama grup tanpa jadwal.
            $kunci = ($mataKuliah === null || $mataKuliah === '')
                ? SesiAbsensi::TANPA_JADWAL
                : (string) $mataKuliah;

            $baris[$kunci] ??= $kosong($kunci);
            $baris[$kunci]['sesi'] = (int) $sesi;
        }

        uksort($baris, 'strnatcmp');

        $rekap = array_values($baris);

        foreach ($rekap as $indeks => $baris) {
            $rekap[$indeks]['persentase'] = $baris['total'] > 0
                ? (int) round($baris['hadir'] / $baris['total'] * 100)
                : 0;
        }

        return $rekap;
    }

    /**
     * Jumlah keseluruhan seluruh baris rekap, dipakai untuk baris TOTAL pada export.
     *
     * @param  list<array{kelas:string,mahasiswa:int,hadir:int,izin:int,sakit:int,alpa:int,total:int,persentase:int}>  $rekap
     * @return array{mahasiswa:int,hadir:int,izin:int,sakit:int,alpa:int,total:int,persentase:int}
     */
    private function ringkasan(array $rekap): array
    {
        $total = ['mahasiswa' => 0, 'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'total' => 0, 'persentase' => 0];

        foreach ($rekap as $baris) {
            foreach (array_keys($total) as $kolom) {
                if ($kolom !== 'persentase') {
                    $total[$kolom] += $baris[$kolom];
                }
            }
        }

        $total['persentase'] = $total['total'] > 0
            ? (int) round($total['hadir'] / $total['total'] * 100)
            : 0;

        return $total;
    }

    /**
     * Jumlah keseluruhan rekap per mata kuliah untuk baris TOTAL.
     *
     * Bedanya dengan `ringkasan()`: kolom `mahasiswa` diganti `sesi`, karena
     * satuan rekap per mata kuliah adalah sesi kuliah, bukan jumlah peserta.
     *
     * @param  list<array{mata_kuliah:string,sesi:int,hadir:int,izin:int,sakit:int,alpa:int,total:int,persentase:int}>  $rekap
     * @return array{sesi:int,hadir:int,izin:int,sakit:int,alpa:int,total:int,persentase:int}
     */
    private function ringkasanMataKuliah(array $rekap): array
    {
        $total = ['sesi' => 0, 'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'total' => 0, 'persentase' => 0];

        foreach ($rekap as $baris) {
            foreach (array_keys($total) as $kolom) {
                if ($kolom !== 'persentase') {
                    $total[$kolom] += $baris[$kolom];
                }
            }
        }

        $total['persentase'] = $total['total'] > 0
            ? (int) round($total['hadir'] / $total['total'] * 100)
            : 0;

        return $total;
    }

    /**
     * Daftar 12 bulan terakhir untuk dropdown filter periode.
     *
     * @return list<array{nilai:string,label:string}>
     */
    private function pilihanBulan(): array
    {
        $awal = now()->startOfMonth()->subMonths(11);

        $pilihan = [];

        for ($offset = 0; $offset <= 11; $offset++) {
            $bulan = $awal->copy()->addMonths($offset);

            $pilihan[] = [
                'nilai' => $bulan->format('Y-m'),
                'label' => self::NAMA_BULAN[$bulan->month].' '.$bulan->year,
            ];
        }

        return $pilihan;
    }

    /**
     * Daftar kelas yang pernah ada mahasiswanya, untuk dropdown filter kelas.
     *
     * @return list<string>
     */
    private function pilihanKelas(): array
    {
        $daftar = Mahasiswa::query()
            ->whereNotNull('kelas')
            ->distinct()
            ->pluck('kelas')
            ->filter()
            ->map(fn ($kelas) => (string) $kelas)
            ->values()
            ->all();

        usort($daftar, 'strnatcmp');

        return $daftar;
    }

    /**
     * Daftar mata kuliah yang punya jadwal mengajar, untuk dropdown filter.
     *
     * Diambil dari `jadwals` bukan dari tabel `mata_kuliah`, supaya pilihan
     * selalu berisi mata kuliah yang bisa benar-benar punya catatan absensi
     * pada rekap — nama yang tidak pernah mengajar tidak menampilkan apa pun.
     *
     * @return list<string>
     */
    private function pilihanMataKuliah(): array
    {
        $daftar = Jadwal::query()
            ->whereNotNull('mata_kuliah')
            ->distinct()
            ->pluck('mata_kuliah')
            ->filter()
            ->map(fn ($mataKuliah) => (string) $mataKuliah)
            ->unique()
            ->values()
            ->all();

        usort($daftar, 'strnatcmp');

        return $daftar;
    }

    /**
     * Validasi nilai bulan `Y-m` dari request.
     */
    private function bulan(mixed $nilai): ?string
    {
        if (! is_string($nilai) || preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $nilai) !== 1) {
            return null;
        }

        return $nilai;
    }

    /**
     * Parsing tanggal `Y-m-d` dari request, null bila kosong atau tidak valid.
     */
    private function parseTanggal(mixed $nilai): ?Carbon
    {
        if (! is_string($nilai) || trim($nilai) === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', trim($nilai));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Ambil teks non-kosong dari query string.
     */
    private function teks(mixed $nilai): ?string
    {
        if (! is_string($nilai)) {
            return null;
        }

        $nilai = trim($nilai);

        return $nilai === '' ? null : $nilai;
    }

    /**
     * Label periode dalam bahasa Indonesia.
     *
     * Bulan penuh disingkat jadi "September 2026", sedangkan rentang sebagian
     * tetap menyebut hari, mis. "2 – 3 September 2026".
     */
    private function labelPeriode(Carbon $dari, Carbon $sampai): string
    {
        if ($dari->isSameDay($sampai)) {
            return $this->tanggal($dari);
        }

        $sebulanPenuh = $dari->isSameMonth($sampai)
            && $dari->day === 1
            && $sampai->day === $sampai->daysInMonth;

        if ($sebulanPenuh) {
            return self::NAMA_BULAN[$dari->month].' '.$dari->year;
        }

        if ($dari->isSameMonth($sampai)) {
            return sprintf('%d – %d %s %d', $dari->day, $sampai->day, self::NAMA_BULAN[$dari->month], $dari->year);
        }

        return $this->tanggal($dari).' – '.$this->tanggal($sampai);
    }

    /**
     * Format tanggal Indonesia, mis. "22 September 2026".
     */
    private function tanggal(Carbon $tanggal, bool $denganWaktu = false): string
    {
        $bulan = self::NAMA_BULAN[$tanggal->month] ?? (string) $tanggal->month;

        return $denganWaktu
            ? sprintf('%d %s %d %02d:%02d', $tanggal->day, $bulan, $tanggal->year, $tanggal->hour, $tanggal->minute)
            : sprintf('%d %s %d', $tanggal->day, $bulan, $tanggal->year);
    }
}
