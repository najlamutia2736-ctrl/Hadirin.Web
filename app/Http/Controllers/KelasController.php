<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\MataKuliah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KelasController extends Controller
{
    /**
     * Validasi dosen pengampu kelas.
     *
     * Dipakai bersama oleh form tambah dan ubah kelas. Daftar ini mengisi
     * tabel penghubung `dosen_kelas`, yang dibaca `Dosen::kelasDiampu()` untuk
     * membatasi kelas mana saja yang tampil di dashboard dosen.
     *
     * @return array<string, array<int, string>>
     */
    private function aturanDosenPengampu(): array
    {
        return [
            'dosens' => ['nullable', 'array'],
            'dosens.*' => ['integer', 'exists:dosens,id'],
        ];
    }

    /**
     * Bersihkan daftar id dosen pengampu sebelum divalidasi.
     *
     * Opsi "Belum ada pengampu" di form mengirim nilai kosong, jadi harus
     * dibuang lebih dulu agar tidak gagal aturan `integer`/`exists`.
     *
     * @return array<int, string>
     */
    private function bersihkanDosenPengampu(?array $dosens): array
    {
        return array_values(array_filter(
            $dosens ?? [],
            fn ($id) => $id !== '' && $id !== null,
        ));
    }

    public function index(Request $request): View
    {
        $kelas = Kelas::query()
            ->with('pembimbingAkademik.user')
            ->with('mataKuliah:id,kode_mata_kuliah,nama_mata_kuliah')
            ->with('dosen.user:id,name')
            ->withCount('mahasiswa')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('nama_kelas', 'like', $keyword)
                        ->orWhere('tingkat', 'like', $keyword)
                        ->orWhereHas('pembimbingAkademik.user', fn ($teacherQuery) => $teacherQuery->where('name', 'like', $keyword));
                });
            })
            ->when($request->filled('level'), fn ($query) => $query->where('tingkat', $request->string('level')))
            ->when($request->filled('mata_kuliah'), fn ($query) => $query->whereHas('mataKuliah', fn ($inner) => $inner->where('kode_mata_kuliah', $request->string('mata_kuliah'))))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $dosens = Dosen::query()
            ->with('user:id,name')
            ->orderBy('id')
            ->get();

        return view('cms.kelas.index', [
            'kelas' => $kelas,
            'dosens' => $dosens,
            'mataKuliah' => $this->daftarMataKuliah(),
        ]);
    }

    /**
     * Daftar mata kuliah untuk form kelas dan filter di halaman index.
     *
     * Diambil dalam satu query supaya form tambah, form ubah, dan filter
     * memakai sumber yang sama.
     *
     * @return Collection<int, MataKuliah>
     */
    protected function daftarMataKuliah(): Collection
    {
        return MataKuliah::query()
            ->orderBy('nama_mata_kuliah')
            ->get(['id', 'kode_mata_kuliah', 'nama_mata_kuliah']);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['dosens' => $this->bersihkanDosenPengampu($request->input('dosens'))]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('kelas', 'nama_kelas')],
            'level' => ['required', Rule::in(Kelas::TINGKAT_TERSEDIA)],
            'mata_kuliah' => ['nullable', 'integer', 'exists:mata_kuliah,id'],
            'homeroom' => ['nullable', 'integer', 'exists:dosens,id'],
            'room' => ['nullable', 'string', 'max:50'],
            'tahun_akademik' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            ...$this->aturanDosenPengampu(),
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => $validated['name'],
            'tingkat' => $validated['level'],
            'mata_kuliah_id' => $validated['mata_kuliah'] ?? null,
            'pembimbing_akademik_id' => $validated['homeroom'] ?? null,
            'ruang' => $validated['room'] ?? null,
            'tahun_akademik' => $validated['tahun_akademik'] ?? now()->year,
            'status' => 'Aktif',
        ]);

        $kelas->dosen()->sync($validated['dosens'] ?? []);

        return redirect()
            ->route('cms.kelas')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function tambahkelas(): View
    {
        $dosens = Dosen::query()
            ->with('user:id,name')
            ->orderBy('id')
            ->get();

        return view('cms.kelas.create', [
            'dosens' => $dosens,
            'mataKuliah' => $this->daftarMataKuliah(),
        ]);
    }

    public function edit(Kelas $kelas): View
    {
        $kelas->load('dosen');

        $dosens = Dosen::query()
            ->with('user:id,name')
            ->orderBy('id')
            ->get();

        return view('cms.kelas.edit', [
            'kelas' => $kelas,
            'dosens' => $dosens,
            'mataKuliah' => $this->daftarMataKuliah(),
        ]);
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $request->merge(['dosens' => $this->bersihkanDosenPengampu($request->input('dosens'))]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                // `ignore` tanpa kolom kedua akan memakai kolom yang sedang
                // divalidasi (`nama_kelas`), bukan primary key.
                Rule::unique('kelas', 'nama_kelas')->ignore($kelas->id, 'id'),
            ],
            'level' => ['required', Rule::in(Kelas::TINGKAT_TERSEDIA)],
            'mata_kuliah' => ['nullable', 'integer', 'exists:mata_kuliah,id'],
            'homeroom' => ['nullable', 'integer', 'exists:dosens,id'],
            'room' => ['nullable', 'string', 'max:50'],
            'tahun_akademik' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            'status' => ['sometimes', Rule::in(['Aktif', 'Arsip'])],
            ...$this->aturanDosenPengampu(),
        ]);

        $kelas->update([
            'nama_kelas' => $validated['name'],
            'tingkat' => $validated['level'],
            'mata_kuliah_id' => $validated['mata_kuliah'] ?? null,
            'pembimbing_akademik_id' => $validated['homeroom'] ?? null,
            'ruang' => $validated['room'] ?? null,
            'tahun_akademik' => $validated['tahun_akademik'] ?? $kelas->tahun_akademik,
            'status' => $validated['status'] ?? $kelas->status,
        ]);

        $kelas->dosen()->sync($validated['dosens'] ?? []);

        return redirect()
            ->route('cms.kelas')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        $kelas->delete();

        return redirect()
            ->route('cms.kelas')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
