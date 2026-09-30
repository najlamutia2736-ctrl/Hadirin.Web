<?php

namespace App\Providers;

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
        $this->composeSidebar();
    }

    /**
     * Isi `components.sidebar` dengan menu sesuai area route yang sedang dibuka.
     *
     * Sidebar jadi generik: halaman CMS maupun Guru cukup memakai layout yang
     * sama, menunya ditentukan dari prefix nama route (lihat config/menu.php).
     */
    protected function composeSidebar(): void
    {
        View::composer('components.sidebar', function ($view): void {
            $view->with([
                'menuGroups' => config('menu.'.$this->resolveMenuArea(), []),
                'menuUser' => config('menu.user.'.$this->resolveMenuArea(), config('menu.user.cms')),
            ]);
        });
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
