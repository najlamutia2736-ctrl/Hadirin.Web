<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Halaman login.
     *
     * Namanya `login` sudah dipakai route closure di routes/web.php, jadi
     * method ini hanya dipakai sebagai fallback bila route itu dihapus.
     */
    public function create(): View
    {
        return view('login');
    }

    /**
     * Proses login.
     *
     * Akun dibuat dari sisi admin (dashboard CMS) memakai email dan NIP/NISN
     * sebagai password, jadi tidak ada form registrasi di sini. Setelah
     * berhasil, pengguna diarahkan ke dashboard sesuai role-nya.
     */
    /**
     * Halaman awal (route `home`).
     *
     * Halaman ini adalah pintu masuk, bukan beranda pengguna. Karena itu sesi
     * yang masih hidup diakhiri lebih dulu, sehingga pengunjung apa pun, baik
     * sudah masuk atau belum, melihat halaman yang sama persis: hanya tombol
     * "Log In".
     *
     * Yang diakhiri hanya sesinya. Semua data yang sudah disimpan (siswa, guru,
     * kelas, jadwal, absensi) tidak tersentuh sama sekali.
     */
    public function awal(Request $request): View
    {
        if ($request->user() !== null) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return view('halaman-awal');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        // Pesan yang sama untuk email tidak ditemukan, password salah, dan
        // akun nonaktif, supaya halaman login tidak membocorkan email mana
        // yang terdaftar.
        $gagal = $user === null
            || $user->status !== 'Aktif'
            || ! Hash::check($credentials['password'], $user->password);

        if ($gagal) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak cocok, atau akun sedang tidak aktif.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended($this->berandaUntuk($user));
    }

    /**
     * Akhiri sesi.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Dashboard awal tiap role.
     *
     * Guru diarahkan ke dashboard guru yang datanya sudah berasal dari
     * database, admin ke dashboard CMS, dan siswa ke beranda. Operator ikut ke
     * dashboard CMS karena perannya juga mengelola data sekolah.
     */
    protected function berandaUntuk(?User $user): string
    {
        return match ($user?->role) {
            'Admin', 'Operator' => route('cms.dashboard'),
            'Guru' => route('guru.dashboard'),
            'Siswa' => route('beranda'),
            default => route('home'),
        };
    }
}
