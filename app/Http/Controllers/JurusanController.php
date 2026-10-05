<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JurusanController extends Controller
{
    /**
     * Halaman daftar jurusan.
     *
     * Setiap baris menampilkan jumlah kelas yang memakai jurusan tersebut,
     * supaya admin tahu mana yang masih dipakai sebelum menghapus.
     */
    public function index(Request $request): View
    {
        $jurusan = Jurusan::query()
            ->withCount('kelas')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = '%'.$request->string('q').'%';

                $query->where(function ($inner) use ($keyword) {
                    $inner->where('nama_jurusan', 'like', $keyword)
                        ->orWhere('kode_jurusan', 'like', $keyword);
                });
            })
            ->orderBy('nama_jurusan')
            ->paginate(10)
            ->withQueryString();

        return view('cms.jurusan.index', [
            'jurusan' => $jurusan,
        ]);
    }

    public function create(): View
    {
        return view('cms.jurusan.create');
    }

    /**
     * Simpan jurusan baru.
     *
     * Kode dan nama keduanya harus unik, tapi pesan errornya dibedakan supaya
     * admin tahu field mana yang bentrok.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('jurusan', 'kode_jurusan')],
            'name' => ['required', 'string', 'max:100', Rule::unique('jurusan', 'nama_jurusan')],
        ], [
            'code.unique' => 'Kode jurusan sudah dipakai.',
            'name.unique' => 'Nama jurusan sudah dipakai.',
        ]);

        Jurusan::create([
            'kode_jurusan' => $this->rapikanKode($validated['code']),
            'nama_jurusan' => $validated['name'],
        ]);

        return redirect()
            ->route('cms.jurusan')
            ->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function edit(Jurusan $jurusan): View
    {
        return view('cms.jurusan.edit', [
            'jurusan' => $jurusan,
        ]);
    }

    public function update(Request $request, Jurusan $jurusan): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:10',
                // `ignore` tanpa kolom kedua memakai kolom yang sedang
                // divalidasi (`kode_jurusan`), bukan primary key.
                Rule::unique('jurusan', 'kode_jurusan')->ignore($jurusan->id, 'id'),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('jurusan', 'nama_jurusan')->ignore($jurusan->id, 'id'),
            ],
        ], [
            'code.unique' => 'Kode jurusan sudah dipakai.',
            'name.unique' => 'Nama jurusan sudah dipakai.',
        ]);

        $jurusan->update([
            'kode_jurusan' => $this->rapikanKode($validated['code']),
            'nama_jurusan' => $validated['name'],
        ]);

        return redirect()
            ->route('cms.jurusan')
            ->with('success', 'Jurusan berhasil diperbarui.');
    }

    /**
     * Hapus jurusan.
     *
     * Foreign key `kelas.jurusan_id` memakai `nullOnDelete`, jadi kelas yang
     * memakai jurusan ini tidak ikut terhapus; kolomnya saja yang jadi kosong.
     */
    public function destroy(Jurusan $jurusan): RedirectResponse
    {
        $jurusan->delete();

        return redirect()
            ->route('cms.jurusan')
            ->with('success', 'Jurusan berhasil dihapus.');
    }

    /**
     * Kode jurusan disimpan dalam huruf kapital tanpa spasi.
     *
     * Admin sering mengetik "rpl" atau "R P L" untuk kode yang sama, dan
     * keduanya harus dianggap satu kode, bukan tiga data berbeda.
     */
    protected function rapikanKode(string $kode): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($kode)) ?? trim($kode));
    }
}
