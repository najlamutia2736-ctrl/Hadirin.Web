<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MataPelajaranController extends Controller
{
    /**
     * Halaman daftar mata pelajaran.
     *
     * Setiap baris menampilkan jumlah kelas yang memakai mata pelajaran tersebut,
     * supaya admin tahu mana yang masih dipakai sebelum menghapus.
     */
    public function index(Request $request): View
    {
        $mataPelajaran = MataPelajaran::query()
            ->withCount('kelas')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = '%'.$request->string('q').'%';

                $query->where(function ($inner) use ($keyword) {
                    $inner->where('nama_mata_pelajaran', 'like', $keyword)
                        ->orWhere('kode_mata_pelajaran', 'like', $keyword);
                });
            })
            ->orderBy('nama_mata_pelajaran')
            ->paginate(10)
            ->withQueryString();

        return view('cms.mata-pelajaran.index', [
            'mataPelajaran' => $mataPelajaran,
        ]);
    }

    public function create(): View
    {
        return view('cms.mata-pelajaran.create');
    }

    /**
     * Simpan mata pelajaran baru.
     *
     * Kode dan nama keduanya harus unik, tapi pesan errornya dibedakan supaya
     * admin tahu field mana yang bentrok.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('mata_pelajaran', 'kode_mata_pelajaran')],
            'name' => ['required', 'string', 'max:100', Rule::unique('mata_pelajaran', 'nama_mata_pelajaran')],
        ], [
            'code.unique' => 'Kode mata pelajaran sudah dipakai.',
            'name.unique' => 'Nama mata pelajaran sudah dipakai.',
        ]);

        MataPelajaran::create([
            'kode_mata_pelajaran' => $this->rapikanKode($validated['code']),
            'nama_mata_pelajaran' => $validated['name'],
        ]);

        return redirect()
            ->route('cms.mata-pelajaran')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(MataPelajaran $mataPelajaran): View
    {
        return view('cms.mata-pelajaran.edit', [
            'mataPelajaran' => $mataPelajaran,
        ]);
    }

    public function update(Request $request, MataPelajaran $mataPelajaran): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:10',
                // `ignore` tanpa kolom kedua memakai kolom yang sedang
                // divalidasi (`kode_mata_pelajaran`), bukan primary key.
                Rule::unique('mata_pelajaran', 'kode_mata_pelajaran')->ignore($mataPelajaran->id, 'id'),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('mata_pelajaran', 'nama_mata_pelajaran')->ignore($mataPelajaran->id, 'id'),
            ],
        ], [
            'code.unique' => 'Kode mata pelajaran sudah dipakai.',
            'name.unique' => 'Nama mata pelajaran sudah dipakai.',
        ]);

        $mataPelajaran->update([
            'kode_mata_pelajaran' => $this->rapikanKode($validated['code']),
            'nama_mata_pelajaran' => $validated['name'],
        ]);

        return redirect()
            ->route('cms.mata-pelajaran')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    /**
     * Hapus mata pelajaran.
     *
     * Foreign key `kelas.mata_pelajaran_id` memakai `nullOnDelete`, jadi kelas
     * yang memakai mata pelajaran ini tidak ikut terhapus; kolomnya saja yang
     * jadi kosong.
     */
    public function destroy(MataPelajaran $mataPelajaran): RedirectResponse
    {
        $mataPelajaran->delete();

        return redirect()
            ->route('cms.mata-pelajaran')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    /**
     * Kode mata pelajaran disimpan dalam huruf kapital tanpa spasi.
     *
     * Admin sering mengetik "rpl" atau "R P L" untuk kode yang sama, dan
     * keduanya harus dianggap satu kode, bukan tiga data berbeda.
     */
    protected function rapikanKode(string $kode): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($kode)) ?? trim($kode));
    }
}
