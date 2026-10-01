<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Simpan pengguna baru dari modal "Tambah Pengguna".
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(self::PERAN_TERSEDIA)],
            'password' => ['required', 'string', 'min:8'],
        ]);

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

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id, 'id'),
            ],
            'role' => ['required', Rule::in(self::PERAN_TERSEDIA)],
            'status' => ['sometimes', 'string', Rule::in(self::STATUS_TERSEDIA)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

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
