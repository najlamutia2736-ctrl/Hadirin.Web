<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Kelas yang boleh dipilih pada form tambah & ubah siswa.
     *
     * Disimpan dari {@see Kelas::ROMBEL_TERSEDIA} supaya nama kelas pada form
     * siswa, filter, dan tabel `kelas` tidak pernah berbeda sumber.
     *
     * @var list<string>
     */
    private const KELAS_TERSEDIA = Kelas::ROMBEL_TERSEDIA;

    /**
     * Status keaktifan siswa yang recognized oleh form dan filter.
     *
     * @var list<string>
     */
    private const STATUS_TERSEDIA = ['Aktif', 'Nonaktif', 'Pindah'];

    /**
     * Daftar siswa (halaman Manajemen Siswa).
     *
     * Filter `kelas`, `status`, dan `q` dinormalisasi lebih dulu supaya nilai
     * tak dikenal (mis. kelas yang sudah dihapus) tidak menghasilkan filter
     * yang diam-diam selalu kosong.
     */
    public function index(Request $request): View
    {
        $daftarKelas = $this->daftarKelas();
        $daftarStatus = $this->daftarStatus();

        $kelas = $this->filter($request->query('kelas'), $daftarKelas);
        $status = $this->filter($request->query('status'), $daftarStatus);

        $students = Siswa::query()
            ->with('user:id,name,status')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('nisn', 'like', $keyword)
                        ->orWhereHas('user', fn ($q) => $q->where('name', 'like', $keyword));
                });
            })
            ->when($kelas !== null, fn ($query) => $query->where('kelas', $kelas))
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $stats = DB::table('siswas')
            ->selectRaw('count(*) as total')
            ->selectRaw("count(case when jenis_kelamin = 'L' then 1 end) as laki")
            ->selectRaw("count(case when jenis_kelamin = 'P' then 1 end) as perempuan")
            ->selectRaw('count(distinct kelas) as kelas')
            ->first();

        return view('cms.student.index', [
            'students' => $students,
            'stats' => $stats,
            'filterKelas' => $kelas,
            'filterStatus' => $status,
            'daftarKelas' => $daftarKelas,
            'daftarStatus' => $daftarStatus,
        ]);
    }

    /**
     * Kelas hasil union antara kelas yang dipakai siswa dan daftar baku.
     *
     * Kelas yang tidak ada di daftar baku ikut ditampilkan supaya siswa dengan
     * kelas lama tidak jadi tidak bisa difilter.
     *
     * @return list<string>
     */
    private function daftarKelas(): array
    {
        $dipakaiSiswa = Siswa::query()
            ->whereNotNull('kelas')
            ->distinct()
            ->pluck('kelas')
            ->map(fn ($kelas) => (string) $kelas)
            ->all();

        $daftar = array_values(array_unique([...self::KELAS_TERSEDIA, ...$dipakaiSiswa]));
        usort($daftar, 'strnatcmp');

        return $daftar;
    }

    /**
     * Status hasil union antara status yang dipakai siswa dan daftar baku.
     *
     * @return list<string>
     */
    private function daftarStatus(): array
    {
        $dipakaiSiswa = Siswa::query()
            ->whereNotNull('status')
            ->distinct()
            ->pluck('status')
            ->map(fn ($status) => (string) $status)
            ->all();

        $daftar = array_values(array_unique([...self::STATUS_TERSEDIA, ...$dipakaiSiswa]));
        usort($daftar, 'strnatcmp');

        return $daftar;
    }

    /**
     * Filter dari request, null bila kosong atau tidak dikenal.
     *
     * Nilai yang tidak dikenal ditolak, bukan diteruskan ke query, supaya
     * `?kelas=X-B` pada kelas yang sudah dihapus tidak diam-diam selalu kosong.
     *
     * @param  list<string>  $pilihan
     */
    private function filter(mixed $nilai, array $pilihan): ?string
    {
        if (! is_string($nilai)) {
            return null;
        }

        $nilai = trim($nilai);

        return in_array($nilai, $pilihan, true) ? $nilai : null;
    }

    /**
     * Simpan siswa baru dari modal "Tambah Siswa".
     *
     * Satu siswa selalu punya akun pengguna (foreign key `user_id` wajib),
     * jadi pembuatan User + Siswa dibungkus dalam satu transaksi.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'digits:8', 'unique:siswas,nisn'],
            'class' => ['required', Rule::in(self::KELAS_TERSEDIA)],
            'gender' => ['required', 'in:L,P'],
            'parent' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['nis'].'@siswa.sekolah.sch.id',
                'password' => $validated['nis'],
                'role' => 'Siswa',
                'status' => 'Aktif',
            ]);

            Siswa::create([
                'user_id' => $user->id,
                'nisn' => $validated['nis'],
                'jenis_kelamin' => $validated['gender'],
                'wali' => $validated['parent'] ?? null,
                'telepon_wali' => $validated['phone'] ?? null,
                'kelas' => $validated['class'],
                'status' => 'Aktif',
            ]);
        });

        return redirect()
            ->route('cms.student')
            ->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function tambahsiswa(Request $request): View
    {
        return view('cms.student.create');
    }

    public function edit(Siswa $siswa): View
    {
        $siswa->load('user:id,name,email,status');

        return view('cms.student.edit', [
            'siswa' => $siswa,
        ]);
    }

    public function update(Request $request, Siswa $siswa): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => [
                'required',
                'digits:8',
                Rule::unique('siswas', 'nisn')->ignore($siswa->id, 'id'),
            ],
            'class' => ['required', Rule::in(self::KELAS_TERSEDIA)],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'parent' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(self::STATUS_TERSEDIA)],
        ]);

        DB::transaction(function () use ($validated, $siswa) {
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

        return redirect()
            ->route('cms.student')
            ->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        DB::transaction(function () use ($siswa) {
            $user = $siswa->user;
            $siswa->delete();
            $user?->delete();
        });

        return redirect()
            ->route('cms.student')
            ->with('success', 'Siswa berhasil dihapus.');
    }
}
