<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DosenController extends Controller
{
    /**
     * Validasi mata kuliah dosen.
     *
     * Mata Kuliah tidak lagi diisi bebas. Nilainya harus salah satu baris di tabel
     * `mata_kuliah` supaya nama dan kode mata kuliah yang muncul di dashboard
     * admin sama dengan yang dipakai dashboard dosen dan halaman Mata Kuliah.
     *
     * @return array<int, string>
     */
    private function aturanMataKuliah(): array
    {
        return ['nullable', 'integer', 'exists:mata_kuliah,id'];
    }

    /**
     * Daftar mata kuliah untuk form dosen dan filter di halaman index.
     *
     * Dipakai bersama oleh `index`, `tambahdosen`, dan `edit` supaya pilihan
     * mata kuliah selalu sama dengan data di halaman Mata Kuliah.
     *
     * @return Collection<int, MataKuliah>
     */
    private function daftarMataKuliah(): Collection
    {
        return MataKuliah::query()
            ->orderBy('nama_mata_kuliah')
            ->get(['id', 'kode_mata_kuliah', 'nama_mata_kuliah']);
    }

    public function index(Request $request): View
    {
        $dosens = Dosen::query()
            ->with('user:id,name,status')
            ->with('mataKuliah:id,kode_mata_kuliah,nama_mata_kuliah')
            ->with('kelas:nama_kelas')
            ->with('kelasDiampu:nama_kelas')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('nidn', 'like', $keyword)
                        ->orWhere('mata_kuliah', 'like', $keyword)
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', $keyword));
                });
            })
            ->when($request->filled('subject'), fn ($query) => $query->where('mata_kuliah_id', $request->integer('subject')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('cms.dosens.index', [
            'dosens' => $dosens,
            'mataKuliah' => $this->daftarMataKuliah(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nidn' => ['required', 'string', 'max:30', Rule::unique('dosens', 'nidn')],
            'subject' => $this->aturanMataKuliah(),
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(['Aktif', 'Cuti', 'Nonaktif'])],
        ]);

        $normalizedNip = preg_replace('/\s+/', '', $validated['nidn']);

        DB::transaction(function () use ($validated, $normalizedNip) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $normalizedNip.'@dosen.kampus.ac.id',
                'password' => $normalizedNip,
                'role' => 'Dosen',
                'status' => $validated['status'] === 'Nonaktif' ? 'Nonaktif' : 'Aktif',
            ]);

            Dosen::create([
                'user_id' => $user->id,
                'nidn' => $validated['nidn'],
                'mata_kuliah_id' => $validated['subject'] ?? null,
                'telepon' => $validated['phone'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return redirect()
            ->route('cms.dosens')
            ->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function tambahdosen(): View
    {
        return view('cms.dosens.create', [
            'mataKuliah' => $this->daftarMataKuliah(),
        ]);
    }

    public function edit(Dosen $dosen): View
    {
        $dosen->load('mataKuliah');

        return view('cms.dosens.edit', [
            'dosen' => $dosen,
            'mataKuliah' => $this->daftarMataKuliah(),
        ]);
    }

    public function update(Request $request, Dosen $dosen): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nidn' => [
                'required',
                'string',
                'max:30',
                Rule::unique('dosens', 'nidn')->ignore($dosen->id, 'id'),
            ],
            'subject' => $this->aturanMataKuliah(),
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(['Aktif', 'Cuti', 'Nonaktif'])],
        ]);

        $normalizedNip = preg_replace('/\s+/', '', $validated['nidn']);

        DB::transaction(function () use ($validated, $normalizedNip, $dosen) {
            $dosen->update([
                'nidn' => $validated['nidn'],
                'mata_kuliah_id' => $validated['subject'] ?? null,
                'telepon' => $validated['phone'] ?? null,
                'status' => $validated['status'],
            ]);

            $dosen->user->update([
                'name' => $validated['name'],
                'email' => $normalizedNip.'@dosen.kampus.ac.id',
                'status' => $validated['status'] === 'Nonaktif' ? 'Nonaktif' : 'Aktif',
            ]);
        });

        return redirect()
            ->route('cms.dosens')
            ->with('success', 'Dosen berhasil diperbarui.');
    }

    public function destroy(Dosen $dosen): RedirectResponse
    {
        DB::transaction(function () use ($dosen) {
            $user = $dosen->user;
            $dosen->delete();
            $user?->delete();
        });

        return redirect()
            ->route('cms.dosens')
            ->with('success', 'Dosen berhasil dihapus.');
    }
}
