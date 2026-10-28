<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\MataKuliah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MataKuliahController extends Controller
{
    /**
     * Halaman daftar mata kuliah.
     *
     * Setiap baris menampilkan dosen pengajar lewat relasi `dosens.mata_kuliah_id`
     * dan jumlah kelas yang memakai mata kuliah tersebut, supaya admin tahu siapa
     * pengajarnya dan mana yang masih dipakai sebelum mengubah atau menghapus.
     */
    public function index(Request $request): View
    {
        $mataKuliah = MataKuliah::query()
            ->withCount('kelas')
            ->with(['dosens.user:id,name'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = '%'.$request->string('q').'%';

                $query->where(function ($inner) use ($keyword) {
                    $inner->where('nama_mata_kuliah', 'like', $keyword)
                        ->orWhere('kode_mata_kuliah', 'like', $keyword);
                });
            })
            ->orderBy('nama_mata_kuliah')
            ->paginate(10)
            ->withQueryString();

        return view('cms.mata-kuliah.index', [
            'mataKuliah' => $mataKuliah,
        ]);
    }

    public function create(): View
    {
        return view('cms.mata-kuliah.create', [
            'dosens' => $this->daftarDosenPengajar(),
        ]);
    }

    /**
     * Simpan mata kuliah baru.
     *
     * Kode dan nama keduanya harus unik, tapi pesan errornya dibedakan supaya
     * admin tahu field mana yang bentrok. Dosen pengajar ikut dikaitkan ke
     * mata kuliah barunya lewat kolom `dosens.mata_kuliah_id`.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('mata_kuliah', 'kode_mata_kuliah')],
            'name' => ['required', 'string', 'max:100', Rule::unique('mata_kuliah', 'nama_mata_kuliah')],
            // Wajib hanya kalau ada dosen yang bisa dipilih, supaya instalasi
            // baru yang belum punya data dosen tetap bisa mengisi daftar mata kuliah.
            'dosen' => [Dosen::query()->exists() ? 'required' : 'nullable', Rule::exists('dosens', 'id')],
        ], [
            'code.unique' => 'Kode mata kuliah sudah dipakai.',
            'name.unique' => 'Nama mata kuliah sudah dipakai.',
            'dosen.required' => 'Pilih dosen pengajar mata kuliah ini.',
            'dosen.exists' => 'Dosen yang dipilih tidak ditemukan.',
        ]);

        $mataKuliah = MataKuliah::create([
            'kode_mata_kuliah' => $this->rapikanKode($validated['code']),
            'nama_mata_kuliah' => $validated['name'],
        ]);

        $this->tetapkanDosenPengajar($mataKuliah, $validated['dosen'] ?? null);

        return redirect()
            ->route('cms.mata-kuliah')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(MataKuliah $mataKuliah): View
    {
        return view('cms.mata-kuliah.edit', [
            'mataKuliah' => $mataKuliah->load('dosens'),
            'dosens' => $this->daftarDosenPengajar(),
        ]);
    }

    public function update(Request $request, MataKuliah $mataKuliah): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:10',
                // `ignore` tanpa kolom kedua memakai kolom yang sedang
                // divalidasi (`kode_mata_kuliah`), bukan primary key.
                Rule::unique('mata_kuliah', 'kode_mata_kuliah')->ignore($mataKuliah->id, 'id'),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('mata_kuliah', 'nama_mata_kuliah')->ignore($mataKuliah->id, 'id'),
            ],
            'dosen' => [Dosen::query()->exists() ? 'required' : 'nullable', Rule::exists('dosens', 'id')],
        ], [
            'code.unique' => 'Kode mata kuliah sudah dipakai.',
            'name.unique' => 'Nama mata kuliah sudah dipakai.',
            'dosen.required' => 'Pilih dosen pengajar mata kuliah ini.',
            'dosen.exists' => 'Dosen yang dipilih tidak ditemukan.',
        ]);

        $mataKuliah->update([
            'kode_mata_kuliah' => $this->rapikanKode($validated['code']),
            'nama_mata_kuliah' => $validated['name'],
        ]);

        $this->tetapkanDosenPengajar($mataKuliah, $validated['dosen'] ?? null);

        return redirect()
            ->route('cms.mata-kuliah')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    /**
     * Hapus mata kuliah.
     *
     * Foreign key `kelas.mata_kuliah_id` dan `dosens.mata_kuliah_id` sama-sama
     * memakai `nullOnDelete`, jadi kelas dan dosen yang memakai mata kuliah ini
     * tidak ikut terhapus; kolomnya saja yang jadi kosong.
     */
    public function destroy(MataKuliah $mataKuliah): RedirectResponse
    {
        $mataKuliah->delete();

        return redirect()
            ->route('cms.mata-kuliah')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    /**
     * Kode mata kuliah disimpan dalam huruf kapital tanpa spasi.
     *
     * Admin sering mengetik "rpl" atau "R P L" untuk kode yang sama, dan
     * keduanya harus dianggap satu kode, bukan tiga data berbeda.
     */
    protected function rapikanKode(string $kode): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($kode)) ?? trim($kode));
    }

    /**
     * Dosen yang bisa dipilih sebagai pengajar di form tambah & ubah.
     *
     * Dosen beserta akunnya (sumber nama) dan mata kuliahnya sekarang ikut
     * diambil, supaya label tiap pilihan bisa memberi tahu admin kalau memilih
     * dosen tersebut berarti memindahkannya dari mata kuliahnya yang sekarang.
     *
     * @return Collection<int, Dosen>
     */
    protected function daftarDosenPengajar(): Collection
    {
        return Dosen::query()
            ->with(['user:id,name', 'mataKuliah:id,nama_mata_kuliah'])
            ->get()
            ->sortBy(fn (Dosen $dosen) => $dosen->user?->name ?? '')
            ->values();
    }

    /**
     * Tetapkan dosen pengajar untuk satu mata kuliah.
     *
     * Form memperlakukan dosen pengajar sebagai penugasan tunggal: dosen yang
     * dipilih dipindahkan ke mata kuliah ini, dan dosen lain yang sebelumnya
     * menunjuk ke mata kuliah ini dilepas, supaya satu mata kuliah tidak punya
     * banyak dosen pengajar hanya karena form disimpan berkali-kali.
     */
    protected function tetapkanDosenPengajar(MataKuliah $mataKuliah, int|string|null $dosenId): void
    {
        if ($dosenId === null) {
            return;
        }

        $mataKuliah->dosens()->whereKeyNot($dosenId)->update(['mata_kuliah_id' => null]);

        Dosen::query()->find($dosenId)?->update([
            'mata_kuliah_id' => $mataKuliah->getKey(),
        ]);
    }
}
