<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\SesiAbsensi;
use App\Models\User;
use App\Services\SesiAbsensiService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardDosenController extends Controller
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
     * Panjang periode yang boleh dipilih pada halaman progres, dalam hari.
     *
     * Dipakai juga untuk menghitung periode pembanding, yaitu rentang dengan
     * panjang yang sama tepat sebelum periode terpilih.
     *
     * @var array<string, int>
     */
    private const PERIODE_HARI = [
        '7' => 7,
        '30' => 30,
        '90' => 90,
        '180' => 180,
    ];

    /**
     * Batas persentase kehadiran sebelum mahasiswa masuk daftar perlu perhatian.
     */
    private const BATAS_PERHATIAN = 75;

    /**
     * Berapa bulan ke belakang untuk grafik tren dashboard dosen.
     */
    private const BULAN_TREN = 12;

    /**
     * Periode yang dipakai kalau dosen belum memilih atau memilih nilai aneh.
     */
    private const PERIODE_BAWAAN = '30';

    /**
     * Sesi presensi dibuat sebelum payload disusun.
     *
     * Dashboard adalah pembaca terbanyak, jadi sesi tiap jadwal hari ini dibuat
     * di sini kalau belum ada — dosen bisa melihat daftar mata kuliah yang sedang
     * berlangsung tanpa harus menunggu satu mahasiswa membuka halaman absensi
     * lebih dulu.
     */
    public function __construct(protected SesiAbsensiService $sesiAbsensi) {}

    /**
     * Dashboard dosen: ringkasan hari ini sekaligus pemantauan real-time.
     *
     * Dulu halaman ini terbagi dua, yaitu `dashboard` (ringkasan + tren) dan
     * `realtime` (pemantauan langsung). Keduanya membaca tabel `absensis` yang
     * sama, memakai pemilih kelas yang sama, dan punya tabel mahasiswa yang hampir
     * identik, jadi sekarang digabung ke sini. Pemantauan real-time tetap
     * berjalan lewat polling ke endpoint JSON halaman ini sendiri.
     *
     * Seluruh angka halaman ini berasal dari database yang sama dengan
     * dashboard admin, jadi mahasiswa atau kelas yang baru ditambahkan dari
     * dashboard CMS langsung muncul di sini tanpa langkah tambahan.
     *
     * Saat diminta JSON, halaman ini mengembalikan payload-nya apa adanya
     * tanpa Blade, jadi polling tidak perlu endpoint baru.
     */
    public function dashboard(Request $request): View|JsonResponse
    {
        $dosenData = $this->dataDosen($request);

        if ($request->wantsJson()) {
            return response()->json($dosenData);
        }

        return view('dosen.dashboard', [
            'dosenData' => $dosenData,
            'trenBulanan' => $this->trenBulanan($this->dosenYangLogin($request)),
        ]);
    }

    /**
     * Progres absensi lintas beberapa periode.
     *
     * Bedanya dengan laporan presensi: laporan terkunci satu bulan dan dipakai
     * untuk mengarsipkan angka, sedangkan halaman ini membandingkan periode
     * terpilih dengan periode sebelumnya sepanjang durasi yang sama. Jadi
     * yang ditampilkan bukan cuma rekap, tapi juga arah kehadiran.
     *
     * Agregasinya tetap dari tabel `absensis` yang sama dan dibatasi ke kelas
     * milik dosen yang sedang login, sehingga angkanya konsisten dengan
     * dashboard admin maupun laporan presensi.
     */
    public function kelola(Request $request): View|JsonResponse
    {
        $dosenData = $this->dataDosen($request);

        if ($request->wantsJson()) {
            return response()->json($dosenData);
        }

        // Mahasiswa barunya dikirim sebagai payload kecil (bukan model) supaya
        // halaman tinggal menyalinnya ke JavaScript lewat `@json` sederhana.
        $mahasiswaBaru = $this->mahasiswaBaru();

        return view('dosen.kelola', [
            'dosenData' => $dosenData,
            'mahasiswaBaru' => $mahasiswaBaru === null ? null : [
                'id' => $mahasiswaBaru->getKey(),
                'nim' => $mahasiswaBaru->nim,
                'nama' => $mahasiswaBaru->user?->name,
            ],
        ]);
    }

    /**
     * Laporan kehadiran: satu halaman untuk rekap bulanan dan progres.
     *
     * Dulu halaman ini terbagi dua, yaitu `laporan` (kalender bulan) dan
     * `progres` (rentang 7/30/90/180 hari). Keduanya dihitung dari tabel
     * `absensis` yang sama dan hanya berbeda cara memilih periodenya, jadi
     * sekarang digabung ke sini dan dibedakan lewat tab.
     *
     * Yang aktif hanya tab yang sedang dibuka; aggregate periode lain tidak
     * dihitung supaya halaman ini tidak membayar dua query ganda.
     *
     * Saat diminta JSON, halaman ini mengembalikan payload tab yang aktif
     * apa adanya tanpa Blade.
     */
    public function laporan(Request $request): View|JsonResponse
    {
        $tab = $this->tabValid($request->query('tab'));

        $laporan = $tab === 'periode'
            ? $this->dataLaporanPeriode($request)
            : $this->dataLaporan($request);

        if ($request->wantsJson()) {
            return response()->json($laporan);
        }

        return view('dosen.laporan', [
            'dosenData' => $this->dataDosen($request),
            'laporan' => $laporan,
        ]);
    }

    /**
     * Tab yang sedang dibuka, default ke rekap bulanan.
     */
    protected function tabValid(mixed $nilai): string
    {
        $nilai = is_string($nilai) ? trim($nilai) : '';

        return $nilai === 'periode' ? 'periode' : 'bulanan';
    }

    /**
     * Opsi tab untuk navigasi atas halaman laporan.
     *
     * @return list<array{nilai: string, label: string}>
     */
    protected function pilihanTab(): array
    {
        return [
            ['nilai' => 'bulanan', 'label' => 'Rekap Bulanan'],
            ['nilai' => 'periode', 'label' => 'Progres Presensi'],
        ];
    }

    /**
     * Export laporan presensi ke CSV yang bisa langsung dibuka di Excel.
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
            fputcsv($keluaran, ['Kampus', config('app.name')], ';');
            fputcsv($keluaran, ['Dosen', $laporan['dosen']['nama'] ?? '-'], ';');
            fputcsv($keluaran, ['NIDN', $laporan['dosen']['nidn'] ?? '-'], ';');
            fputcsv($keluaran, ['Mata Kuliah', $laporan['dosen']['mataKuliah'] ?? '-'], ';');
            fputcsv($keluaran, ['Periode', $laporan['label']], ';');
            fputcsv($keluaran, ['Kelas', $laporan['filterKelas'] ?? 'Semua Kelas yang Diampu'], ';');
            fputcsv($keluaran, ['Filter Mata Kuliah', $laporan['mata_kuliah'] ?? 'Semua Mata Kuliah'], ';');
            fputcsv($keluaran, ['Dicetak', $laporan['dicetak']], ';');
            fputcsv($keluaran, [], ';');

            fputcsv($keluaran, ['RINGKASAN PER KELAS'], ';');
            fputcsv($keluaran, [
                'Kelas',
                'Jumlah Mahasiswa',
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
                    $baris['mahasiswa'],
                    $baris['hadir'],
                    $baris['izin'],
                    $baris['sakit'],
                    $baris['alpa'],
                    $baris['total'],
                    $baris['persentase'],
                ], ';');
            }

            fputcsv($keluaran, [], ';');
            fputcsv($keluaran, ['RINGKASAN PER MATA KULIAH'], ';');
            fputcsv($keluaran, [
                'Mata Kuliah',
                'Sesi',
                'Hadir',
                'Izin',
                'Sakit',
                'Alpa',
                'Total Catatan',
                'Persentase (%)',
            ], ';');

            foreach ($laporan['perMataKuliah'] as $baris) {
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

            fputcsv($keluaran, [], ';');
            fputcsv($keluaran, ['RINGKASAN PER MAHASISWA'], ';');
            fputcsv($keluaran, [
                'No',
                'Nama Mahasiswa',
                'NIM',
                'Kelas',
                'Hadir',
                'Izin',
                'Sakit',
                'Alpa',
                'Catatan Presensi',
                'Persentase (%)',
            ], ';');

            foreach ($laporan['perMahasiswa'] as $index => $baris) {
                fputcsv($keluaran, [
                    $index + 1,
                    $baris['nama'],
                    $baris['nim'],
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

    /**
     * Halaman tambah mahasiswa baru untuk kelas yang diampu dosen.
     *
     * Bentuknya halaman penuh, bukan modal, mengikuti halaman tambah mahasiswa
     * di sisi admin (`cms.mahasiswa.create`). Pilihan kelasnya dibatasi kelas
     * yang memang diampu, sama seperti validasi saat penyimpanan.
     */
    public function create(Request $request): View
    {
        $dosen = $this->dosenYangLogin($request);

        abort_unless($dosen !== null, 403, 'Hanya akun dosen yang bisa menambahkan mahasiswa.');

        return view('dosen.kelola.create', [
            'pilihanKelas' => $this->kelasYangDiampu($dosen)
                ->map(fn (Kelas $kelas): string => $kelas->nama_kelas)
                ->values()
                ->all(),
        ]);
    }

    /**
     * Tambah mahasiswa baru ke salah satu kelas yang diampu dosen.
     *
     * Kelas tujuan divalidasi terhadap daftar kelas yang diampu, bukan
     * dipercaya dari request, supaya dosen tidak bisa menambahkan mahasiswa ke kelas
     * milik orang lain.
     *
     * Permintaan form (dari halaman tambah) dibalas dengan halaman kelola supaya
     * dosen langsung melihat daftar terbarunya, sedangkan permintaan JSON tetap
     * dibalas JSON 201 seperti sebelumnya. Pemisahan ini mengikuti pola
     * `wantsJson()` yang dipakai method `kelola()` dan `dashboard()`.
     */
    public function storeMahasiswa(Request $request): JsonResponse|RedirectResponse
    {
        $dosen = $this->dosenYangLogin($request);

        if ($dosen === null) {
            return $request->wantsJson()
                ? $this->ditolakAkses()
                : redirect()->route('dosen.kelola');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'digits:8', Rule::unique('mahasiswa', 'nim')],
            'class' => ['required', 'string', 'max:50'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'parent' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $this->pastikanKelasMilikDosen($validated['class'], $dosen);

        $mahasiswa = DB::transaction(function () use ($validated): Mahasiswa {
            // Sama seperti MahasiswaController di sisi admin: satu mahasiswa selalu
            // punya akun pengguna, jadi pembuatan dua tabel dibungkus transaksi.
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['nim'].'@mahasiswa.kampus.ac.id',
                'password' => $validated['nim'],
                'role' => 'Mahasiswa',
                'status' => 'Aktif',
            ]);

            return Mahasiswa::create([
                'user_id' => $user->id,
                'nim' => $validated['nim'],
                'jenis_kelamin' => $validated['gender'],
                'wali' => $validated['parent'] ?? null,
                'telepon_wali' => $validated['phone'] ?? null,
                'kelas' => $validated['class'],
                'status' => 'Aktif',
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Mahasiswa berhasil ditambahkan.',
                'mahasiswa' => $this->barisMahasiswa($mahasiswa),
            ], 201);
        }

        /*
         | Id mahasiswa barunya ikut di-flash supaya halaman kelola bisa langsung
         | membuka barcode miliknya. Barcode tiap mahasiswa berisikan NIM sehingga
         | langsung siap dipakai begitu datanya tersimpan.
         */
        return redirect()
            ->route('dosen.kelola')
            ->with('success', 'Mahasiswa berhasil ditambahkan.')
            ->with('mahasiswa_baru', $mahasiswa->getKey());
    }

    /**
     * Ubah data mahasiswa yang sudah ada di kelas yang diampu.
     */
    public function updateMahasiswa(Request $request, Mahasiswa $mahasiswa): JsonResponse
    {
        $dosen = $this->dosenYangLogin($request);

        if ($dosen === null) {
            return $this->ditolakAkses();
        }

        $this->pastikanMahasiswaMilikKelas($mahasiswa, $dosen);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'digits:8', Rule::unique('mahasiswa', 'nim')->ignore($mahasiswa->id, 'id')],
            'class' => ['required', 'string', 'max:50'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'parent' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(['Aktif', 'Nonaktif', 'Pindah'])],
        ]);

        // Memindahkan mahasiswa ke kelas lain harus tetap ke kelas yang diampu.
        $this->pastikanKelasMilikDosen($validated['class'], $dosen);

        DB::transaction(function () use ($validated, $mahasiswa): void {
            $mahasiswa->update([
                'nim' => $validated['nim'],
                'kelas' => $validated['class'],
                'jenis_kelamin' => $validated['gender'],
                'wali' => $validated['parent'] ?? null,
                'telepon_wali' => $validated['phone'] ?? null,
                'status' => $validated['status'],
            ]);

            $mahasiswa->user?->update([
                'name' => $validated['name'],
                'email' => $validated['nim'].'@mahasiswa.kampus.ac.id',
                'status' => $validated['status'] === 'Aktif' ? 'Aktif' : 'Nonaktif',
            ]);
        });

        return response()->json([
            'message' => 'Data mahasiswa berhasil diperbarui.',
            'mahasiswa' => $this->barisMahasiswa($mahasiswa->fresh()),
        ]);
    }

    /**
     * Hapus mahasiswa beserta akunnya.
     */
    public function destroyMahasiswa(Request $request, Mahasiswa $mahasiswa): JsonResponse
    {
        $dosen = $this->dosenYangLogin($request);

        if ($dosen === null) {
            return $this->ditolakAkses();
        }

        $this->pastikanMahasiswaMilikKelas($mahasiswa, $dosen);

        DB::transaction(function () use ($mahasiswa): void {
            $user = $mahasiswa->user;
            $mahasiswa->delete();
            $user?->delete();
        });

        return response()->json([
            'message' => 'Mahasiswa berhasil dihapus.',
        ]);
    }

    /**
     * Data yang dikirim ke `dosen.partials.data-dosen` sebagai sumber awal.
     *
     * @return array{dosen: array<string, string|null>|null, kelas: list<array<string, mixed>>, teraut: bool}
     */
    protected function dataDosen(Request $request): array
    {
        $dosen = $request->user()?->dosen?->load('mataKuliah');

        $kelasDiampu = $this->kelasYangDiampu($dosen);

        // Sesi hari ini dibuat lebih dulu supaya payload selalu memuat daftar
        // mata kuliah yang sedang berlangsung, walau belum ada mahasiswa yang
        // membuka halaman absensi hari ini. Pembuatannya dibatasi jadwal yang
        // benar-benar diampu dosen ini, bukan seluruh jadwal kampus.
        $this->buatSesiMilikDosen($dosen, $kelasDiampu->pluck('id'));

        $kelas = $kelasDiampu
            ->map(fn (Kelas $kelas): array => $this->payloadKelas($kelas))
            ->values()
            ->all();

        return [
            'teraut' => $dosen !== null,
            'dosen' => $dosen === null ? null : [
                'nama' => $dosen->user?->name,
                'nidn' => $dosen->nidn,
                // Nama & kode mata kuliah diambil dari tabel `mata_kuliah` yang sama
                // dengan dashboard admin, supaya dosen yang sama tidak bisa tampil
                // dengan mata kuliah berbeda di kedua dashboard.
                'mataKuliah' => $dosen->mataKuliah?->nama_mata_kuliah ?? $dosen->mata_kuliah,
                'kodeMataKuliah' => $dosen->mataKuliah?->kode_mata_kuliah,
                'kelas' => implode(', ', array_column($kelas, 'nama')),
            ],
            'kelas' => $kelas,
        ];
    }

    /**
     * Buat sesi hari ini untuk jadwal yang benar-benar diampu dosen ini.
     *
     * Dulu dashboard memanggil `pastikanSesiHariIni()` yang membuat sesi
     * seluruh kampus pada setiap kunjungan — termasuk kelas dosen lain.
     * Cukup jadwal mengajar dosen sendiri dan kelas yang diampunya, supaya
     * kerja tulis di halaman baca ini tetap ringan.
     *
     * @param  SupportCollection<int, int|string>  $kelasDiampu
     */
    protected function buatSesiMilikDosen(?Dosen $dosen, SupportCollection $kelasDiampu): void
    {
        if ($dosen === null) {
            return;
        }

        $jadwal = Jadwal::query()
            ->where('status', 'Aktif')
            ->where('tahun_akademik', now()->year)
            ->where('hari', SesiAbsensiService::namaHari(today()))
            ->where(function ($query) use ($dosen, $kelasDiampu) {
                $query->where('dosen_id', $dosen->getKey())
                    ->orWhereIn('kelas_id', $kelasDiampu);
            })
            ->get();

        $this->sesiAbsensi->pastikanSesi($jadwal, today());
    }

    /**
     * Tren kehadiran 12 bulan terakhir untuk kelas yang diampu dosen.
     *
     * Angkanya diambil dari tabel `absensis` yang sama dengan halaman Rekap di
     * sisi admin dan dengan laporan presensi dosen, sehingga grafik di dashboard
     * tidak mungkin berbeda dari angka di halaman lain.
     *
     * Bulan tanpa catatan tetap ikut dikembalikan dengan angka nol supaya
     * kurvanya tidak lompat. `total` di luar deret dipakai halaman untuk
     * membedakan "belum ada absensi sama sekali" dari "sudah ada, tapi bulan
     * ini kosong".
     *
     * @return array{tren:list<array<string,int|string>>,total:int}
     */
    protected function trenBulanan(?Dosen $dosen): array
    {
        $deret = [];

        $kelas = $this->kelasYangDiampu($dosen)->pluck('nama_kelas');

        if ($kelas->isNotEmpty()) {
            $mahasiswaIds = Mahasiswa::query()->whereIn('kelas', $kelas)->pluck('id');

            if ($mahasiswaIds->isNotEmpty()) {
                $deret = Absensi::query()
                    ->whereIn('mahasiswa_id', $mahasiswaIds)
                    ->where('waktu_absen', '>=', now()->startOfMonth()->subMonths(self::BULAN_TREN - 1))
                    ->selectRaw('substr(waktu_absen, 1, 7) as periode')
                    ->selectRaw('count(*) as total')
                    ->selectRaw('sum(case when status = ? then 1 else 0 end) as hadir', ['hadir'])
                    ->selectRaw('sum(case when status = ? then 1 else 0 end) as izin', ['izin'])
                    ->selectRaw('sum(case when status = ? then 1 else 0 end) as sakit', ['sakit'])
                    ->selectRaw('sum(case when status = ? then 1 else 0 end) as alpha', ['alpha'])
                    ->groupBy('periode')
                    ->get()
                    ->mapWithKeys(fn ($baris): array => [
                        (string) $baris->periode => [
                            'hadir' => (int) $baris->hadir,
                            'izin' => (int) $baris->izin,
                            'sakit' => (int) $baris->sakit,
                            'alpa' => (int) $baris->alpha,
                            'total' => (int) $baris->total,
                        ],
                    ])
                    ->all();
            }
        }

        $tren = [];
        $mulai = now()->startOfMonth()->subMonths(self::BULAN_TREN - 1);

        for ($i = 0; $i < self::BULAN_TREN; $i++) {
            $awal = $mulai->copy()->addMonthsNoOverflow($i);
            $baris = $deret[$awal->format('Y-m')] ?? null;

            $tren[] = [
                'label' => $awal->locale('id')->translatedFormat('M'),
                'hadir' => $baris['hadir'] ?? 0,
                'izin' => $baris['izin'] ?? 0,
                'sakit' => $baris['sakit'] ?? 0,
                'alpa' => $baris['alpa'] ?? 0,
                'total' => $baris['total'] ?? 0,
            ];
        }

        return [
            'tren' => $tren,
            'total' => array_sum(array_column($deret, 'total')),
        ];
    }

    /**
     * Kelas milik dosen yang sedang login.
     *
     * Relasi `dosen_kelas` diisi dari dashboard admin (Manajemen Kelas),
     * sehingga daftar ini hanya berisi kelas yang benar-benar diampu dosen
     * tersebut.
     */
    protected function kelasYangDiampu(?Dosen $dosen): Collection
    {
        $query = Kelas::query()
            ->where('status', 'Aktif')
            ->orderBy('tingkat')
            ->orderBy('nama_kelas');

        if ($dosen !== null) {
            $query->whereHas('dosen', fn ($relasi) => $relasi->whereKey($dosen->getKey()));
        }

        return $query->get();
    }

    /**
     * Satu kelas beserta mahasiswa dan absensi hari ini.
     *
     * Mahasiswa yang belum punya catatan presensi hari ini dikirim dengan status
     * `null` supaya tampilan tidak salah menghitungnya sebagai alpha.
     *
     * @return array<string, mixed>
     */
    protected function payloadKelas(Kelas $kelas): array
    {
        $mahasiswa = $kelas->mahasiswa()
            ->with('user:id,name')
            ->orderBy('id')
            ->get();

        $absensiHariIni = $this->absensiHariIni($mahasiswa->pluck('id'));

        $baris = $mahasiswa->map(function (Mahasiswa $mahasiswa) use ($absensiHariIni): array {
            $absen = $absensiHariIni->get($mahasiswa->getKey());

            return [
                'id' => $mahasiswa->getKey(),
                'nama' => $mahasiswa->user?->name ?? 'Mahasiswa tanpa akun',
                'nim' => $mahasiswa->nim,
                'gender' => $mahasiswa->jenis_kelamin,
                'wali' => $mahasiswa->wali,
                'telepon' => $mahasiswa->telepon_wali,
                // Status keaktifan mahasiswa (Aktif/Nonaktif/Pindah).
                'status' => $mahasiswa->status,
                // Status kehadiran hari ini; null berarti belum absen.
                'absensi' => $absen?->status,
                'waktu' => $absen?->waktu_absen?->toIso8601String(),
                // Cara presensi terlihat dari statusnya: izin/sakit datang
                // dari formulir mahasiswa, sedangkan hadir dari scan QR atau
                // Kode NIM yang catatannya sama persis.
                'metode' => match ($absen?->status) {
                    null => null,
                    'izin', 'sakit' => 'Izin/Sakit',
                    default => 'Scan QR',
                },
            ];
        })->values()->all();

        return [
            'nama' => $kelas->nama_kelas,
            'total' => count($baris),
            'mahasiswa' => $baris,
            // Presensi per mata kuliah: satu entri per sesi hari ini, lengkap
            // dengan catatan tiap mahasiswanya di sesi itu.
            'sesi' => $this->sesiHariIni($kelas),
        ];
    }

    /**
     * Sesi presensi hari ini untuk satu kelas, beserta absensinya per sesi.
     *
     * Presensi dihitung per mata kuliah, jadi dashboard dosen butuh daftar ini
     * untuk menampilkan siapa hadir di jam pelajaran mana. Sesi yang belum
     * dimulai tetap dikirim supaya mahasiswa yang belum absen terlihat sebagai
     * "belum", bukan hilang dari layar.
     *
     * @return list<array<string, mixed>>
     */
    protected function sesiHariIni(Kelas $kelas): array
    {
        $jadwalIds = Jadwal::query()
            ->where('kelas_id', $kelas->getKey())
            ->where('status', 'Aktif')
            ->where('tahun_akademik', now()->year)
            ->where('hari', SesiAbsensiService::namaHari(today()))
            ->pluck('id');

        if ($jadwalIds->isEmpty()) {
            return [];
        }

        $sesi = SesiAbsensi::query()
            ->with('jadwal:id,kelas_id,mata_kuliah,ruang')
            ->whereIn('jadwal_id', $jadwalIds)
            ->whereDate('waktu_mulai', today())
            ->orderBy('waktu_mulai')
            ->get();

        if ($sesi->isEmpty()) {
            return [];
        }

        $absensi = Absensi::query()
            ->whereIn('sesi_absensi_id', $sesi->pluck('id'))
            ->get()
            ->groupBy('sesi_absensi_id');

        return $sesi->map(function (SesiAbsensi $sesi) use ($absensi): array {
            $catatan = $absensi->get($sesi->getKey(), collect());

            return [
                'id' => $sesi->getKey(),
                'mata_kuliah' => $sesi->judul(),
                'jam' => $sesi->waktu_mulai->format('H:i').' - '.$sesi->waktu_selesai->format('H:i'),
                'ruang' => $sesi->jadwal?->ruang,
                // Kondisi terhadap waktu sekarang: `belum`, `berjalan`, `selesai`.
                'kondisi' => $sesi->kondisiSekarang(),
                // Catatan per mahasiswa pada sesi ini saja, supaya status mahasiswa
                // di satu mata kuliah tidak tertimpa oleh mata kuliah lain.
                'absensi' => $catatan->mapWithKeys(fn (Absensi $absen): array => [
                    $absen->mahasiswa_id => [
                        'status' => $absen->status,
                        'waktu' => $absen->waktu_absen?->toIso8601String(),
                    ],
                ])->all(),
            ];
        })->values()->all();
    }

    /**
     * Mahasiswa yang baru ditambahkan lewat halaman tambah.
     *
     * Id-nya di-flash oleh {@see storeMahasiswa()} supaya halaman kelola bisa
     * langsung membuka barcode untuknya, mengikuti aturan bahwa tiap mahasiswa
     * baru otomatis punya barcode siap pakai.
     */
    protected function mahasiswaBaru(): ?Mahasiswa
    {
        $id = session('mahasiswa_baru');

        return is_numeric($id)
            ? Mahasiswa::with('user:id,name')->find($id)
            : null;
    }

    /**
     * Bentuk satu baris mahasiswa untuk respons JSON.
     *
     * @return array<string, mixed>
     */
    protected function barisMahasiswa(Mahasiswa $mahasiswa): array
    {
        return [
            'id' => $mahasiswa->getKey(),
            'nama' => $mahasiswa->user?->name ?? 'Mahasiswa tanpa akun',
            'nim' => $mahasiswa->nim,
            'gender' => $mahasiswa->jenis_kelamin,
            'wali' => $mahasiswa->wali,
            'telepon' => $mahasiswa->telepon_wali,
            'status' => $mahasiswa->status,
            'absensi' => null,
        ];
    }

    /**
     * Absensi hari ini untuk sekumpulan mahasiswa.
     *
     * Diambil dengan satu query untuk semua kelas supaya dashboard tidak
     * menambah satu query per kelas.
     *
     * @param  SupportCollection<int, int|string>  $mahasiswaIds
     * @return Collection<int, Absensi>
     */
    protected function absensiHariIni(SupportCollection $mahasiswaIds): Collection
    {
        if ($mahasiswaIds->isEmpty()) {
            return new Collection;
        }

        return Absensi::query()
            ->whereIn('mahasiswa_id', $mahasiswaIds)
            ->whereDate('waktu_absen', today())
            // Satu mahasiswa bisa punya banyak catatan hari ini (per mata
            // kuliah), jadi urutannya dibalik supaya yang terbaru yang menang.
            ->orderByDesc('waktu_absen')
            ->get()
            ->keyBy('mahasiswa_id');
    }

    /**
     * Deret harian untuk grafik progres.
     *
     * `perHari` dari rekap laporan hanya berisi tanggal yang ada catatannya.
     * Di sini seluruh tanggal pada periode dilewati supaya kurvanya tetap
     * naik-turun rapi dan hari tanpa sesi absensi terlihat sebagai titik
     * kosong, bukan lompatan.
     *
     * @param  list<array<string, mixed>>  $perHari
     * @return list<array<string, int|string>>
     */
    protected function trenHarian(array $perHari, Carbon $dari, Carbon $sampai, int $jumlahHari): array
    {
        $catatan = [];

        foreach ($perHari as $baris) {
            $catatan[$baris['tanggal']] = $baris;
        }

        $tren = [];

        for ($hari = 0; $hari < $jumlahHari; $hari++) {
            $tanggal = $dari->copy()->addDays($hari);
            $baris = $catatan[$tanggal->format('Y-m-d')] ?? null;

            $tren[] = [
                'tanggal' => $tanggal->format('Y-m-d'),
                // Pekanan cukup nama hari, periode panjang cukup tanggal.
                'label' => $jumlahHari <= 7
                    ? $tanggal->locale('id')->translatedFormat('D')
                    : $tanggal->format('d/m'),
                'hadir' => $baris['hadir'] ?? 0,
                'izin' => $baris['izin'] ?? 0,
                'sakit' => $baris['sakit'] ?? 0,
                'alpa' => $baris['alpa'] ?? 0,
                'catatan' => $baris['total'] ?? 0,
                'persentase' => $this->persen($baris['hadir'] ?? 0, $baris['total'] ?? 0),
            ];
        }

        return $tren;
    }

    /**
     * Periode terpilih dari query string, atau default 30 hari kalau nilainya
     * tidak dikenal.
     */
    protected function periodeValid(mixed $nilai): string
    {
        $nilai = is_string($nilai) ? trim($nilai) : '';

        return isset(self::PERIODE_HARI[$nilai]) ? $nilai : self::PERIODE_BAWAAN;
    }

    /**
     * Rentang tanggal dari panjang periode yang dipilih.
     *
     * @return array{dari: Carbon, sampai: Carbon}
     */
    protected function rentangPeriode(string $periode): array
    {
        $hari = self::PERIODE_HARI[$periode] ?? 30;

        return [
            'dari' => today()->subDays($hari - 1)->startOfDay(),
            'sampai' => now()->endOfDay(),
        ];
    }

    /**
     * Opsi periode untuk dropdown filter.
     *
     * @return list<array{nilai: string, label: string}>
     */
    protected function pilihanPeriode(): array
    {
        return [
            ['nilai' => '7', 'label' => '7 Hari Terakhir'],
            ['nilai' => '30', 'label' => '30 Hari Terakhir'],
            ['nilai' => '90', 'label' => '3 Bulan Terakhir'],
            ['nilai' => '180', 'label' => '6 Bulan Terakhir'],
        ];
    }

    /**
     * Judul periode yang enak dibaca, dipadatkan kalau masih satu bulan.
     */
    protected function labelPeriode(Carbon $dari, Carbon $sampai): string
    {
        if ($dari->isSameMonth($sampai)) {
            return sprintf(
                '%d – %s',
                $dari->day,
                $sampai->locale('id')->translatedFormat('F Y')
            );
        }

        return sprintf(
            '%s – %s',
            $dari->locale('id')->translatedFormat('j M Y'),
            $sampai->locale('id')->translatedFormat('j M Y')
        );
    }

    /**
     * Isi halaman laporan untuk tab Rekap Bulanan.
     *
     * Dipakai juga oleh ekspor CSV, jadi payload-nya sengaja tidak bergantung
     * pada tab yang sedang dibuka di layar.
     *
     * @return array<string, mixed>
     */
    protected function dataLaporan(Request $request): array
    {
        $dosen = $this->dosenYangLogin($request);

        $kelasDiampu = $this->kelasYangDiampu($dosen);
        $pilihanKelas = $kelasDiampu->pluck('nama_kelas')->all();

        $bulan = $this->bulanValid($request->query('bulan')) ?? now()->format('Y-m');
        $dari = Carbon::createFromFormat('Y-m-d', $bulan.'-01')->startOfDay();
        $sampai = $dari->copy()->endOfMonth()->endOfDay();

        $filterKelas = $this->filterKelasValid($request, $pilihanKelas);

        // Filter per mata kuliah: pilihannya diambil dari jadwal kelas yang
        // diampu, sehingga laporan bisa dipertajam ke satu mata kuliah saja.
        $pilihanMataKuliah = $this->pilihanMataKuliah($kelasDiampu);
        $filterMataKuliah = $this->filterMataKuliahValid($request, $pilihanMataKuliah);

        $rekap = $this->rekapLaporan(
            $filterKelas !== null ? [$filterKelas] : $pilihanKelas,
            $dari,
            $sampai,
            $filterMataKuliah,
        );

        $jumlahHari = (int) $dari->diffInDays($sampai) + 1;

        return [
            'tab' => 'bulanan',
            'pilihanTab' => $this->pilihanTab(),
            'bulan' => $bulan,
            'periode' => $this->periodeValid($request->query('periode')),
            'jumlahHari' => $jumlahHari,
            'label' => $dari->locale('id')->translatedFormat('F Y'),
            'dari' => $dari,
            'sampai' => $sampai,
            'filterKelas' => $filterKelas,
            'pilihanKelas' => $pilihanKelas,
            'mata_kuliah' => $filterMataKuliah,
            'pilihanMataKuliah' => $pilihanMataKuliah,
            'pilihanBulan' => $this->pilihanBulan(),
            'pilihanPeriode' => $this->pilihanPeriode(),
            'dosen' => $this->identitasDosen($dosen),
            'perKelas' => $rekap['perKelas'],
            'perMahasiswa' => $rekap['perMahasiswa'],
            'perMataKuliah' => $rekap['perMataKuliah'],
            'perJadwal' => $rekap['perJadwal'],
            'perHari' => $rekap['perHari'],
            'trenHarian' => $this->trenHarian($rekap['perHari'], $dari, $sampai, $jumlahHari),
            'total' => $rekap['total'],
            'sebelumnya' => null,
            'selisih' => null,
            'hariEfektif' => count($rekap['perHari']),
            'batasPerhatian' => self::BATAS_PERHATIAN,
            'perluPerhatian' => $this->mahasiswaPerluPerhatian($rekap['perMahasiswa']),
            'adaCatatan' => $rekap['total']['total'] > 0,
            'dicetak' => now()->locale('id')->translatedFormat('d F Y H:i'),
        ];
    }

    /**
     * Isi halaman laporan untuk tab Progres Presensi.
     *
     * Bedanya dengan rekap bulanan: periodenya bukan satu bulan kalender,
     * melainkan rentang berpatokan hari ke belakang. Karena itu halaman ini
     * selalu membandingkan periodenya dengan rentang sepanjang durasi yang
     * sama tepat sebelumnya, supaya dosen tahu arah kehadiran bukan cuma
     * angkanya.
     *
     * @return array<string, mixed>
     */
    protected function dataLaporanPeriode(Request $request): array
    {
        $dosen = $this->dosenYangLogin($request);

        $kelasDiampu = $this->kelasYangDiampu($dosen);
        $pilihanKelas = $kelasDiampu->pluck('nama_kelas')->all();

        $periode = $this->periodeValid($request->query('periode'));
        $rentang = $this->rentangPeriode($periode);
        $dari = $rentang['dari'];
        $sampai = $rentang['sampai'];
        $jumlahHari = (int) $dari->diffInDays($sampai) + 1;

        $filterKelas = $this->filterKelasValid($request, $pilihanKelas);

        $pilihanMataKuliah = $this->pilihanMataKuliah($kelasDiampu);
        $filterMataKuliah = $this->filterMataKuliahValid($request, $pilihanMataKuliah);

        $kelas = $filterKelas !== null ? [$filterKelas] : $pilihanKelas;

        $rekap = $this->rekapLaporan($kelas, $dari, $sampai, $filterMataKuliah);

        // Periode pembanding: rentang sepanjang sama, tepat sebelum periode
        // terpilih. Tanpa ini dosen hanya tahu angkanya, tidak tahu arahnya.
        $sebelumnya = $this->rekapLaporan(
            $kelas,
            $dari->copy()->subDays($jumlahHari),
            $dari->copy()->subDay()->endOfDay(),
            $filterMataKuliah,
        )['total'];

        $perKelas = $rekap['perKelas'];

        // Kelas paling baik di atas supaya daftar langsung terlihat.
        usort($perKelas, fn (array $a, array $b): int => $b['persentase'] <=> $a['persentase']
            ?: strnatcmp((string) $a['kelas'], (string) $b['kelas']));

        // Mahasiswa dengan kehadiran terendah dulu. Mahasiswa tanpa catatan presensi
        // disortir paling akhir supaya tidak dianggap sebagai yang paling buruk.
        $perMahasiswa = $rekap['perMahasiswa'];

        usort(
            $perMahasiswa,
            fn (array $a, array $b): int => [$a['total'] === 0 ? 1 : 0, $a['persentase'], $a['nama']]
                <=> [$b['total'] === 0 ? 1 : 0, $b['persentase'], $b['nama']]
        );

        return [
            'tab' => 'periode',
            'pilihanTab' => $this->pilihanTab(),
            // Ekspor CSV selalu memakai bulan, jadi `bulan` tetap ikut dikirim
            // supaya tombol ekspor di tab ini tidak kehilangan konteks.
            'bulan' => $this->bulanValid($request->query('bulan')) ?? now()->format('Y-m'),
            'periode' => $periode,
            'jumlahHari' => $jumlahHari,
            'label' => $this->labelPeriode($dari, $sampai),
            'dari' => $dari,
            'sampai' => $sampai,
            'filterKelas' => $filterKelas,
            'pilihanKelas' => $pilihanKelas,
            'mata_kuliah' => $filterMataKuliah,
            'pilihanMataKuliah' => $pilihanMataKuliah,
            'pilihanBulan' => $this->pilihanBulan(),
            'pilihanPeriode' => $this->pilihanPeriode(),
            'dosen' => $this->identitasDosen($dosen),
            'perKelas' => $perKelas,
            'perMahasiswa' => $perMahasiswa,
            'perMataKuliah' => $rekap['perMataKuliah'],
            'perJadwal' => $rekap['perJadwal'],
            'perHari' => $rekap['perHari'],
            'trenHarian' => $this->trenHarian($rekap['perHari'], $dari, $sampai, $jumlahHari),
            'total' => $rekap['total'],
            'sebelumnya' => $sebelumnya,
            'selisih' => $rekap['total']['persentase'] - $sebelumnya['persentase'],
            'hariEfektif' => count($rekap['perHari']),
            'batasPerhatian' => self::BATAS_PERHATIAN,
            'perluPerhatian' => $this->mahasiswaPerluPerhatian($rekap['perMahasiswa']),
            'adaCatatan' => $rekap['total']['total'] > 0,
            'dicetak' => now()->locale('id')->translatedFormat('d F Y H:i'),
        ];
    }

    /**
     * Identitas dosen untuk kepala laporan & ekspor CSV.
     *
     * Nama dan kode mata kuliah diambil dari tabel `mata_kuliah` yang sama dengan
     * dashboard admin, supaya laporan tidak pernah mencantumkan mata kuliah yang
     * berbeda dari yang tampil di dashboard.
     *
     * @return array<string, string|null>|null
     */
    protected function identitasDosen(?Dosen $dosen): ?array
    {
        if ($dosen === null) {
            return null;
        }

        return [
            'nama' => $dosen->user?->name,
            'nidn' => $dosen->nidn,
            'mataKuliah' => $dosen->namaMataKuliah(),
            'kodeMataKuliah' => $dosen->mataKuliah?->kode_mata_kuliah,
        ];
    }

    /**
     * Filter kelas dari query string, kalau memang milik dosen yang sedang login.
     *
     * Nilai yang tidak dikenal diabaikan, bukan dipakai apa adanya, supaya
     * `?kelas=` tidak pernah membuka rekap kelas orang lain.
     *
     * @param  list<string>  $pilihanKelas
     */
    protected function filterKelasValid(Request $request, array $pilihanKelas): ?string
    {
        $diminta = $this->teks($request->query('kelas'));

        return $diminta !== null && in_array($diminta, $pilihanKelas, true) ? $diminta : null;
    }

    /**
     * Filter mata kuliah dari query string, kalau memang ada di pilihan laporan.
     *
     * Sama seperti filter kelas: nilai yang tidak dikenal diabaikan, bukan
     * dipakai apa adanya, supaya `?mata_kuliah=` tidak pernah menyaring laporan
     * ke nama yang tidak ada pada kelas yang diampu.
     *
     * @param  list<string>  $pilihanMataKuliah
     */
    protected function filterMataKuliahValid(Request $request, array $pilihanMataKuliah): ?string
    {
        $diminta = $this->teks($request->query('mata_kuliah'));

        return $diminta !== null && in_array($diminta, $pilihanMataKuliah, true) ? $diminta : null;
    }

    /**
     * Daftar mata kuliah dari jadwal pada kelas-kelas yang diampu, untuk filter.
     *
     * Diambil dari tabel `jadwals` (bukan daftar mata kuliah kampus) supaya
     * pilihan filter selalu berisi mata kuliah yang memang punya jadwal mengajar
     * pada kelas tersebut, sama seperti data yang muncul di rekap per mata kuliah.
     *
     * @param  Collection<int, Kelas>  $kelas
     * @return list<string>
     */
    protected function pilihanMataKuliah(Collection $kelas): array
    {
        if ($kelas->isEmpty()) {
            return [];
        }

        $daftar = Jadwal::query()
            ->whereIn('kelas_id', $kelas->pluck('id'))
            ->distinct()
            ->pluck('mata_kuliah')
            ->filter()
            ->map(fn ($nama): string => (string) $nama)
            ->unique()
            ->values()
            ->all();

        usort($daftar, 'strnatcmp');

        return $daftar;
    }

    /**
     * Mahasiswa yang kehadirannya di bawah ambang dan punya catatan presensi.
     *
     * Tanpa catatan presensi tidak dihitung, karena "tidak punya data" bukan
     * sama dengan "kehadirannya rendah".
     *
     * @param  list<array<string, mixed>>  $perMahasiswa
     * @return list<array<string, mixed>>
     */
    protected function mahasiswaPerluPerhatian(array $perMahasiswa): array
    {
        return array_values(array_filter(
            $perMahasiswa,
            fn (array $baris): bool => $baris['total'] > 0 && $baris['persentase'] < self::BATAS_PERHATIAN
        ));
    }

    /**
     * Mahasiswa milik kelas-kelas yang sedang dilaporkan.
     *
     * @param  list<string>  $kelas
     * @return Collection<int, Mahasiswa>
     */
    protected function mahasiswaLaporan(array $kelas): Collection
    {
        if ($kelas === []) {
            return new Collection;
        }

        return Mahasiswa::query()
            ->with('user:id,name')
            ->whereIn('kelas', $kelas)
            ->orderBy('kelas')
            ->orderBy('id')
            ->get();
    }

    /**
     * Agregasi absensi satu periode menjadi rekap per kelas, per mahasiswa, per mata kuliah, per jadwal, per tanggal.
     *
     * Absensi dihitung dari baris `absensis` yang ada di periode tersebut.
     * Mahasiswa tanpa catatan sama sekali tetap muncul di `perMahasiswa` dengan
     * angka nol, supaya tidak hilang dari laporan.
     *
     * `perMataKuliah` adalah inti laporan sesi kuliah: satu baris per mata kuliah
     * berisi jumlah sesi yang tercatat beserta rincian statusnya. Baris absensi
     * lama tanpa jadwal masuk ke grup `SesiAbsensi::TANPA_JADWAL` supaya riwayat
     * lama tetap ikut terhitung.
     *
     * `perJadwal` memecah catatan yang sama per slot jadwal mengajar (hari dan
     * jam), sedangkan `perHari` memecahnya per tanggal; keduanya memakai sumber
     * baris yang persis sama seperti `perMataKuliah`, jadi totalnya selalu
     * identik dengan rekap lain di laporan ini.
     *
     * @param  list<string>  $kelas
     * @param  string|null  $mataKuliah  filter satu mata kuliah, null untuk semua
     * @return array{perKelas:list<array<string,mixed>>,perMahasiswa:list<array<string,mixed>>,perMataKuliah:list<array<string,mixed>>,perJadwal:list<array<string,mixed>>,perHari:list<array<string,mixed>>,total:array<string,int>}
     */
    protected function rekapLaporan(array $kelas, Carbon $dari, Carbon $sampai, ?string $mataKuliah = null): array
    {
        $mahasiswa = $this->mahasiswaLaporan($kelas);

        $catatan = $this->catatanAbsensi($kelas, $dari, $sampai, $mataKuliah);

        $perMahasiswa = [];
        $perKelas = [];
        $perMataKuliah = [];
        $perJadwal = [];
        $perTanggal = [];
        $sesiPerMataKuliah = [];
        $sesiPerJadwal = [];
        $sesiPerTanggal = [];

        foreach ($mahasiswa as $s) {
            $perMahasiswa[$s->getKey()] = [
                'id' => $s->getKey(),
                'nama' => $s->user?->name ?? 'Mahasiswa tanpa akun',
                'nim' => $s->nim,
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
            $mahasiswaId = (int) $baris->mahasiswa_id;
            $kolom = self::KOLOM_STATUS[$baris->status] ?? null;

            if ($kolom === null || ! isset($perMahasiswa[$mahasiswaId])) {
                continue;
            }

            $perMahasiswa[$mahasiswaId][$kolom]++;
            $perMahasiswa[$mahasiswaId]['total']++;
            $perMahasiswa[$mahasiswaId]['hari'][$baris->tanggal] = true;

            $kelasMahasiswa = $perMahasiswa[$mahasiswaId]['kelas'];

            $perKelas[$kelasMahasiswa] ??= $this->barisKosong($kelasMahasiswa);
            $perKelas[$kelasMahasiswa][$kolom]++;
            $perKelas[$kelasMahasiswa]['total']++;

            $perTanggal[$baris->tanggal] ??= $this->barisKosong($baris->tanggal);
            $perTanggal[$baris->tanggal][$kolom]++;
            $perTanggal[$baris->tanggal]['total']++;

            // Rekap per mata kuliah: satu baris per nama mata kuliah, dengan
            // jumlah sesi yang berbeda-beda dihitung dari id sesi uniknya.
            $perMataKuliah[$baris->mata_kuliah] ??= $this->barisKosongMataKuliah($baris->mata_kuliah);
            $perMataKuliah[$baris->mata_kuliah][$kolom]++;
            $perMataKuliah[$baris->mata_kuliah]['total']++;
            $sesiPerMataKuliah[$baris->mata_kuliah][(int) $baris->sesi] = true;

            // Rekap per jadwal: satu baris per slot jadwal mengajar (hari dan
            // jam), jadi tiap pertemuan terbaca terpisah. Catatan lama tanpa
            // `jadwal_id` masuk ke grup `TANPA_JADWAL` seperti pada rekap per
            // mata kuliah supaya riwayat lama tetap ikut terhitung.
            $kunciJadwal = $baris->jadwal_id ?? 'tanpa-jadwal';
            $perJadwal[$kunciJadwal] ??= $this->barisKosongJadwal($baris);
            $perJadwal[$kunciJadwal][$kolom]++;
            $perJadwal[$kunciJadwal]['total']++;
            $sesiPerJadwal[$kunciJadwal][(int) $baris->sesi] = true;

            // Sesi unik per tanggal dipakai tabel rekap per tanggal.
            $sesiPerTanggal[$baris->tanggal][(int) $baris->sesi] = true;
        }

        // Kelas yang punya mahasiswa tapi belum ada absensi tetap ikut dihitung.
        foreach ($perMahasiswa as $baris) {
            $namaKelas = $baris['kelas'];

            $perKelas[$namaKelas] ??= $this->barisKosong($namaKelas);
            $perKelas[$namaKelas]['mahasiswa']++;
        }

        // Filter per mata kuliah yang belum punya catatan tetap ditampilkan
        // dengan angka nol, supaya laporan kosong terbaca sebagai "belum ada
        // data", bukan sebagai "mata kuliah tidak ditemukan".
        if ($mataKuliah !== null && ! isset($perMataKuliah[$mataKuliah])) {
            $perMataKuliah[$mataKuliah] = $this->barisKosongMataKuliah($mataKuliah);
        }

        // Urutkan kelas secara natural (XII-A sebelum XII-B), bukan leksikografis.
        uksort($perKelas, 'strnatcmp');
        ksort($perTanggal);

        $daftarMahasiswa = array_values($perMahasiswa);

        foreach ($daftarMahasiswa as $indeks => $baris) {
            $daftarMahasiswa[$indeks]['hari'] = count($baris['hari']);
            $daftarMahasiswa[$indeks]['persentase'] = $this->persen($baris['hadir'], $baris['total']);
        }

        $daftarKelas = array_values($perKelas);

        foreach ($daftarKelas as $indeks => $baris) {
            $daftarKelas[$indeks]['persentase'] = $this->persen($baris['hadir'], $baris['total']);
        }

        $daftarMataKuliah = array_values($perMataKuliah);

        foreach ($daftarMataKuliah as $indeks => $baris) {
            $daftarMataKuliah[$indeks]['sesi'] = count($sesiPerMataKuliah[$baris['mata_kuliah']] ?? []);
            $daftarMataKuliah[$indeks]['persentase'] = $this->persen($baris['hadir'], $baris['total']);
        }

        // Nama mata kuliah urut natural, sama seperti urutan baris kelas.
        usort($daftarMataKuliah, fn (array $a, array $b): int => strnatcmp($a['mata_kuliah'], $b['mata_kuliah']));

        $daftarHari = array_values($perTanggal);

        foreach ($daftarHari as $indeks => $baris) {
            $daftarHari[$indeks]['tanggal'] = $baris['kelas'];
            $daftarHari[$indeks]['label'] = Carbon::createFromFormat('Y-m-d', $baris['kelas'])
                ->locale('id')
                ->translatedFormat('d');
            // Teks tanggal lengkap untuk tabel rekap per tanggal, misalnya
            // "Senin, 03 Oktober 2026", supaya barisnya langsung jelas.
            $daftarHari[$indeks]['namaTanggal'] = Carbon::createFromFormat('Y-m-d', $baris['kelas'])
                ->locale('id')
                ->translatedFormat('l, d F Y');
            $daftarHari[$indeks]['sesi'] = count($sesiPerTanggal[$baris['kelas']] ?? []);
            $daftarHari[$indeks]['persentase'] = $this->persen($baris['hadir'], $baris['total']);
            unset($daftarHari[$indeks]['kelas']);
        }

        // Rekap per jadwal: jumlah sesi & persentase per baris, lalu diurutkan
        // mengikuti hari kerja (Senin dulu), jam mulai, dan nama kelas.
        // Catatan tanpa jadwal diletakkan paling bawah.
        $urutanHari = array_flip(Jadwal::HARI_TERSEDIA);

        $daftarJadwal = [];

        foreach ($perJadwal as $kunci => $baris) {
            $baris['sesi'] = count($sesiPerJadwal[$kunci] ?? []);
            $baris['persentase'] = $this->persen($baris['hadir'], $baris['total']);

            $daftarJadwal[] = $baris;
        }

        usort($daftarJadwal, fn (array $a, array $b): int => [
            $a['hari'] === null ? 1 : 0,
            $urutanHari[$a['hari'] ?? ''] ?? PHP_INT_MAX,
            $a['jam'] ?? '',
            $a['kelas'] ?? '',
            $a['jadwal'],
        ] <=> [
            $b['hari'] === null ? 1 : 0,
            $urutanHari[$b['hari'] ?? ''] ?? PHP_INT_MAX,
            $b['jam'] ?? '',
            $b['kelas'] ?? '',
            $b['jadwal'],
        ]);

        $total = [
            'mahasiswa' => count($daftarMahasiswa),
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
            'total' => 0,
            'sesi' => 0,
        ];

        foreach ($daftarKelas as $baris) {
            $total['hadir'] += $baris['hadir'];
            $total['izin'] += $baris['izin'];
            $total['sakit'] += $baris['sakit'];
            $total['alpa'] += $baris['alpa'];
            $total['total'] += $baris['total'];
        }

        // Jumlah sesi dihitung dari rekap per mata kuliah karena kelas tidak
        // punya konsep sesi; tiap sesi hanya muncul di satu baris mata kuliah.
        $total['sesi'] = array_sum(array_column($daftarMataKuliah, 'sesi'));

        $total['persentase'] = $this->persen($total['hadir'], $total['total']);

        return [
            'perKelas' => $daftarKelas,
            'perMahasiswa' => $daftarMahasiswa,
            'perMataKuliah' => $daftarMataKuliah,
            'perJadwal' => $daftarJadwal,
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
            'mahasiswa' => 0,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
            'total' => 0,
        ];
    }

    /**
     * Baris kosong untuk dipakai saat menyetel akumulasi per mata kuliah.
     *
     * Bentuknya sengaja berbeda dari `barisKosong()` yang berbasis kelas,
     * karena baris rekap per mata kuliah memakai kunci `mata_kuliah` dan `sesi`.
     *
     * @return array<string, int|string>
     */
    protected function barisKosongMataKuliah(string $nama): array
    {
        return [
            'mata_kuliah' => $nama,
            'sesi' => 0,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
            'total' => 0,
        ];
    }

    /**
     * Baris kosong untuk akumulasi rekap per jadwal.
     *
     * Satu baris mewakili satu slot jadwal mengajar (hari, jam, mata kuliah,
     * dan kelas). Catatan lama tanpa `jadwal_id` tidak punya slot, jadi
     * seluruh labelnya diisi null dan judul barisnya memakai
     * `SesiAbsensi::TANPA_JADWAL`.
     *
     * @return array<string, int|string|null>
     */
    protected function barisKosongJadwal(object $baris): array
    {
        if ($baris->jadwal_id === null) {
            return [
                'jadwal' => SesiAbsensi::TANPA_JADWAL,
                'hari' => null,
                'jam' => null,
                'mata_kuliah' => null,
                'kelas' => null,
                'sesi' => 0,
                'hadir' => 0,
                'izin' => 0,
                'sakit' => 0,
                'alpa' => 0,
                'total' => 0,
            ];
        }

        // Kolom `time` berbentuk `07:00:00`, dirapikan menjadi `07:00 - 08:30`
        // mengikuti format `Jadwal::rentangJam()`.
        $jam = $baris->jam_mulai !== null && $baris->jam_selesai !== null
            ? substr((string) $baris->jam_mulai, 0, 5).' - '.substr((string) $baris->jam_selesai, 0, 5)
            : null;

        return [
            'jadwal' => trim($baris->hari.($jam !== null ? ', '.$jam : '')),
            'hari' => $baris->hari,
            'jam' => $jam,
            'mata_kuliah' => $baris->mata_kuliah,
            'kelas' => $baris->nama_kelas_jadwal,
            'sesi' => 0,
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
     * Absensi ditautkan ke `sesi_absensis` lalu ke `jadwals` supaya tiap baris
     * membawa nama mata kuliahnya, beserta hari, jam, dan kelas jadwalnya untuk
     * rekap per jadwal (join `kelas` hanya dipakai untuk menampilkan nama
     * kelas). Jadwal ditambahkan dengan left join karena baris lama
     * (`jadwal_id` null) tetap harus ikut terbawa; namanya nanti diganti
     * `SesiAbsensi::TANPA_JADWAL` oleh pemanggil agregasi.
     *
     * @param  list<string>  $kelas
     * @param  string|null  $mataKuliah  filter satu mata kuliah, null untuk semua
     * @return SupportCollection<int, object>
     */
    protected function catatanAbsensi(array $kelas, Carbon $dari, Carbon $sampai, ?string $mataKuliah = null): SupportCollection
    {
        if ($kelas === []) {
            return new SupportCollection;
        }

        return Absensi::query()
            ->join('sesi_absensis', 'absensis.sesi_absensi_id', '=', 'sesi_absensis.id')
            ->leftJoin('jadwals', 'sesi_absensis.jadwal_id', '=', 'jadwals.id')
            ->leftJoin('kelas', 'jadwals.kelas_id', '=', 'kelas.id')
            ->whereBetween('absensis.waktu_absen', [$dari, $sampai])
            ->when($mataKuliah !== null, fn ($query) => $query->where('jadwals.mata_kuliah', $mataKuliah))
            ->whereIn('absensis.mahasiswa_id', function ($query) use ($kelas) {
                $query->select('id')->from('mahasiswa')->whereIn('kelas', $kelas);
            })
            ->get([
                'absensis.mahasiswa_id',
                'absensis.status',
                'absensis.waktu_absen',
                'absensis.sesi_absensi_id',
                'sesi_absensis.jadwal_id',
                'jadwals.mata_kuliah',
                'jadwals.hari',
                'jadwals.jam_mulai',
                'jadwals.jam_selesai',
                'kelas.nama_kelas as nama_kelas_jadwal',
            ])
            ->map(fn (Absensi $absen): object => (object) [
                'mahasiswa_id' => $absen->mahasiswa_id,
                'status' => $absen->status,
                'tanggal' => $absen->waktu_absen->format('Y-m-d'),
                'sesi' => $absen->sesi_absensi_id,
                'mata_kuliah' => $absen->mata_kuliah ?? SesiAbsensi::TANPA_JADWAL,
                'jadwal_id' => $absen->jadwal_id,
                'hari' => $absen->hari,
                'jam_mulai' => $absen->jam_mulai,
                'jam_selesai' => $absen->jam_selesai,
                'nama_kelas_jadwal' => $absen->nama_kelas_jadwal,
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
     * Dosen yang sedang login, atau null kalau bukan akun dosen.
     */
    protected function dosenYangLogin(Request $request): ?Dosen
    {
        // `mataKuliah` dimuat di sini supaya semua halaman dosen yang
        // menampilkan nama & kode mata kuliah tidak_query ulang per halaman.
        return $request->user()?->dosen?->load('mataKuliah');
    }

    /**
     * Respuesta seragam saat akun yang masuk bukan dosen.
     */
    protected function ditolakAkses(): JsonResponse
    {
        return response()->json([
            'message' => 'Hanya akun dosen yang bisa mengelola data kelas.',
        ], 403);
    }

    /**
     * Pastikan sebuah nama kelas benar-benar diampu dosen.
     *
     * Nama kelas (bukan id) yang dibandingkan karena `mahasiswa.kelas` menyimpan
     * nama kelas sebagai teks, sama seperti `kelas.nama_kelas`.
     *
     * @throws ValidationException
     */
    protected function pastikanKelasMilikDosen(string $namaKelas, Dosen $dosen): void
    {
        $boleh = $this->kelasYangDiampu($dosen)
            ->contains(fn (Kelas $kelas): bool => $kelas->nama_kelas === $namaKelas);

        if (! $boleh) {
            throw ValidationException::withMessages([
                'class' => 'Kelas tersebut tidak termasuk kelas yang Anda ampu.',
            ]);
        }
    }

    /**
     * Pastikan mahasiswa ini anggota kelas yang diampu dosen.
     */
    protected function pastikanMahasiswaMilikKelas(Mahasiswa $mahasiswa, Dosen $dosen): void
    {
        $boleh = $this->kelasYangDiampu($dosen)
            ->contains(fn (Kelas $kelas): bool => $kelas->nama_kelas === $mahasiswa->kelas);

        abort_unless($boleh, 403, 'Mahasiswa ini bukan anggota kelas yang Anda ampu.');
    }
}
