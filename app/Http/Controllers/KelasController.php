<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
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

    /**
     * Bersihkan daftar id guru pengampu sebelum divalidasi.
     *
     * Opsi "Belum ada pengampu" di form mengirim nilai kosong, jadi harus
     * dibuang lebih dulu agar tidak gagal aturan `integer`/`exists`.
     *
     * @return array<int, string>
     */
    private function bersihkanGuruPengampu(?array $teachers): array
    {
        return array_values(array_filter(
            $teachers ?? [],
            fn ($id) => $id !== '' && $id !== null,
        ));
    }

    public function index(Request $request): View
    {
        $classes = Kelas::query()
            ->with('waliKelas.user')
            ->with('mataPelajaran:id,kode_mata_pelajaran,nama_mata_pelajaran')
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
            ->when($request->filled('mata_pelajaran'), fn ($query) => $query->whereHas('mataPelajaran', fn ($inner) => $inner->where('kode_mata_pelajaran', $request->string('mata_pelajaran'))))
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
            'mataPelajaran' => $this->daftarMataPelajaran(),
        ]);
    }

    /**
     * Daftar mata pelajaran untuk form kelas dan filter di halaman index.
     *
     * Diambil dalam satu query supaya form tambah, form ubah, dan filter
     * memakai sumber yang sama.
     *
     * @return Collection<int, MataPelajaran>
     */
    protected function daftarMataPelajaran(): Collection
    {
        return MataPelajaran::query()
            ->orderBy('nama_mata_pelajaran')
            ->get(['id', 'kode_mata_pelajaran', 'nama_mata_pelajaran']);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['teachers' => $this->bersihkanGuruPengampu($request->input('teachers'))]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('kelas', 'nama_kelas')],
            'level' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'mata_pelajaran' => ['nullable', 'integer', 'exists:mata_pelajaran,id'],
            'homeroom' => ['nullable', 'integer', 'exists:gurus,id'],
            'room' => ['nullable', 'string', 'max:50'],
            'tahun_ajaran' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            ...$this->aturanGuruPengampu(),
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => $validated['name'],
            'tingkat' => $validated['level'],
            'mata_pelajaran_id' => $validated['mata_pelajaran'] ?? null,
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
            'mataPelajaran' => $this->daftarMataPelajaran(),
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
            'mataPelajaran' => $this->daftarMataPelajaran(),
        ]);
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $request->merge(['teachers' => $this->bersihkanGuruPengampu($request->input('teachers'))]);

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
            'mata_pelajaran' => ['nullable', 'integer', 'exists:mata_pelajaran,id'],
            'homeroom' => ['nullable', 'integer', 'exists:gurus,id'],
            'room' => ['nullable', 'string', 'max:50'],
            'tahun_ajaran' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            'status' => ['sometimes', Rule::in(['Aktif', 'Arsip'])],
            ...$this->aturanGuruPengampu(),
        ]);

        $kelas->update([
            'nama_kelas' => $validated['name'],
            'tingkat' => $validated['level'],
            'mata_pelajaran_id' => $validated['mata_pelajaran'] ?? null,
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
