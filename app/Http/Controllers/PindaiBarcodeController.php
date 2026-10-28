<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Mahasiswa;
use App\Models\SesiAbsensi;
use App\Services\SesiAbsensiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Pemindaian barcode presensi oleh petugas: dosen dan admin.
 *
 * Tiap mahasiswa punya satu barcode yang berisikan NIM-nya, jadi barcode
 * dipindai di sini berarti "mahasiswa ini sedang berada di kelas itu". Beda
 * dengan halaman absensi mahasiswa yang mencatat kehadiran atas nama pemilik
 * akun, halaman ini mencatat kehadiran atas nama mahasiswa yang dipindai, jadi
 * hasilnya ditujukan ke sesi yang sedang dipilih petugas.
 *
 * Waktu berlakunya absensi tetap datang dari jadwal yang diisi admin
 * (`jadwals.jam_mulai`/`jam_selesai`): sesi dibuat dari jam itu dan scan yang
 * masuk setelah `jam_selesai` ditolak sebagai kedaluwarsa.
 */
class PindaiBarcodeController extends Controller
{
    /**
     * Sesi absensi dipakai di dua method, jadi disimpan sekali di constructor.
     */
    public function __construct(protected SesiAbsensiService $sesiAbsensi) {}

    /**
     * Halaman pemindai barcode untuk dosen dan admin.
     *
     * Daftar sesi yang bisa dipilih dibatasi jadwal hari ini: dosen hanya
     * melihat jadwal yang diajarnya sendiri, sedangkan admin dan operator
     * melihat seluruh jadwal aktif hari ini supaya bisa memantau kelas mana
     * pun. Sesi dibuat lewat service yang sama dengan halaman mahasiswa,
     * jadi petugas tidak perlu menunggu scheduler yang di hosting gratisan
     * sering tidak berjalan.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $jadwal = Jadwal::query()
            ->where('status', 'Aktif')
            // Jadwal tahun akademik lampau tidak membuat sesi baru di tahun
            // sekarang, sama dengan halaman mahasiswa dan scheduler.
            ->where('tahun_akademik', now()->year)
            ->where('hari', SesiAbsensiService::namaHari(today()))
            ->when(
                $user->role === 'Dosen',
                fn ($query) => $query->where('dosen_id', $user->dosen?->getKey() ?? 0)
            )
            ->orderBy('jam_mulai')
            ->get();

        $dibuat = $this->sesiAbsensi->pastikanSesi($jadwal, today());

        /*
         | Sesinya diambil ulang dengan relasi yang dibutuhkan halaman.
         | `pastikanSesi` mengembalikan collection biasa saat jadwalnya kosong,
         | dan method `load()` hanya dimiliki collection Eloquent, jadi rantai
         | pemuatan tidak boleh diletakkan langsung di hasilnya.
         */
        $sesi = SesiAbsensi::query()
            ->with(['jadwal.kelas:id,nama_kelas'])
            ->withCount('absensis')
            ->whereIn('id', $dibuat->pluck('id'))
            ->orderBy('waktu_mulai')
            ->get();

        /*
         | Sesi yang sedang dipilih bisa dibawa lewat query `?sesi=`, supaya
         | pilihan yang sama tetap berlaku ketika petugas berpindah tab antara
         | halaman Pindai dan halaman QR Sesi. Kalau tidak ada — atau id-nya
         | bukan sesi hari ini — kembali ke sesi yang sedang berjalan.
         */
        $sesiTerpilih = $sesi->first(fn (SesiAbsensi $item): bool => $item->getKey() === (int) $request->query('sesi'))
            ?? $sesi->first(fn (SesiAbsensi $item): bool => $item->kondisiSekarang() === 'berjalan')
            ?? $sesi->first();

        $dosenPindai = $request->routeIs('dosen.pindai');

        return view('pindai.index', [
            'daftarSesi' => $sesi,
            'sesiTerpilih' => $sesiTerpilih,
            // Tab menentukan isi halaman: kamera pemindai atau tampilan QR Sesi.
            'tab' => $this->tabValid($request->query('tab')),
            'pilihanTab' => $this->pilihanTab(),
            'ruteHalaman' => $dosenPindai ? 'dosen.pindai' : 'cms.pindai',
            'ruteSimpan' => route($dosenPindai ? 'dosen.pindai.store' : 'cms.pindai.store'),
        ]);
    }

    /**
     * Tab halaman pemindai yang sedang dibuka.
     *
     * Hanya ada dua tab di dalam bagian Pindai Barcode: `pindai` (kamera dan
     * input manual) dan `qr` (tampilan QR Sesi untuk mahasiswa). Nilai lain
     * dianggap tab pindai, supaya alamat yang salah ketik tetap membuka
     * halaman pemindai, bukan halaman kosong.
     */
    protected function tabValid(mixed $nilai): string
    {
        $nilai = is_string($nilai) ? trim($nilai) : '';

        return $nilai === 'qr' ? 'qr' : 'pindai';
    }

    /**
     * Daftar tab di bagian Pindai Barcode.
     *
     * @return list<array{nilai: string, label: string, icon: string}>
     */
    protected function pilihanTab(): array
    {
        return [
            ['nilai' => 'pindai', 'label' => 'Pindai Barcode', 'icon' => 'fas fa-camera'],
            ['nilai' => 'qr', 'label' => 'QR Sesi', 'icon' => 'fas fa-qrcode'],
        ];
    }

    /**
     * Terima satu hasil scan lalu catat kehadiran untuk sesi terpilih.
     *
     * Semua penolakan dikirim sebagai JSON 4xx dengan pesan yang bisa langsung
     * dibaca petugas di layar, bukan exception, supaya halaman pemindai bisa
     * tetap berjalan dan langsung memindai mahasiswa berikutnya.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:32'],
            'sesi' => ['required', 'integer', Rule::exists('sesi_absensis', 'id')],
        ]);

        $tolak = fn (string $pesan, int $status = 422): JsonResponse => response()->json([
            'sukses' => false,
            'pesan' => $pesan,
        ], $status);

        $kode = $this->normalisasiKode($validated['kode']);

        $mahasiswa = Mahasiswa::query()->where('nim', $kode)->first();

        if ($mahasiswa === null) {
            return $tolak('Barcode tidak dikenal. Hanya barcode mahasiswa terdaftar yang bisa dipindai.');
        }

        // Kehadiran hanya bermakna untuk akun aktif; mahasiswa pindah atau
        // nonaktif sudah tidak lagi bagian dari kelas mana pun.
        if ($mahasiswa->status !== 'Aktif') {
            return $tolak('Status '.$mahasiswa->user?->name.' bukan Aktif, jadi kehadirannya tidak dicatat.');
        }

        $sesi = SesiAbsensi::query()
            ->with(['jadwal.kelas:id,nama_kelas'])
            ->find($validated['sesi']);

        if ($sesi === null || $sesi->jadwal === null) {
            return $tolak('Sesi absensi tidak dikenal. Muat ulang halaman pemindai lalu pilih sesi kembali.');
        }

        // Dosen hanya boleh mencatat kehadiran untuk jadwal yang diajarnya
        // sendiri, supaya akun dosen tidak bisa menandai kelas dosen lain.
        if ($request->user()->role === 'Dosen') {
            $dosen = $request->user()->dosen;

            if ($dosen === null || $sesi->jadwal->dosen_id !== $dosen->getKey()) {
                return $tolak('Sesi '.$sesi->judul().' bukan jadwalmu, jadi kehadiran tidak bisa dicatat lewat akun ini.', 403);
            }
        }

        // Barcode menandai mahasiswa secara umum, tetapi catatan absennya
        // menempel di kelas sesi. Barcode dari kelas lain ditolak supaya
        // mahasiswa satu kelas tidak bisa dicatat di sesi kelas lain.
        if ($sesi->jadwal->kelas?->nama_kelas !== $mahasiswa->kelas) {
            return $tolak('Barcode itu milik '.$mahasiswa->user?->name.' dari kelas '.$mahasiswa->kelas.', bukan kelas '.$sesi->jadwal->kelas?->nama_kelas.'.');
        }

        $kondisi = $sesi->kondisiSekarang();

        if ($kondisi === 'belum') {
            return $tolak('Sesi '.$sesi->judul().' belum dimulai (mulai '.$sesi->waktu_mulai->format('H:i').').');
        }

        if ($kondisi === 'selesai') {
            return $tolak('Sesi '.$sesi->judul().' sudah berakhir pada '.$sesi->waktu_selesai->format('H:i').', jadi absensinya sudah kedaluwarsa.');
        }

        $absensi = $this->catatKehadiran($sesi, $mahasiswa);

        if ($absensi === null) {
            return $tolak('Kehadiran '.$mahasiswa->user?->name.' untuk '.$sesi->judul().' sudah tercatat sebelumnya.');
        }

        return response()->json([
            'sukses' => true,
            'pesan' => 'Kehadiran '.$sesi->judul().' tercatat pada '.$absensi->waktu_absen->format('H:i').' WIB.',
            'mahasiswa' => $mahasiswa->user?->name ?? 'Mahasiswa',
            'nim' => $mahasiswa->nim,
            'kelas' => $mahasiswa->kelas,
            'mata_kuliah' => $sesi->judul(),
            'waktu' => $absensi->waktu_absen?->format('H:i'),
        ]);
    }

    /**
     * Catat kehadiran satu mahasiswa pada satu sesi.
     *
     * @return Absensi|null catatan yang baru dibuat, atau null kalau kehadiran
     *                      mahasiswa itu sudah ada di sesi yang sama
     */
    protected function catatKehadiran(SesiAbsensi $sesi, Mahasiswa $mahasiswa): ?Absensi
    {
        return DB::transaction(function () use ($sesi, $mahasiswa): ?Absensi {
            $sudahAda = Absensi::query()
                ->where('sesi_absensi_id', $sesi->getKey())
                ->where('mahasiswa_id', $mahasiswa->getKey())
                ->exists();

            if ($sudahAda) {
                return null;
            }

            return Absensi::create([
                'mahasiswa_id' => $mahasiswa->getKey(),
                'sesi_absensi_id' => $sesi->getKey(),
                'waktu_absen' => now(),
                'status' => 'hadir',
            ]);
        });
    }

    /**
     * Buang spasi, strip, dan tanda baca lain dari hasil scan.
     *
     * Kamera dan scanner fisik kadang menambahkan karakter pemisah, sementara
     * NIM di database disimpan polos tanpa pemisah.
     */
    protected function normalisasiKode(string $kode): string
    {
        return preg_replace('/[^A-Za-z0-9]/', '', $kode) ?? '';
    }
}
