<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->composeLayoutComponents();
    }

    /**
     * Isi `components.sidebar` dan `components.header` sesuai area route yang dibuka.
     *
     * Komponen jadi generik: halaman CMS maupun Guru cukup memakai layout yang
     * sama, menunya ditentukan dari prefix nama route (lihat config/menu.php),
     * dan identitas di sidebar/header diambil dari user yang sedang login.
     */
    protected function composeLayoutComponents(): void
    {
        View::composer(['components.sidebar', 'components.header'], function ($view): void {
            $area = $this->resolveMenuArea();

            $view->with([
                'menuGroups' => config('menu.'.$area, []),
                'menuUser' => $this->resolveMenuUser($area),
                'menuBrandSubtitle' => config('menu.brand.'.$area, config('menu.brand.cms')),
            ]);
        });
    }

    /**
     * Identitas pengguna di footer sidebar.
     *
     * Memakai user yang sedang login supaya nama di sidebar sama dengan akun
     * yang dipakai masuk. Kalau belum login, memakai nilai bawaan per area.
     *
     * @return array<string, string|null>
     */
    protected function resolveMenuUser(string $area): array
    {
        $bawaan = (array) config('menu.user.'.$area, config('menu.user.cms'));

        $user = Auth::user();

        if ($user === null) {
            return $bawaan;
        }

        return [
            'initial' => mb_strtoupper(mb_substr($user->name, 0, 1)),
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];
    }

    /**
     * Prefix route yang sedang aktif, mis. `guru.dashboard` -> `guru`.
     */
    protected function resolveMenuArea(): string
    {
        $routeName = request()->route()?->getName() ?? '';

        foreach ((array) config('menu.areas', []) as $area => $pattern) {
            if ($routeName !== '' && request()->routeIs($pattern)) {
                return $area;
            }
        }

        return 'cms';
    }
}
