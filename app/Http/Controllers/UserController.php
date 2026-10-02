<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Peran yang recognized oleh form dan filter pengguna.
     *
     * @var list<string>
     */
    private const PERAN_TERSEDIA = ['Admin', 'Guru', 'Siswa', 'Operator'];

    /**
     * Status keaktifan akun yang recognized oleh form dan filter.
     *
     * @var list<string>
     */
    private const STATUS_TERSEDIA = ['Aktif', 'Nonaktif'];

    /**
     * Daftar pengguna (halaman Manajemen Pengguna).
     *
     * Filter `role`, `status`, dan `q` dinormalisasi lebih dulu supaya nilai tak
     * dikenal tidak menghasilkan filter yang diam-diam selalu kosong.
     */
    public function index(Request $request): View
    {
        $daftarPeran = $this->daftarPeran();
        $daftarStatus = $this->daftarStatus();

        $peran = $this->filter($request->query('role'), $daftarPeran);
        $status = $this->filter($request->query('status'), $daftarStatus);

        $users = User::query()
            ->with('siswa:id,user_id,jenis_kelamin')
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($inner) use ($request) {
                    $inner->where('name', 'like', '%'.$request->string('q').'%')
                        ->orWhere('email', 'like', '%'.$request->string('q').'%');
                });
            })
            ->when($peran !== null, fn ($query) => $query->where('role', $peran))
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('cms.users.index', [
            'users' => $users,
            'filterPeran' => $peran,
            'filterStatus' => $status,
            'daftarPeran' => $daftarPeran,
            'daftarStatus' => $daftarStatus,
        ]);
    }

    /**
     * Peran hasil union antara peran yang dipakai pengguna dan daftar baku.
     *
     * @return list<string>
     */
    private function daftarPeran(): array
    {
        $dipakai = User::query()
            ->whereNotNull('role')
            ->distinct()
            ->pluck('role')
            ->map(fn ($peran) => (string) $peran)
            ->all();

        return array_values(array_unique([...self::PERAN_TERSEDIA, ...$dipakai]));
    }

    /**
     * Status hasil union antara status yang dipakai pengguna dan daftar baku.
     *
     * @return list<string>
     */
    private function daftarStatus(): array
    {
        $dipakai = User::query()
            ->whereNotNull('status')
            ->distinct()
            ->pluck('status')
            ->map(fn ($status) => (string) $status)
            ->all();

        return array_values(array_unique([...self::STATUS_TERSEDIA, ...$dipakai]));
    }

    /**
     * Filter dari request, null bila kosong atau tidak dikenal.
     *
     * Nilai yang tidak dikenal ditolak, bukan diteruskan ke query, supaya
     * `?role=Super Admin` tidak diam-diam selalu mengembalikan daftar kosong.
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
     * Rapikan input sebelum divalidasi.
     *
     * Spasi di pinggir dibuang supaya ` budi@sekolah.id ` tidak gagal
     * validasi dan tidak tersimpan dengan spasi, dan email dilowercase
     * supaya `Budi@Sekolah.ID` tidak tersimpan sebagai akun terpisah dari
     * `budi@sekolah.id`.
     */
    private function rapikanInput(Request $request): void
    {
        foreach (['name', 'email'] as $kolom) {
            $nilai = $request->input($kolom);

            // Kalau bukan string (mis. `email[]`), biarkan saja supaya
            // aturan validasi yang menolaknya, bukan kode ini.
            if (! is_string($nilai)) {
                continue;
            }

            $nilai = trim($nilai);

            $request->merge([
                $kolom => $kolom === 'email' ? Str::lower($nilai) : $nilai,
            ]);
        }
    }

    /**
     * Aturan validasi untuk form pengguna, dipakai bersama oleh tambah & ubah.
     *
     * Disatukan supaya keduanya tidak bisa lagi berbeda aturan. Sebelumnya
     * kolom `status` hanya divalidasi saat ubah, padahal kolom itu sudah ada
     * di tabel `users` dan form ubah pun sudah menampilkannya.
     *
     * @return array<string, list<mixed>>
     */
    private function aturan(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                $user === null
                    ? Rule::unique('users', 'email')
                    : Rule::unique('users', 'email')->ignore($user->getKey(), 'id'),
            ],
            'role' => ['required', Rule::in(self::PERAN_TERSEDIA)],
            'status' => ['required', Rule::in(self::STATUS_TERSEDIA)],
            // Di form ubah, password dikosongkan berarti tidak diubah.
            'password' => $user === null
                ? ['required', 'string', 'min:8', 'confirmed']
                : ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Simpan pengguna baru dari form "Tambah Pengguna".
     */
    public function store(Request $request): RedirectResponse
    {
        $this->rapikanInput($request);

        $validated = $request->validate($this->aturan());

        User::create($validated);

        return redirect()
            ->route('cms.users')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function tambahuser(Request $request): View
    {
        return view('cms.users.tambah');
    }

    public function edit(User $user): View
    {
        return view('cms.users.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Simpan perubahan pengguna.
     *
     * Email miliknya sendiri tetap boleh dipakai, jadi aturan `unique`
     * dikecualikan untuk baris yang sedang diedit.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->rapikanInput($request);

        $validated = $request->validate($this->aturan($user));

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('cms.users')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()
            ->route('cms.users')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
