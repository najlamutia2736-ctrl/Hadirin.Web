<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HADIRIN · Konfirmasi Profil</title>
    <!-- Tailwind via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <!-- Font tambahan -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">

    <!-- ========== NAVBAR (sama dengan login1) ========== -->
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
                    <a href="{{ route('home') }}" class="hover:text-indigo-600 transition">Beranda</a>
                    <a href="#" class="hover:text-indigo-600 transition">Absen Siswa</a>
                    <a href="#" class="hover:text-indigo-600 transition">Dashboard Guru</a>
                    <a href="#" class="hover:text-indigo-600 transition">Rekap</a>
                </div>

                <!-- Tombol Login (Desktop) -->
                <div class="hidden md:block">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-indigo-600 text-white text-sm font-medium px-5 py-2.5 rounded-full shadow-md shadow-indigo-200 transition hover:bg-indigo-700">
                        <i class="fas fa-arrow-right-to-bracket text-xs"></i> Log In
                    </a>
                </div>

                <!-- Mobile Menu -->
                <div class="md:hidden flex items-center gap-3">
                    <a href="{{ route('login') }}" class="text-sm font-medium text-indigo-600 bg-indigo-50 px-4 py-2 rounded-full">Log In</a>
                    <button class="text-slate-500 hover:text-indigo-600 transition">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </nav>
        </div>
    </header>

    <!-- ========== MAIN: KONFIRMASI PROFIL ========== -->
    <main class="flex-1 flex items-center justify-center px-4 py-8 sm:py-12">
        <div class="w-full max-w-md">

            <!-- Kartu Konfirmasi Profil -->
            <div class="bg-white rounded-3xl shadow-xl shadow-indigo-100/40 border border-slate-200/60 p-6 sm:p-8 transition hover:shadow-indigo-200/30">

                <!-- Header: Konfirmasi Profil -->
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight flex items-center justify-center gap-2">
                        <i class="fas fa-user-check text-indigo-600 text-2xl"></i>
                        Konfirmasi Profil
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">Lanjutkan Sebagai</p>
                </div>

                <!-- Profil User -->
                <div class="bg-indigo-50/60 rounded-2xl p-6 mb-6 border border-indigo-100/60">
                    <div class="flex items-center gap-4">
                        <!-- Avatar -->
                        <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-2xl font-bold">
                            <i class="fas fa-user-circle text-4xl"></i>
                        </div>
                        <!-- Info User -->
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-slate-800">Najla Mutia</h3>
                            <p class="text-sm text-slate-500">najla.mutia@smk.belajar.id</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="inline-flex items-center gap-1 text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">
                                    <i class="fas fa-check-circle text-emerald-500 text-[10px]"></i> Terverifikasi
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pesan Konfirmasi -->
                <div class="mb-8">
                    <p class="text-sm text-slate-600 text-center leading-relaxed">
                        <i class="fas fa-info-circle text-indigo-400 mr-1"></i>
                        Silahkan Konfirmasi Data Diri Anda Untuk Memulai Absensi Digital
                    </p>
                </div>

                <!-- Tombol Konfirmasi -->
                <!-- Tombol Konfirmasi -->
<a href="{{ route('beranda') }}" 
   class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3.5 rounded-xl shadow-md shadow-indigo-200/60 transition flex items-center justify-center gap-2 text-base group">
    <i class="fas fa-check-circle group-hover:scale-110 transition"></i>
    Konfirmasi & lanjut Ke Beranda
    <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition"></i>
</a>

                <!-- Link Ganti Akun -->
                <div class="text-center mt-5">
                    <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-indigo-600 transition inline-flex items-center gap-2">
                        <i class="fas fa-exchange-alt text-indigo-400"></i>
                        Bukan Anda? Ganti Akun Google
                    </a>
                </div>

                <!-- Footer Form -->
                <p class="text-xs text-slate-400 text-center mt-6 pt-4 border-t border-slate-200/60">
                    <i class="fas fa-shield-alt text-indigo-300 mr-1"></i> 
                    Data Anda aman & terenkripsi
                </p>
            </div>

            <!-- Back Link -->
            <div class="text-center mt-5 text-xs text-slate-400">
                <a href="{{ route('login') }}" class="hover:text-indigo-500 transition inline-flex items-center gap-1">
                    <i class="fas fa-arrow-left"></i> Kembali ke Login
                </a>
            </div>
        </div>
    </main>

    <!-- ========== FOOTER ========== -->
    <footer class="w-full border-t border-slate-200/60 py-4 text-center text-[10px] text-slate-400 bg-white/40 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 flex flex-wrap items-center justify-center gap-3">
            <span>© 2026 Hadirin.web</span>
            <span class="w-px h-3 bg-slate-300"></span>
            <span>Absensi Siswa Harian</span>
            <span class="w-px h-3 bg-slate-300"></span>
            <span><i class="fas fa-image mr-1"></i> Login2.png</span>
        </div>
    </footer>

</body>
</html>