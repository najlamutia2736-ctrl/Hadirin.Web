<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuruController extends Controller
{
    /**
     * Validasi mata pelajaran guru.
     *
     * Mapel tidak lagi diisi bebas. Nilainya harus salah satu baris di tabel
     * `mata_pelajaran` supaya nama dan kode mapel yang muncul di dashboard
     * admin sama dengan yang dipakai dashboard guru dan halaman Subjects.
     *
     * @return array<int, string>
     */
    private function aturanMataPelajaran(): array
    {
        return ['nullable', 'integer', 'exists:mata_pelajaran,id'];
    }

    /**
     * Daftar mata pelajaran untuk form guru dan filter di halaman index.
     *
     * Dipakai bersama oleh `index`, `tambahguru`, dan `edit` supaya pilihan
     * mapel selalu sama dengan data di halaman Subjects.
     *
     * @return Collection<int, MataPelajaran>
     */
    private function daftarMataPelajaran(): Collection
    {
        return MataPelajaran::query()
            ->orderBy('nama_mata_pelajaran')
            ->get(['id', 'kode_mata_pelajaran', 'nama_mata_pelajaran']);
    }

    public function index(Request $request): View
    {
        $teachers = Guru::query()
            ->with('user:id,name,status')
            ->with('mataPelajaran:id,kode_mata_pelajaran,nama_mata_pelajaran')
            ->with('kelas:nama_kelas')
            ->with('kelasDiampu:nama_kelas')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('nip', 'like', $keyword)
                        ->orWhere('mata_pelajaran', 'like', $keyword)
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', $keyword));
                });
            })
            ->when($request->filled('subject'), fn ($query) => $query->where('mata_pelajaran_id', $request->integer('subject')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('cms.teachers.index', [
            'teachers' => $teachers,
            'mataPelajaran' => $this->daftarMataPelajaran(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:30', Rule::unique('gurus', 'nip')],
            'subject' => $this->aturanMataPelajaran(),
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(['Aktif', 'Cuti', 'Nonaktif'])],
        ]);

        $normalizedNip = preg_replace('/\s+/', '', $validated['nip']);

        DB::transaction(function () use ($validated, $normalizedNip) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $normalizedNip.'@guru.sekolah.sch.id',
                'password' => $normalizedNip,
                'role' => 'Guru',
                'status' => $validated['status'] === 'Nonaktif' ? 'Nonaktif' : 'Aktif',
            ]);

            Guru::create([
                'user_id' => $user->id,
                'nip' => $validated['nip'],
                'mata_pelajaran_id' => $validated['subject'] ?? null,
                'telepon' => $validated['phone'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return redirect()
            ->route('cms.teachers')
            ->with('success', 'Guru berhasil ditambahkan.');
    }

    public function tambahguru(): View
    {
        return view('cms.teachers.create', [
            'mataPelajaran' => $this->daftarMataPelajaran(),
        ]);
    }

    public function edit(Guru $guru): View
    {
        $guru->load('mataPelajaran');

        return view('cms.teachers.edit', [
            'guru' => $guru,
            'mataPelajaran' => $this->daftarMataPelajaran(),
        ]);
    }

    public function update(Request $request, Guru $guru): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => [
                'required',
                'string',
                'max:30',
                Rule::unique('gurus', 'nip')->ignore($guru->id, 'id'),
            ],
            'subject' => $this->aturanMataPelajaran(),
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(['Aktif', 'Cuti', 'Nonaktif'])],
        ]);

        $normalizedNip = preg_replace('/\s+/', '', $validated['nip']);

        DB::transaction(function () use ($validated, $normalizedNip, $guru) {
            $guru->update([
                'nip' => $validated['nip'],
                'mata_pelajaran_id' => $validated['subject'] ?? null,
                'telepon' => $validated['phone'] ?? null,
                'status' => $validated['status'],
            ]);

            $guru->user->update([
                'name' => $validated['name'],
                'email' => $normalizedNip.'@guru.sekolah.sch.id',
                'status' => $validated['status'] === 'Nonaktif' ? 'Nonaktif' : 'Aktif',
            ]);
        });

        return redirect()
            ->route('cms.teachers')
            ->with('success', 'Guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru): RedirectResponse
    {
        DB::transaction(function () use ($guru) {
            $user = $guru->user;
            $guru->delete();
            $user?->delete();
        });

        return redirect()
            ->route('cms.teachers')
            ->with('success', 'Guru berhasil dihapus.');
    }
}
