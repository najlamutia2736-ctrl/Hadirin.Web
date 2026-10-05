<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * Route `POST /login` memakai middleware `guest`. Tanpa arah yang
         * jelas, `RedirectIfAuthenticated` mencari route bernama `dashboard`
         * lalu `home`, dan karena route dashboard di sini bernama
         * `cms.dashboard`, orang yang sudah login lalu membuka `/login`
         * lagi akan mendarat di halaman awal, bukan beranda.
         *
         * Dipakai path literal, bukan `route()`, karena route belum dimuat
         * pada fase boot ini. `guests` sengaja tidak diubah supaya tamu
         * yang ditolak middleware `auth` tetap diarahkan ke halaman login.
         */
        $middleware->redirectUsersTo('/beranda');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
