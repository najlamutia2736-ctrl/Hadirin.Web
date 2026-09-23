<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Daftar siswa (halaman Manajemen Siswa).
     */
    public function index(Request $request): View
    {
        $students = Siswa::query()
            ->with('user:id,name,status')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('nisn', 'like', $keyword)
                        ->orWhereHas('user', fn ($q) => $q->where('name', 'like', $keyword));
                });
            })
            ->when($request->filled('kelas'), fn ($query) => $query->where('kelas', $request->string('kelas')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $stats = DB::table('siswas')
            ->selectRaw('count(*) as total')
            ->selectRaw("count(case when jenis_kelamin = 'L' then 1 end) as laki")
            ->selectRaw("count(case when jenis_kelamin = 'P' then 1 end) as perempuan")
            ->selectRaw('count(distinct kelas) as kelas')
            ->first();

        return view('cms.student', [
            'students' => $students,
            'stats' => $stats,
        ]);
    }

    /**
     * Simpan siswa baru dari modal "Tambah Siswa".
     *
     * Satu siswa selalu punya akun pengguna (foreign key `user_id` wajib),
     * jadi pembuatan User + Siswa dibungkus dalam satu transaksi.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'digits:8', 'unique:siswas,nisn'],
            'class' => ['required', 'in:X-A,X-B,XI-A,XI-B,XII-A'],
            'gender' => ['required', 'in:L,P'],
            'parent' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['nis'].'@siswa.sekolah.sch.id',
                'password' => $validated['nis'],
                'role' => 'Siswa',
                'status' => 'Aktif',
            ]);

            Siswa::create([
                'user_id' => $user->id,
                'nisn' => $validated['nis'],
                'jenis_kelamin' => $validated['gender'],
                'wali' => $validated['parent'] ?? null,
                'telepon_wali' => $validated['phone'] ?? null,
                'kelas' => $validated['class'],
                'status' => 'Aktif',
            ]);
        });

        return redirect()
            ->route('cms.students')
            ->with('success', 'Siswa berhasil ditambahkan.');
    }
}
