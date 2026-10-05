<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KelasController extends Controller
{
    /**
     * Validasi guru pengampu kelas.
     *
     * Dipakai bersama oleh form tambah dan ubah kelas. Daftar ini mengisi
     * tabel penghubung `guru_kelas`, yang dibaca `Guru::kelasDiampu()` untuk
     * membatasi kelas mana saja yang tampil di dashboard guru.
     *
     * @return array<string, array<int, string>>
     */
    private function aturanGuruPengampu(): array
    {
        return [
            'teachers' => ['nullable', 'array'],
            'teachers.*' => ['integer', 'exists:gurus,id'],
        ];
    }

    public function index(Request $request): View
    {
        $classes = Kelas::query()
            ->with('waliKelas.user')
            ->with('jurusan:id,kode_jurusan,nama_jurusan')
            ->with('guru.user:id,name')
            ->withCount('siswa')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('nama_kelas', 'like', $keyword)
                        ->orWhere('tingkat', 'like', $keyword)
                        ->orWhereHas('waliKelas.user', fn ($teacherQuery) => $teacherQuery->where('name', 'like', $keyword));
                });
            })
            ->when($request->filled('level'), fn ($query) => $query->where('tingkat', $request->string('level')))
            ->when($request->filled('jurusan'), fn ($query) => $query->whereHas('jurusan', fn ($inner) => $inner->where('kode_jurusan', $request->string('jurusan'))))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $gurus = Guru::query()
            ->with('user:id,name')
            ->orderBy('id')
            ->get();

        return view('cms.classes.index', [
            'classes' => $classes,
            'gurus' => $gurus,
            'jurusan' => $this->daftarJurusan(),
        ]);
    }

    /**
     * Daftar jurusan untuk form kelas dan filter di halaman index.
     *
     * Diambil dalam satu query supaya form tambah, form ubah, dan filter
     * memakai sumber yang sama.
     *
     * @return Collection<int, Jurusan>
     */
    protected function daftarJurusan(): Collection
    {
        return Jurusan::query()
            ->orderBy('nama_jurusan')
            ->get(['id', 'kode_jurusan', 'nama_jurusan']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('kelas', 'nama_kelas')],
            'level' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'jurusan' => ['nullable', 'integer', 'exists:jurusan,id'],
            'homeroom' => ['nullable', 'integer', 'exists:gurus,id'],
            'room' => ['nullable', 'string', 'max:50'],
            'tahun_ajaran' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            ...$this->aturanGuruPengampu(),
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => $validated['name'],
            'tingkat' => $validated['level'],
            'jurusan_id' => $validated['jurusan'] ?? null,
            'wali_kelas_id' => $validated['homeroom'] ?? null,
            'ruang' => $validated['room'] ?? null,
            'tahun_ajaran' => $validated['tahun_ajaran'] ?? now()->year,
            'status' => 'Aktif',
        ]);

        $kelas->guru()->sync($validated['teachers'] ?? []);

        return redirect()
            ->route('cms.classes')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function tambahkelas(): View
    {
        $gurus = Guru::query()
            ->with('user:id,name')
            ->orderBy('id')
            ->get();

        return view('cms.classes.create', [
            'gurus' => $gurus,
            'jurusan' => $this->daftarJurusan(),
        ]);
    }

    public function edit(Kelas $kelas): View
    {
        $kelas->load('guru');

        $gurus = Guru::query()
            ->with('user:id,name')
            ->orderBy('id')
            ->get();

        return view('cms.classes.edit', [
            'kelas' => $kelas,
            'gurus' => $gurus,
            'jurusan' => $this->daftarJurusan(),
        ]);
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                // `ignore` tanpa kolom kedua akan memakai kolom yang sedang
                // divalidasi (`nama_kelas`), bukan primary key.
                Rule::unique('kelas', 'nama_kelas')->ignore($kelas->id, 'id'),
            ],
            'level' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'jurusan' => ['nullable', 'integer', 'exists:jurusan,id'],
            'homeroom' => ['nullable', 'integer', 'exists:gurus,id'],
            'room' => ['nullable', 'string', 'max:50'],
            'tahun_ajaran' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            'status' => ['sometimes', Rule::in(['Aktif', 'Arsip'])],
            ...$this->aturanGuruPengampu(),
        ]);

        $kelas->update([
            'nama_kelas' => $validated['name'],
            'tingkat' => $validated['level'],
            'jurusan_id' => $validated['jurusan'] ?? null,
            'wali_kelas_id' => $validated['homeroom'] ?? null,
            'ruang' => $validated['room'] ?? null,
            'tahun_ajaran' => $validated['tahun_ajaran'] ?? $kelas->tahun_ajaran,
            'status' => $validated['status'] ?? $kelas->status,
        ]);

        $kelas->guru()->sync($validated['teachers'] ?? []);

        return redirect()
            ->route('cms.classes')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        $kelas->delete();

        return redirect()
            ->route('cms.classes')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
