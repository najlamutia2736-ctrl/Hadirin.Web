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
     * Daftar pengguna (halaman Manajemen Pengguna).
     */
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('siswa:id,user_id,jenis_kelamin')
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($inner) use ($request) {
                    $inner->where('name', 'like', '%'.$request->string('q').'%')
                        ->orWhere('email', 'like', '%'.$request->string('q').'%');
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('cms.users.index', [
            'users' => $users,
        ]);
    }

    /**
     * Simpan pengguna baru dari modal "Tambah Pengguna".
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:Admin,Guru,Siswa,Operator'],
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
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => ['required', Rule::in(['Admin', 'Guru', 'Siswa', 'Operator'])],
            'status' => ['sometimes', 'string', Rule::in(['Aktif', 'Nonaktif'])],
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
