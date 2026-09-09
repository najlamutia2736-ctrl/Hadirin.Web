<!-- resources/views/layouts/navbar.blade.php -->
<header class="w-full bg-white/80 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center justify-between h-16 md:h-20">
            <!-- Brand / Logo -->
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-2xl font-bold text-indigo-700 tracking-tight">
                    Hadirin.<span class="text-slate-700">web</span>
                </a>
                <span class="hidden sm:inline-block text-[10px] font-medium bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full">beta</span>
            </div>

           <!-- Menu Desktop -->
<div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600">
    <a href="{{ route('home') }}" class="hover:text-indigo-600 transition {{ request()->routeIs('home') ? 'text-indigo-600 font-semibold' : '' }}">Home</a>
    <a href="{{ route('beranda') }}" class="hover:text-indigo-600 transition {{ request()->routeIs('beranda') ? 'text-indigo-600 font-semibold' : '' }}">Beranda</a>
    <a href="#" class="hover:text-indigo-600 transition">Absen Siswa</a>
    <a href="#" class="hover:text-indigo-600 transition">Dashboard Guru</a>
    <a href="#" class="hover:text-indigo-600 transition">Rekap</a>
</div>

            <!-- Tombol Login / User -->
            <div class="hidden md:block">
                @auth
                    <a href="#" class="inline-flex items-center gap-2 bg-indigo-600 text-white text-sm font-medium px-5 py-2.5 rounded-full shadow-md shadow-indigo-200 transition hover:bg-indigo-700">
                        <i class="fas fa-user text-xs"></i> {{ Auth::user()->name }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-indigo-600 text-white text-sm font-medium px-5 py-2.5 rounded-full shadow-md shadow-indigo-200 transition hover:bg-indigo-700 {{ request()->routeIs('login', 'login2') ? 'ring-2 ring-indigo-300' : '' }}">
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
                <button class="text-slate-500 hover:text-indigo-600 transition" id="mobileMenuButton">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </nav>
    </div>
</header>