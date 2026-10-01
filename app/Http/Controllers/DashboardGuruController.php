<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardGuruController extends Controller
{
    /**
     * Pemetaan nilai enum `absensis.status` ke kolom rekap.
     *
     * Di database status alpa disimpan sebagai `alpha`, sedangkan di laporan
     * ditampilkan sebagai "Alpa" supaya sama dengan rekap admin.
     *
     * @var array<string, string>
     */
    private const KOLOM_STATUS = [
        'hadir' => 'hadir',
        'izin' => 'izin',
        'sakit' => 'sakit',
        'alpha' => 'alpa',
    ];

    /**
     * Dashboard guru.
     *
     * Seluruh angka halaman ini berasal dari database yang sama dengan
     * dashboard admin, jadi siswa atau kelas yang baru ditambahkan dari
     * dashboard CMS langsung muncul di sini tanpa langkah tambahan.
     *
     * Saat diminta JSON, halaman ini mengembalikan payload-nya apa adanya
     * tanpa Blade. Ini yang dipakai polling untuk halaman real-time monitoring
     * tanpa perlu endpoint baru.
     */
    public function dashboard(Request $request): View|JsonResponse
    {
        $guruData = $this->dataGuru($request);

        if ($request->wantsJson()) {
            return response()->json($guruData);
        }

        return view('guru.dashboard', [
            'guruData' => $guruData,
        ]);
    }

    public function progres(): View
    {
        return view('guru.progres');
    }

    public function kelola(Request $request): View|JsonResponse
    {
        $guruData = $this->dataGuru($request);

        if ($request->wantsJson()) {
            return response()->json($guruData);
        }

        return view('guru.kelola', [
            'guruData' => $guruData,
        ]);
    }

    /**
     * Laporan kehadiran bulanan.
     *
     * Agregasi diambil dari tabel `absensis` yang sama dengan rekap di sisi
     * admin, tetapi dibatasi hanya kelas yang diampu guru yang sedang login.
     * Kalau admin menambah kelas atau siswa baru, angka di halaman ini ikut
     * berubah tanpa perlu ada sinkronisasi terpisah.
     */
    public function laporan(Request $request): View|JsonResponse
    {
        $laporan = $this->dataLaporan($request);

        if ($request->wantsJson()) {
            return response()->json($laporan);
        }

        return view('guru.laporan', [
            'guruData' => $this->dataGuru($request),
            'laporan' => $laporan,
        ]);
    }

    /**
     * Export laporan bulanan ke CSV yang bisa langsung dibuka di Excel.
     *
     * Mengikuti `RekapController::exportExcel`: pemisah titik koma dan BOM
     * UTF-8, karena itu bawaan Excel versi Indonesia.
     */
    public function exportLaporan(Request $request): StreamedResponse
    {
        $laporan = $this->dataLaporan($request);

        $namaBerkas = sprintf('laporan-bulanan-%s-%s.csv', $laporan['bulan'], now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($laporan): void {
            $keluaran = fopen('php://output', 'wb');

            fwrite($keluaran, "\xEF\xBB\xBF");

            fputcsv($keluaran, ['LAPORAN KEHADIRAN BULANAN'], ';');
            fputcsv($keluaran, ['Sekolah', config('app.name')], ';');
            fputcsv($keluaran, ['Guru', $laporan['guru']['nama'] ?? '-'], ';');
            fputcsv($keluaran, ['NIP', $laporan['guru']['nip'] ?? '-'], ';');
            fputcsv($keluaran, ['Mata Pelajaran', $laporan['guru']['mapel'] ?? '-'], ';');
            fputcsv($keluaran, ['Periode', $laporan['label']], ';');
            fputcsv($keluaran, ['Kelas', $laporan['filterKelas'] ?? 'Semua Kelas yang Diampu'], ';');
            fputcsv($keluaran, ['Dicetak', $laporan['dicetak']], ';');
            fputcsv($keluaran, [], ';');

            fputcsv($keluaran, ['RINGKASAN PER KELAS'], ';');
            fputcsv($keluaran, [
                'Kelas',
                'Jumlah Siswa',
                'Hadir',
                'Izin',
                'Sakit',
                'Alpa',
                'Total Catatan',
                'Persentase (%)',
            ], ';');

            foreach ($laporan['perKelas'] as $baris) {
                fputcsv($keluaran, [
                    $baris['kelas'],
                    $baris['siswa'],
                    $baris['hadir'],
                    $baris['izin'],
                    $baris['sakit'],
                    $baris['alpa'],
                    $baris['total'],
                    $baris['persentase'],
                ], ';');
            }

            fputcsv($keluaran, [], ';');
            fputcsv($keluaran, ['RINGKASAN PER SISWA'], ';');
            fputcsv($keluaran, [
                'No',
                'Nama Siswa',
                'NIS',
                'Kelas',
                'Hadir',
                'Izin',
                'Sakit',
                'Alpa',
                'Hari Tercatat',
                'Persentase (%)',
            ], ';');

            foreach ($laporan['perSiswa'] as $index => $baris) {
                fputcsv($keluaran, [
                    $index + 1,
                    $baris['nama'],
                    $baris['nis'],
                    $baris['kelas'],
                    $baris['hadir'],
                    $baris['izin'],
                    $baris['sakit'],
                    $baris['alpa'],
                    $baris['hari'],
                    $baris['persentase'],
                ], ';');
            }

            fputcsv($keluaran, [], ';');
            fputcsv($keluaran, [
                'TOTAL',
                '',
                '',
                '',
                $laporan['total']['hadir'],
                $laporan['total']['izin'],
                $laporan['total']['sakit'],
                $laporan['total']['alpa'],
                $laporan['total']['total'],
                $laporan['total']['persentase'],
            ], ';');

            fclose($keluaran);
        }, $namaBerkas, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    public function realtime(): View
    {
        return view('guru.realtime');
    }

    /**
     * Tambah siswa baru ke salah satu kelas yang diampu guru.
     *
     * Kelas tujuan divalidasi terhadap daftar kelas yang diampu, bukan
     * dipercaya dari request, supaya guru tidak bisa menambahkan siswa ke kelas
     * milik orang lain.
     */
    public function storeSiswa(Request $request): JsonResponse
    {
        $guru = $this->guruYangLogin($request);

        if ($guru === null) {
            return $this->ditolakAkses();
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'digits:8', Rule::unique('siswas', 'nisn')],
            'class' => ['required', 'string', 'max:50'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'parent' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $this->pastikanKelasMilikGuru($validated['class'], $guru);

        $siswa = DB::transaction(function () use ($validated): Siswa {
            // Sama seperti StudentController di sisi admin: satu siswa selalu
            // punya akun pengguna, jadi pembuatan dua tabel dibungkus transaksi.
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['nis'].'@siswa.sekolah.sch.id',
                'password' => $validated['nis'],
                'role' => 'Siswa',
                'status' => 'Aktif',
            ]);

            return Siswa::create([
                'user_id' => $user->id,
                'nisn' => $validated['nis'],
                'jenis_kelamin' => $validated['gender'],
                'wali' => $validated['parent'] ?? null,
                'telepon_wali' => $validated['phone'] ?? null,
                'kelas' => $validated['class'],
                'status' => 'Aktif',
            ]);
        });

        return response()->json([
            'message' => 'Siswa berhasil ditambahkan.',
            'siswa' => $this->barisSiswa($siswa),
        ], 201);
    }

    /**
     * Ubah data siswa yang sudah ada di kelas yang diampu.
     */
    public function updateSiswa(Request $request, Siswa $siswa): JsonResponse
    {
        $guru = $this->guruYangLogin($request);

        if ($guru === null) {
            return $this->ditolakAkses();
        }

        $this->pastikanSiswaMilikKelas($siswa, $guru);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'digits:8', Rule::unique('siswas', 'nisn')->ignore($siswa->id, 'id')],
            'class' => ['required', 'string', 'max:50'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'parent' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(['Aktif', 'Nonaktif', 'Pindah'])],
        ]);

        // Memindahkan siswa ke kelas lain harus tetap ke kelas yang diampu.
        $this->pastikanKelasMilikGuru($validated['class'], $guru);

        DB::transaction(function () use ($validated, $siswa): void {
            $siswa->update([
                'nisn' => $validated['nis'],
                'kelas' => $validated['class'],
                'jenis_kelamin' => $validated['gender'],
                'wali' => $validated['parent'] ?? null,
                'telepon_wali' => $validated['phone'] ?? null,
                'status' => $validated['status'],
            ]);

            $siswa->user?->update([
                'name' => $validated['name'],
                'email' => $validated['nis'].'@siswa.sekolah.sch.id',
                'status' => $validated['status'] === 'Aktif' ? 'Aktif' : 'Nonaktif',
            ]);
        });

        return response()->json([
            'message' => 'Data siswa berhasil diperbarui.',
            'siswa' => $this->barisSiswa($siswa->fresh()),
        ]);
    }

    /**
     * Hapus siswa beserta akunnya.
     */
    public function destroySiswa(Request $request, Siswa $siswa): JsonResponse
    {
        $guru = $this->guruYangLogin($request);

        if ($guru === null) {
            return $this->ditolakAkses();
        }

        $this->pastikanSiswaMilikKelas($siswa, $guru);

        DB::transaction(function () use ($siswa): void {
            $user = $siswa->user;
            $siswa->delete();
            $user?->delete();
        });

        return response()->json([
            'message' => 'Siswa berhasil dihapus.',
        ]);
    }

    /**
     * Data yang dikirim ke `guru.partials.data-guru` sebagai sumber awal.
     *
     * @return array{guru: array<string, string|null>|null, kelas: list<array<string, mixed>>, teraut: bool}
     */
    protected function dataGuru(Request $request): array
    {
        $guru = $request->user()?->guru;

        $kelas = $this->kelasYangDiampu($guru)
            ->map(fn (Kelas $kelas): array => $this->payloadKelas($kelas))
            ->values()
            ->all();

        return [
            'teraut' => $guru !== null,
            'guru' => $guru === null ? null : [
                'nama' => $guru->user?->name,
                'nip' => $guru->nip,
                'mapel' => $guru->mata_pelajaran,
                'kelas' => implode(', ', array_column($kelas, 'nama')),
            ],
            'kelas' => $kelas,
        ];
    }

    /**
     * Kelas milik guru yang sedang login.
     *
     * Relasi `guru_kelas` diisi dari dashboard admin (Manajemen Kelas),
     * sehingga daftar ini hanya berisi kelas yang benar-benar diampu guru
     * tersebut.
     */
    protected function kelasYangDiampu(?Guru $guru): Collection
    {
        $query = Kelas::query()
            ->where('status', 'Aktif')
            ->orderBy('tingkat')
            ->orderBy('nama_kelas');

        if ($guru !== null) {
            $query->whereHas('guru', fn ($relasi) => $relasi->whereKey($guru->getKey()));
        }

        return $query->get();
    }

    /**
     * Satu kelas beserta siswa dan absensi hari ini.
     *
     * Siswa yang belum punya catatan absensi hari ini dikirim dengan status
     * `null` supaya tampilan tidak salah menghitungnya sebagai alpha.
     *
     * @return array<string, mixed>
     */
    protected function payloadKelas(Kelas $kelas): array
    {
        $siswa = $kelas->siswa()
            ->with('user:id,name')
            ->orderBy('id')
            ->get();

        $absensiHariIni = $this->absensiHariIni($siswa->pluck('id'));

        $baris = $siswa->map(function (Siswa $siswa) use ($absensiHariIni): array {
            $absen = $absensiHariIni->get($siswa->getKey());

            return [
                'id' => $siswa->getKey(),
                'nama' => $siswa->user?->name ?? 'Siswa tanpa akun',
                'nis' => $siswa->nisn,
                'gender' => $siswa->jenis_kelamin,
                'wali' => $siswa->wali,
                'telepon' => $siswa->telepon_wali,
                // Status keaktifan siswa (Aktif/Nonaktif/Pindah).
                'status' => $siswa->status,
                // Status kehadiran hari ini; null berarti belum absen.
                'absensi' => $absen?->status,
                'waktu' => $absen?->waktu_absen?->toIso8601String(),
                'metode' => $absen === null ? null : 'Scan QR',
            ];
        })->values()->all();

        return [
            'nama' => $kelas->nama_kelas,
            'total' => count($baris),
            'siswa' => $baris,
        ];
    }

    /**
     * Bentuk satu baris siswa untuk respons JSON.
     *
     * @return array<string, mixed>
     */
    protected function barisSiswa(Siswa $siswa): array
    {
        return [
            'id' => $siswa->getKey(),
            'nama' => $siswa->user?->name ?? 'Siswa tanpa akun',
            'nis' => $siswa->nisn,
            'gender' => $siswa->jenis_kelamin,
            'wali' => $siswa->wali,
            'telepon' => $siswa->telepon_wali,
            'status' => $siswa->status,
            'absensi' => null,
        ];
    }

    /**
     * Absensi hari ini untuk sekumpulan siswa.
     *
     * Diambil dengan satu query untuk semua kelas supaya dashboard tidak
     * menambah satu query per kelas.
     *
     * @param  SupportCollection<int, int|string>  $siswaIds
     * @return Collection<int, Absensi>
     */
    protected function absensiHariIni(SupportCollection $siswaIds): Collection
    {
        if ($siswaIds->isEmpty()) {
            return new Collection;
        }

        return Absensi::query()
            ->whereIn('siswa_id', $siswaIds)
            ->whereDate('waktu_absen', today())
            ->get()
            ->keyBy('siswa_id');
    }

    /**
     * Susun seluruh isi halaman laporan bulanan.
     *
     * @return array<string, mixed>
     */
    protected function dataLaporan(Request $request): array
    {
        $guru = $this->guruYangLogin($request);

        $pilihanKelas = $this->kelasYangDiampu($guru)->pluck('nama_kelas')->all();

        $bulan = $this->bulanValid($request->query('bulan')) ?? now()->format('Y-m');
        $dari = Carbon::createFromFormat('Y-m-d', $bulan.'-01')->startOfDay();
        $sampai = $dari->copy()->endOfMonth()->endOfDay();

        // Filter kelas hanya diterima kalau benar-benar kelas milik guru ini,
        // supaya `?kelas=` tidak bisa membuka rekap kelas orang lain.
        $diminta = $this->teks($request->query('kelas'));
        $filterKelas = $diminta !== null && in_array($diminta, $pilihanKelas, true) ? $diminta : null;

        $kelas = $filterKelas !== null ? [$filterKelas] : $pilihanKelas;

        $siswa = $this->siswaLaporan($kelas);
        $rekap = $this->rekapLaporan($kelas, $dari, $sampai);

        return [
            'bulan' => $bulan,
            'label' => $dari->locale('id')->translatedFormat('F Y'),
            'dari' => $dari,
            'sampai' => $sampai,
            'filterKelas' => $filterKelas,
            'pilihanKelas' => $pilihanKelas,
            'pilihanBulan' => $this->pilihanBulan(),
            'guru' => $guru === null ? null : [
                'nama' => $guru->user?->name,
                'nip' => $guru->nip,
                'mapel' => $guru->mata_pelajaran,
            ],
            'perKelas' => $rekap['perKelas'],
            'perSiswa' => $rekap['perSiswa'],
            'perHari' => $rekap['perHari'],
            'total' => $rekap['total'],
            'dicetak' => now()->locale('id')->translatedFormat('d F Y H:i'),
        ];
    }

    /**
     * Siswa milik kelas-kelas yang sedang dilaporkan.
     *
     * @param  list<string>  $kelas
     * @return Collection<int, Siswa>
     */
    protected function siswaLaporan(array $kelas): Collection
    {
        if ($kelas === []) {
            return new Collection;
        }

        return Siswa::query()
            ->with('user:id,name')
            ->whereIn('kelas', $kelas)
            ->orderBy('kelas')
            ->orderBy('id')
            ->get();
    }

    /**
     * Agregasi absensi satu periode menjadi rekap per kelas, per siswa, per hari.
     *
     * Absensi dihitung dari baris `absensis` yang ada di periode tersebut.
     * Siswa tanpa catatan sama sekali tetap muncul di `perSiswa` dengan
     * angka nol, supaya tidak hilang dari laporan.
     *
     * @param  list<string>  $kelas
     * @return array{perKelas:list<array<string,mixed>>,perSiswa:list<array<string,mixed>>,perHari:list<array<string,mixed>>,total:array<string,int>}
     */
    protected function rekapLaporan(array $kelas, Carbon $dari, Carbon $sampai): array
    {
        $siswa = $this->siswaLaporan($kelas);

        $catatan = $this->catatanAbsensi($kelas, $dari, $sampai);

        $perSiswa = [];
        $perKelas = [];
        $perTanggal = [];

        foreach ($siswa as $s) {
            $perSiswa[$s->getKey()] = [
                'id' => $s->getKey(),
                'nama' => $s->user?->name ?? 'Siswa tanpa akun',
                'nis' => $s->nisn,
                'kelas' => (string) $s->kelas,
                'hadir' => 0,
                'izin' => 0,
                'sakit' => 0,
                'alpa' => 0,
                'total' => 0,
                'hari' => [],
            ];
        }

        foreach ($catatan as $baris) {
            $siswaId = (int) $baris->siswa_id;
            $kolom = self::KOLOM_STATUS[$baris->status] ?? null;

            if ($kolom === null || ! isset($perSiswa[$siswaId])) {
                continue;
            }

            $perSiswa[$siswaId][$kolom]++;
            $perSiswa[$siswaId]['total']++;
            $perSiswa[$siswaId]['hari'][$baris->tanggal] = true;

            $kelasSiswa = $perSiswa[$siswaId]['kelas'];

            $perKelas[$kelasSiswa] ??= $this->barisKosong($kelasSiswa);
            $perKelas[$kelasSiswa][$kolom]++;
            $perKelas[$kelasSiswa]['total']++;

            $perTanggal[$baris->tanggal] ??= $this->barisKosong($baris->tanggal);
            $perTanggal[$baris->tanggal][$kolom]++;
            $perTanggal[$baris->tanggal]['total']++;
        }

        // Kelas yang punya siswa tapi belum ada absensi tetap ikut dihitung.
        foreach ($perSiswa as $baris) {
            $namaKelas = $baris['kelas'];

            $perKelas[$namaKelas] ??= $this->barisKosong($namaKelas);
            $perKelas[$namaKelas]['siswa']++;
        }

        // Urutkan kelas secara natural (XII-A sebelum XII-B), bukan leksikografis.
        uksort($perKelas, 'strnatcmp');
        ksort($perTanggal);

        $daftarSiswa = array_values($perSiswa);

        foreach ($daftarSiswa as $indeks => $baris) {
            $daftarSiswa[$indeks]['hari'] = count($baris['hari']);
            $daftarSiswa[$indeks]['persentase'] = $this->persen($baris['hadir'], $baris['total']);
        }

        $daftarKelas = array_values($perKelas);

        foreach ($daftarKelas as $indeks => $baris) {
            $daftarKelas[$indeks]['persentase'] = $this->persen($baris['hadir'], $baris['total']);
        }

        $daftarHari = array_values($perTanggal);

        foreach ($daftarHari as $indeks => $baris) {
            $daftarHari[$indeks]['label'] = Carbon::createFromFormat('Y-m-d', $baris['kelas'])
                ->locale('id')
                ->translatedFormat('d');
            $daftarHari[$indeks]['persentase'] = $this->persen($baris['hadir'], $baris['total']);
            unset($daftarHari[$indeks]['kelas']);
        }

        $total = [
            'siswa' => count($daftarSiswa),
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
            'total' => 0,
        ];

        foreach ($daftarKelas as $baris) {
            $total['hadir'] += $baris['hadir'];
            $total['izin'] += $baris['izin'];
            $total['sakit'] += $baris['sakit'];
            $total['alpa'] += $baris['alpa'];
            $total['total'] += $baris['total'];
        }

        $total['persentase'] = $this->persen($total['hadir'], $total['total']);

        return [
            'perKelas' => $daftarKelas,
            'perSiswa' => $daftarSiswa,
            'perHari' => $daftarHari,
            'total' => $total,
        ];
    }

    /**
     * Baris kosong untuk dipakai saat menyetel akumulasi per kelompok.
     *
     * @return array<string, int|string>
     */
    protected function barisKosong(string $kunci): array
    {
        return [
            'kelas' => $kunci,
            'siswa' => 0,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
            'total' => 0,
        ];
    }

    /**
     * Catatan absensi pada periode tertentu, diambil sekali untuk seluruh kelas.
     *
     * @param  list<string>  $kelas
     * @return SupportCollection<int, object>
     */
    protected function catatanAbsensi(array $kelas, Carbon $dari, Carbon $sampai): SupportCollection
    {
        if ($kelas === []) {
            return new SupportCollection;
        }

        return Absensi::query()
            ->whereBetween('waktu_absen', [$dari, $sampai])
            ->whereIn('siswa_id', function ($query) use ($kelas) {
                $query->select('id')->from('siswas')->whereIn('kelas', $kelas);
            })
            ->get(['siswa_id', 'status', 'waktu_absen'])
            ->map(fn (Absensi $absen): object => (object) [
                'siswa_id' => $absen->siswa_id,
                'status' => $absen->status,
                'tanggal' => $absen->waktu_absen->format('Y-m-d'),
            ])
            ->values();
    }

    /**
     * 12 bulan terakhir untuk dropdown periode.
     *
     * @return list<array{nilai:string,label:string}>
     */
    protected function pilihanBulan(): array
    {
        $awal = now()->startOfMonth()->subMonths(11);

        $pilihan = [];

        for ($offset = 0; $offset <= 11; $offset++) {
            $bulan = $awal->copy()->addMonths($offset);

            $pilihan[] = [
                'nilai' => $bulan->format('Y-m'),
                'label' => $bulan->locale('id')->translatedFormat('F Y'),
            ];
        }

        return $pilihan;
    }

    /**
     * Persentase hadir, dibulatkan. Nol bila tidak ada catatan sama sekali.
     */
    protected function persen(int $jumlah, int $total): int
    {
        return $total > 0 ? (int) round($jumlah / $total * 100) : 0;
    }

    /**
     * Validasi format bulan `Y-m` dari query string.
     */
    protected function bulanValid(mixed $nilai): ?string
    {
        if (! is_string($nilai) || preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $nilai) !== 1) {
            return null;
        }

        return $nilai;
    }

    /**
     * Ambil teks non-kosong dari query string.
     */
    protected function teks(mixed $nilai): ?string
    {
        if (! is_string($nilai)) {
            return null;
        }

        $nilai = trim($nilai);

        return $nilai === '' ? null : $nilai;
    }

    /**
     * Guru yang sedang login, atau null kalau bukan akun guru.
     */
    protected function guruYangLogin(Request $request): ?Guru
    {
        return $request->user()?->guru;
    }

    /**
     * Respuesta seragam saat akun yang masuk bukan guru.
     */
    protected function ditolakAkses(): JsonResponse
    {
        return response()->json([
            'message' => 'Hanya akun guru yang bisa mengelola data kelas.',
        ], 403);
    }

    /**
     * Pastikan sebuah nama kelas benar-benar diampu guru.
     *
     * Nama kelas (bukan id) yang dibandingkan karena `siswas.kelas` menyimpan
     * nama kelas sebagai teks, sama seperti `kelas.nama_kelas`.
     *
     * @throws ValidationException
     */
    protected function pastikanKelasMilikGuru(string $namaKelas, Guru $guru): void
    {
        $boleh = $this->kelasYangDiampu($guru)
            ->contains(fn (Kelas $kelas): bool => $kelas->nama_kelas === $namaKelas);

        if (! $boleh) {
            throw ValidationException::withMessages([
                'class' => 'Kelas tersebut tidak termasuk kelas yang Anda ampu.',
            ]);
        }
    }

    /**
     * Pastikan siswa ini anggota kelas yang diampu guru.
     */
    protected function pastikanSiswaMilikKelas(Siswa $siswa, Guru $guru): void
    {
        $boleh = $this->kelasYangDiampu($guru)
            ->contains(fn (Kelas $kelas): bool => $kelas->nama_kelas === $siswa->kelas);

        abort_unless($boleh, 403, 'Siswa ini bukan anggota kelas yang Anda ampu.');
    }
}
