<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    /**
     * Status keaktifan mahasiswa yang recognized oleh form dan filter.
     *
     * @var list<string>
     */
    private const STATUS_TERSEDIA = ['Aktif', 'Nonaktif', 'Pindah'];

    /**
     * Daftar mahasiswa (halaman Manajemen Mahasiswa).
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

        $mahasiswa = Mahasiswa::query()
            ->with('user:id,name,status')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('nim', 'like', $keyword)
                        ->orWhereHas('user', fn ($q) => $q->where('name', 'like', $keyword));
                });
            })
            ->when($kelas !== null, fn ($query) => $query->where('kelas', $kelas))
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $stats = DB::table('mahasiswa')
            ->selectRaw('count(*) as total')
            ->selectRaw("count(case when jenis_kelamin = 'L' then 1 end) as laki")
            ->selectRaw("count(case when jenis_kelamin = 'P' then 1 end) as perempuan")
            ->selectRaw('count(distinct kelas) as kelas')
            ->first();

        return view('cms.mahasiswa.index', [
            'mahasiswa' => $mahasiswa,
            'stats' => $stats,
            'filterKelas' => $kelas,
            'filterStatus' => $status,
            'daftarKelas' => $daftarKelas,
            'daftarStatus' => $daftarStatus,
            'pilihanKelas' => $this->pilihanKelas(),
        ]);
    }

    /**
     * Kelas hasil union antara kelas yang dipakai mahasiswa dan tabel `kelas`.
     *
     * Kelas yang tidak ada di tabel `kelas` ikut ditampilkan supaya mahasiswa dengan
     * kelas lama tidak jadi tidak bisa difilter.
     *
     * @return list<string>
     */
    private function daftarKelas(): array
    {
        $dipakaiMahasiswa = Mahasiswa::query()
            ->whereNotNull('kelas')
            ->distinct()
            ->pluck('kelas')
            ->map(fn ($kelas) => (string) $kelas)
            ->all();

        $daftar = array_values(array_unique([...$this->pilihanKelas(), ...$dipakaiMahasiswa]));
        usort($daftar, 'strnatcmp');

        return $daftar;
    }

    /**
     * Kelas yang boleh dipilih pada form tambah & ubah mahasiswa.
     *
     * Dibaca langsung dari tabel `kelas` supaya pilihan di halaman mahasiswa sama
     * persis dengan kelas yang ada di halaman Classes. Karena `mahasiswa.kelas`
     * menyimpan nama kelas sebagai teks, kesamaan nama itulah yang membuat
     * jumlah mahasiswa di Classes ikut terisi.
     *
     * @return list<string>
     */
    private function pilihanKelas(): array
    {
        return Kelas::query()
            ->orderBy('nama_kelas')
            ->pluck('nama_kelas')
            ->map(fn ($kelas) => (string) $kelas)
            ->all();
    }

    /**
     * Status hasil union antara status yang dipakai mahasiswa dan daftar baku.
     *
     * @return list<string>
     */
    private function daftarStatus(): array
    {
        $dipakaiMahasiswa = Mahasiswa::query()
            ->whereNotNull('status')
            ->distinct()
            ->pluck('status')
            ->map(fn ($status) => (string) $status)
            ->all();

        $daftar = array_values(array_unique([...self::STATUS_TERSEDIA, ...$dipakaiMahasiswa]));
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
     * Simpan mahasiswa baru dari modal "Tambah Mahasiswa".
     *
     * Satu mahasiswa selalu punya akun pengguna (foreign key `user_id` wajib),
     * jadi pembuatan User + Mahasiswa dibungkus dalam satu transaksi.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'digits:8', 'unique:mahasiswa,nim'],
            'class' => ['required', 'exists:kelas,nama_kelas'],
            'gender' => ['required', 'in:L,P'],
            'parent' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
        ], [
            'class.exists' => 'Kelas tersebut belum terdaftar di halaman Classes.',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['nim'].'@mahasiswa.kampus.ac.id',
                'password' => $validated['nim'],
                'role' => 'Mahasiswa',
                'status' => 'Aktif',
            ]);

            Mahasiswa::create([
                'user_id' => $user->id,
                'nim' => $validated['nim'],
                'jenis_kelamin' => $validated['gender'],
                'wali' => $validated['parent'] ?? null,
                'telepon_wali' => $validated['phone'] ?? null,
                'kelas' => $validated['class'],
                'status' => 'Aktif',
            ]);
        });

        return redirect()
            ->route('cms.mahasiswa')
            ->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    public function tambahmahasiswa(Request $request): View
    {
        return view('cms.mahasiswa.create', [
            'pilihanKelas' => $this->pilihanKelas(),
        ]);
    }

    public function edit(Mahasiswa $mahasiswa): View
    {
        $mahasiswa->load('user:id,name,email,status');

        return view('cms.mahasiswa.edit', [
            'mahasiswa' => $mahasiswa,
            'pilihanKelas' => $this->pilihanKelas(),
        ]);
    }

    public function update(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => [
                'required',
                'digits:8',
                Rule::unique('mahasiswa', 'nim')->ignore($mahasiswa->id, 'id'),
            ],
            'class' => ['required', 'exists:kelas,nama_kelas'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'parent' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(self::STATUS_TERSEDIA)],
        ], [
            'class.exists' => 'Kelas tersebut belum terdaftar di halaman Classes.',
        ]);

        DB::transaction(function () use ($validated, $mahasiswa) {
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

        return redirect()
            ->route('cms.mahasiswa')
            ->with('success', 'Mahasiswa berhasil diperbarui.');
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        DB::transaction(function () use ($mahasiswa) {
            $user = $mahasiswa->user;
            $mahasiswa->delete();
            $user?->delete();
        });

        return redirect()
            ->route('cms.mahasiswa')
            ->with('success', 'Mahasiswa berhasil dihapus.');
    }
}
