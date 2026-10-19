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
     * Akun dibuat dari sisi admin (dashboard CMS) memakai email dan NIDN/NIM
     * sebagai password, jadi tidak ada form registrasi di sini. Setelah
     * berhasil, pengguna diarahkan ke dashboard sesuai role-nya.
     */
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
     * Halaman tujuan setelah login, tiap role.
     *
     * Dosen diarahkan ke dashboard dosen, admin dan operator ke dashboard CMS.
     * Mahasiswa langsung ke halaman absensi karena halaman itu memang tempat
     * kegiatannya, sama seperti admin yang langsung masuk ke dashboard CMS.
     * Dengan begitu halaman depan tidak muncul lagi setelah mahasiswa berhasil
     * masuk.
     *
     * Mahasiswa yang belum punya baris di tabel `mahasiswa` tetap dikirim ke halaman
     * depan. Halaman absensi menolak akun tanpa profil dengan 403, jadi
     * mengirimnya ke sana hanya akan menghasilkan halaman error.
     */
    protected function berandaUntuk(?User $user): string
    {
        return match ($user?->role) {
            'Admin', 'Operator' => route('cms.dashboard'),
            'Dosen' => route('dosen.dashboard'),
            'Mahasiswa' => $user->mahasiswa !== null ? route('absensi.index') : route('beranda'),
            default => route('beranda'),
        };
    }
}
