<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MataKuliahController extends Controller
{
    /**
     * Halaman daftar mata kuliah.
     *
     * Setiap baris menampilkan jumlah kelas yang memakai mata kuliah tersebut,
     * supaya admin tahu mana yang masih dipakai sebelum menghapus.
     */
    public function index(Request $request): View
    {
        $mataKuliah = MataKuliah::query()
            ->withCount('kelas')
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
        return view('cms.mata-kuliah.create');
    }

    /**
     * Simpan mata kuliah baru.
     *
     * Kode dan nama keduanya harus unik, tapi pesan errornya dibedakan supaya
     * admin tahu field mana yang bentrok.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('mata_kuliah', 'kode_mata_kuliah')],
            'name' => ['required', 'string', 'max:100', Rule::unique('mata_kuliah', 'nama_mata_kuliah')],
        ], [
            'code.unique' => 'Kode mata kuliah sudah dipakai.',
            'name.unique' => 'Nama mata kuliah sudah dipakai.',
        ]);

        MataKuliah::create([
            'kode_mata_kuliah' => $this->rapikanKode($validated['code']),
            'nama_mata_kuliah' => $validated['name'],
        ]);

        return redirect()
            ->route('cms.mata-kuliah')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(MataKuliah $mataKuliah): View
    {
        return view('cms.mata-kuliah.edit', [
            'mataKuliah' => $mataKuliah,
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
        ], [
            'code.unique' => 'Kode mata kuliah sudah dipakai.',
            'name.unique' => 'Nama mata kuliah sudah dipakai.',
        ]);

        $mataKuliah->update([
            'kode_mata_kuliah' => $this->rapikanKode($validated['code']),
            'nama_mata_kuliah' => $validated['name'],
        ]);

        return redirect()
            ->route('cms.mata-kuliah')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    /**
     * Hapus mata kuliah.
     *
     * Foreign key `kelas.mata_kuliah_id` memakai `nullOnDelete`, jadi kelas
     * yang memakai mata kuliah ini tidak ikut terhapus; kolomnya saja yang
     * jadi kosong.
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
}
