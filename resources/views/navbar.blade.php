<!-- resources/views/navbar.blade.php -->
@php
    /*
    | Navbar hanya menampilkan logo dan tombol akun. Semua area (CMS, Dosen,
    | dan Presensi) punya halaman & navigasinya sendiri, jadi navbar publik
    | tidak menampilkan link menu apa pun.
    |
    | Dipakai lewat:
    |     @include('navbar')
    */
    $user = auth()->user();
@endphp
<header class="w-full bg-white/80 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center justify-between h-16 md:h-20">
            <!-- Brand / Logo -->
            <div class="flex items-center gap-2">
                {{-- Logo jadi jalan ke halaman depan, karena link "Beranda" di navbar
                     sengaja tidak ditampilkan. --}}
                <a href="{{ route('beranda') }}" class="text-2xl font-bold text-indigo-700 tracking-tight">
                    Hadirin.<span class="text-slate-700">web</span>
                </a>
            </div>

            <!-- Menu Desktop: sengaja kosong. Semua area punya halaman & navigasinya
                 sendiri, jadi navbar publik hanya menampilkan logo dan akun. -->
            <div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600"></div>

            <!-- Tombol Login / User -->
            <div class="hidden md:block">
                @if ($user)
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-600 flex items-center gap-2">
                            <i class="fas fa-user-circle text-indigo-600 text-lg"></i>
                            <span>{{ $user->name }}</span>
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 transition">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 bg-indigo-600 text-white text-sm font-medium px-5 py-2.5 rounded-full shadow-md shadow-indigo-200 transition hover:bg-indigo-700">
                        <i class="fas fa-arrow-right-to-bracket text-xs"></i> Log In
                    </a>
                @endif
            </div>

            <!-- Mobile Menu: tanpa tombol hamburger karena navbar tidak punya
                 menu dropdown. Nama akun dan logout sudah cukup. -->
            <div class="md:hidden flex items-center gap-3">
                @if ($user)
                    <span class="text-sm font-medium text-slate-600">{{ $user->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-500 hover:text-red-700 transition">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="text-sm font-medium text-indigo-600 bg-indigo-50 px-4 py-2 rounded-full">Log In</a>
                @endif
            </div>
        </nav>
    </div>
</header>
