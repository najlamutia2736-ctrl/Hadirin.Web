<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JadwalController extends Controller
{
    /**
     * Daftar jadwal mengajar dengan pencarian dan filter.
     *
     * Filter `kelas`, `hari`, dan `status` dinormalkan lebih dulu supaya nilai
     * tak dikenal tidak menghasilkan daftar kosong tanpa penjelasan.
     */
    public function index(Request $request): View
    {
        $hari = $this->filter($request->query('hari'), Jadwal::HARI_TERSEDIA);
        $status = $this->filter($request->query('status'), Jadwal::STATUS_TERSEDIA);
        // Ketiganya sudah dinormalkan di atas, jadi query memakai nilai yang
        // sama persis dengan yang dipakai dropdown. Kalau filter memakai nilai
        // mentah dari request, `?kelas=999` akan diam-diam jadi tabel kosong.
        $kelas = $this->kelasValid($request->integer('kelas'));

        $jadwal = Jadwal::query()
            ->with('kelas')
            ->with('guru.user:id,name')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = '%'.$request->string('q').'%';

                $query->where(function ($inner) use ($keyword) {
                    $inner->where('mata_pelajaran', 'like', $keyword)
                        ->orWhere('ruang', 'like', $keyword)
                        ->orWhereHas('kelas', fn ($kelas) => $kelas->where('nama_kelas', 'like', $keyword))
                        ->orWhereHas('guru.user', fn ($user) => $user->where('name', 'like', $keyword));
                });
            })
            ->when($kelas !== null, fn ($query) => $query->where('kelas_id', $kelas))
            ->when($hari !== null, fn ($query) => $query->where('hari', $hari))
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            // Diurutkan per jam supaya jam pelajaran teratas adalah yang pagi.
            ->orderBy('jam_mulai')
            ->orderBy('kelas_id')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('cms.jadwal.index', [
            'jadwal' => $jadwal,
            'kelasList' => $this->kelasList(),
            'filterKelas' => $kelas,
            'filterHari' => $hari,
            'filterStatus' => $status,
            'daftarHari' => Jadwal::HARI_TERSEDIA,
            'daftarStatus' => Jadwal::STATUS_TERSEDIA,
        ]);
    }

    /**
     * Halaman form tambah jadwal.
     */
    public function create(): View
    {
        return view('cms.jadwal.create', [
            'kelasList' => $this->kelasList(),
            'guruList' => $this->guruList(),
            'daftarHari' => Jadwal::HARI_TERSEDIA,
            'daftarStatus' => Jadwal::STATUS_TERSEDIA,
        ]);
    }

    /**
     * Simpan jadwal baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->aturan());

        $this->cekBentrok($validated);

        Jadwal::create($validated);

        return redirect()
            ->route('cms.jadwal')
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    /**
     * Halaman form ubah jadwal.
     */
    public function edit(Jadwal $jadwal): View
    {
        return view('cms.jadwal.edit', [
            'jadwal' => $jadwal,
            'kelasList' => $this->kelasList(),
            'guruList' => $this->guruList(),
            'daftarHari' => Jadwal::HARI_TERSEDIA,
            'daftarStatus' => Jadwal::STATUS_TERSEDIA,
        ]);
    }

    /**
     * Simpan perubahan jadwal.
     */
    public function update(Request $request, Jadwal $jadwal): RedirectResponse
    {
        $validated = $request->validate($this->aturan());

        // Jadwal yang sedang diedit tidak boleh dianggap bentrok dengan dirinya
        // sendiri, jadi barisnya dikecualikan dari pengecekan.
        $this->cekBentrok($validated, $jadwal);

        $jadwal->update($validated);

        return redirect()
            ->route('cms.jadwal')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    /**
     * Hapus jadwal.
     */
    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        $jadwal->delete();

        return redirect()
            ->route('cms.jadwal')
            ->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * Aturan validasi untuk form jadwal, dipakai bersama oleh tambah & ubah.
     *
     * Disatukan supaya keduanya tidak bisa berbeda aturan.
     *
     * @return array<string, array<int, mixed>>
     */
    private function aturan(): array
    {
        return [
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'guru_id' => ['required', 'integer', 'exists:gurus,id'],
            'mata_pelajaran' => ['required', 'string', 'min:2', 'max:100'],
            'hari' => ['required', Rule::in(Jadwal::HARI_TERSEDIA)],
            'jam_mulai' => ['required', 'date_format:H:i'],
            // `after` menolak jadwal yang selesai sebelum atau tepat sama
            // dengan waktu mulainya, jadi durasinya selalu di atas nol.
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'ruang' => ['nullable', 'string', 'max:50'],
            'tahun_ajaran' => ['required', 'integer', 'min:2000', 'max:'.(now()->year + 2)],
            'status' => ['required', Rule::in(Jadwal::STATUS_TERSEDIA)],
        ];
    }

    /**
     * Tolak jadwal yang menabrak jadwal lain di kelas atau guru yang sama.
     *
     * Dua slot dianggap bertabrakan kalau jadwal baru mulai sebelum jadwal lama
     * selesai, dan selesai setelah jadwal lama mulai. Slot yang bersambung
     * Sendiri (mis. 08:30-09:00 sesudah 07:30-08:30) bukan tabrakan.
     *
     * Jadwal Nonaktif diabaikan supaya jadwal lama bisa dinonaktifkan dulu
     * sebelum memasukkan penggantinya.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    private function cekBentrok(array $data, ?Jadwal $dihapus = null): void
    {
        $sumber = [
            'kelas_id' => 'Kelas ini sudah punya jadwal lain pada rentang jam tersebut.',
            'guru_id' => 'Guru ini sudah mengajar kelas lain pada rentang jam tersebut.',
        ];

        foreach ($sumber as $kolom => $pesan) {
            $bentrok = Jadwal::query()
                ->where($kolom, $data[$kolom])
                ->where('hari', $data['hari'])
                ->where('tahun_ajaran', $data['tahun_ajaran'])
                ->where('status', 'Aktif')
                ->where('jam_mulai', '<', $data['jam_selesai'])
                ->where('jam_selesai', '>', $data['jam_mulai'])
                ->when(
                    $dihapus !== null,
                    fn ($query) => $query->where($dihapus->getKeyName(), '!=', $dihapus->getKey())
                )
                ->exists();

            if ($bentrok) {
                throw ValidationException::withMessages(['jam_mulai' => $pesan]);
            }
        }
    }

    /**
     * Kelas aktif untuk dropdown filter dan form.
     *
     * @return Collection<int, Kelas>
     */
    private function kelasList()
    {
        return Kelas::query()
            ->where('status', 'Aktif')
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();
    }

    /**
     * Guru aktif untuk dropdown form.
     *
     * @return Collection<int, Guru>
     */
    private function guruList()
    {
        return Guru::query()
            ->with('user:id,name')
            ->orderBy('id')
            ->get();
    }

    /**
     * Id kelas dari query string, atau null kalau tidak dikenal.
     */
    private function kelasValid(mixed $nilai): ?int
    {
        $id = filter_var($nilai, FILTER_VALIDATE_INT);

        if ($id === false || $id <= 0) {
            return null;
        }

        return $this->kelasList()->contains('id', $id) ? $id : null;
    }

    /**
     * Filter dari request, null bila kosong atau tidak dikenal.
     *
     * Nilai yang tidak dikenal ditolak, bukan diteruskan ke query, supaya
     * `?hari=Minggu` tidak diam-diam selalu mengembalikan daftar kosong.
     *
     * @param  list<string>  $pilihan
     */
    private function filter(mixed $nilai, array $pilihan): ?string
    {
        if (! is_string($nilai)) {
            return null;
        }

        $nilai = trim($nilai);

        return in_array($nilai, $pilihan, true) ? $nilai : null;
    }
}
