<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Batasi halaman berdasarkan role akun yang sedang login.
 *
 * Selalu dipakai setelah middleware `auth`, jadi user yang belum login sudah
 * ditolak lebih dulu dan middleware ini hanya mengurus soal role-nya. Tanpa
 * `auth` di_chain sebelumnya, `$request->user()` bisa null dan seluruh request
 * ikut ditolak dengan 403.
 */
class EnsureUserHasRole
{
    /**
     * Jalankan request bila role user termasuk daftar yang diizinkan.
     *
     * @param  Closure(Request): Response  $next
     * @param  string  ...$peran  Role yang boleh mengakses, mis. `Admin,Operator`.
     */
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();

        abort_if(
            $user === null || ! in_array($user->role, $peran, true),
            403,
            'Akun ini tidak punya akses ke halaman tersebut.'
        );

        return $next($request);
    }
}
