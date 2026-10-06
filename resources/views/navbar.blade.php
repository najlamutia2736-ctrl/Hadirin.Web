<!-- resources/views/navbar.blade.php -->
@php
    // Menu "Absen Siswa" hanya untuk akun yang punya profil di tabel `siswas`.
    // Pemeriksaannya sama dengan yang dipakai AbsensiSiswaController, supaya
    // menu ini tidak pernah mengarah ke halaman yang akan menolak user.
    $bisaAbsen = auth()->user()?->siswa !== null;

    // Halaman CMS dan Guru sudah dilindungi middleware `role`, jadi link-nya
    // hanya ditampilkan ke akun yang benar-benar boleh membukanya. Tanpa ini,
    // tamu akan melihat link yang setelah diklik hanya memantulkan ke halaman
    // login.
    $bukaCms = in_array(auth()->user()?->role, ['Admin', 'Operator'], true);
    $bukaGuru = auth()->user()?->role === 'Guru';
@endphp
<header class="w-full bg-white/80 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center justify-between h-16 md:h-20">
            <!-- Brand / Logo -->
            <div class="flex items-center gap-2">
                {{-- Logo jadi jalan ke beranda, karena link "Beranda" di navbar
                     sengaja tidak ditampilkan. --}}
                <a href="{{ route('beranda') }}" class="text-2xl font-bold text-indigo-700 tracking-tight">
                    Hadirin.<span class="text-slate-700">web</span>
                </a>
                <span class="hidden sm:inline-block text-[10px] font-medium bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full">beta</span>
            </div>

            <!-- Menu Desktop -->
            <div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600">
                @if ($bisaAbsen)
                    <a href="{{ route('absensi.index') }}" class="hover:text-indigo-600 transition {{ request()->routeIs('absensi.*') ? 'text-indigo-600 font-semibold' : '' }}">Absen Siswa</a>
                @endif
                @if ($bukaGuru)
                    <a href="{{ route('guru.dashboard') }}" class="hover:text-indigo-600 transition {{ request()->routeIs('guru.dashboard') ? 'text-indigo-600 font-semibold' : '' }}">Dashboard Guru</a>
                @endif
                @if ($bukaCms)
                    <a href="{{ route('cms.dashboard') }}" class="hover:text-indigo-600 transition {{ request()->routeIs('cms.dashboard') ? 'text-indigo-600 font-semibold' : '' }}">Dashboard Admin</a>
                    <a href="{{ route('cms.rekap') }}" class="hover:text-indigo-600 transition {{ request()->routeIs('cms.rekap') ? 'text-indigo-600 font-semibold' : '' }}">Rekap</a>
                @endif
            </div>

            <!-- Tombol Login / User -->
            <div class="hidden md:block">
                @auth
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-600 flex items-center gap-2">
                            <i class="fas fa-user-circle text-indigo-600 text-lg"></i>
                            <span>{{ Auth::user()->name }}</span>
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 transition">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-indigo-600 text-white text-sm font-medium px-5 py-2.5 rounded-full shadow-md shadow-indigo-200 transition hover:bg-indigo-700">
                        <i class="fas fa-arrow-right-to-bracket text-xs"></i> Log In
                    </a>
                @endauth
            </div>

            <!-- Mobile Menu -->
            <div class="md:hidden flex items-center gap-3">
                @auth
                    <span class="text-sm font-medium text-slate-600">{{ Auth::user()->name }}</span>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-indigo-600 bg-indigo-50 px-4 py-2 rounded-full">Log In</a>
                @endauth
                <button class="text-slate-500 hover:text-indigo-600 transition" onclick="toggleMobileNav()">
                    <i class="fas fa-bars text-xl" id="mobileNavIcon"></i>
                </button>
            </div>
        </nav>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div id="mobileNavDropdown" class="hidden md:hidden border-t border-slate-200/60 bg-white/95 backdrop-blur-sm">
        <div class="px-4 py-3 space-y-1">
            @if ($bisaAbsen)
                <a href="{{ route('absensi.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50">
                    <i class="fas fa-user-graduate w-5"></i> Absen Siswa
                </a>
            @endif
            @if ($bukaGuru)
                <a href="{{ route('guru.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50">
                    <i class="fas fa-chalkboard-teacher w-5"></i> Dashboard Guru
                </a>
            @endif
            @if ($bukaCms)
                <a href="{{ route('cms.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50">
                    <i class="fas fa-user-shield w-5"></i> Dashboard Admin
                </a>
                <a href="{{ route('cms.rekap') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50">
                    <i class="fas fa-file-alt w-5"></i> Rekap
                </a>
            @endif
        </div>
    </div>
</header>

<script>
function toggleMobileNav() {
    const menu = document.getElementById('mobileNavDropdown');
    const icon = document.getElementById('mobileNavIcon');
    if (!menu) return;
    menu.classList.toggle('hidden');
    if (icon) {
        icon.classList.toggle('fa-bars');
        icon.classList.toggle('fa-times');
    }
}
</script>