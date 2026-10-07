<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hadirin.web · Beranda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased">

    <!-- ========== NAVBAR ========== -->
    {{-- `publik` => true: halaman ini adalah pintu masuk, jadi navbar selalu
         ditampilkan dalam wujud pengunjung umum. Nama akun, tombol Logout,
         dan link dashboard tidak muncul di sini meski sesinya masih hidup,
         sehingga pengunjung selalu diarahkan ke halaman Login. --}}
    @include('navbar', ['publik' => true])

    <!-- ========== HERO SECTION ========== -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            
            <!-- Kiri: Teks -->
            <div class="space-y-6">
                <div class="inline-block bg-indigo-100/70 text-indigo-800 text-xs font-semibold px-4 py-1.5 rounded-full border border-indigo-200/60">
                    <i class="fas fa-qrcode mr-2"></i> Absensi modern
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight text-slate-800">
                    Satu kartu,<br>
                    <span class="text-indigo-600">dua cara hadir</span>
                    <span class="text-slate-700">: scan atau ketik.</span>
                </h1>

                <p class="text-lg sm:text-xl text-slate-600 max-w-lg leading-relaxed">
                    Lakukan Login Terlebih Dahulu Untuk Memulai Absensi Digital Mu!
                </p>

                <!-- Tombol GET STARTED -->
                <div class="pt-2">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-3 bg-indigo-600 hover:bg-indigo-700 text-white text-base sm:text-lg font-semibold px-8 py-4 rounded-2xl shadow-lg shadow-indigo-200/60 transition transform hover:-translate-y-0.5">
                        GET STARTED
                        <i class="fas fa-arrow-right text-sm"></i>
                    </a>
                </div>

                <!-- Badge -->
                {{-- Badge memakai klaim yang benar-benar ada di aplikasi.
                     Angka pengguna pernah ditulis di sini, padahal tidak ada
                     sumber datanya dan bisa terlupa diperbarui. --}}
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-400 pt-4">
                    <span><i class="fas fa-shield-alt text-indigo-400 mr-1"></i> aman &amp; terenkripsi</span>
                    <span class="hidden sm:inline-block w-px h-4 bg-slate-300"></span>
                    <span><i class="fas fa-chart-line text-indigo-400 mr-1"></i> rekap real-time</span>
                </div>
            </div>

            <!-- Kanan: Ilustrasi Kartu -->
            <div class="relative flex justify-center lg:justify-end">
                <div class="w-full max-w-sm md:max-w-md lg:max-w-lg">
                    <div class="bg-white rounded-3xl shadow-2xl shadow-indigo-100/50 border border-slate-200/60 p-6 md:p-8">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                            </div>
                            <span class="text-xs font-mono text-slate-400 bg-slate-100 px-3 py-1 rounded-full">Hadirin.web</span>
                        </div>

                        <!-- Dua cara absensi -->
                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <!-- Scan -->
                            <div class="bg-indigo-50/70 rounded-2xl p-4 text-center border border-indigo-100/60">
                                <div class="text-3xl text-indigo-500 mb-2">
                                    <i class="fas fa-camera"></i>
                                </div>
                                <p class="text-xs font-semibold text-slate-700">Scan QR</p>
                                <p class="text-[10px] text-slate-400">tap & hadir</p>
                                <div class="mt-2 flex justify-center">
                                    <div class="w-10 h-10 bg-white rounded-lg shadow-inner flex items-center justify-center border border-slate-200/60">
                                        <div class="w-6 h-6 border-2 border-indigo-300 rounded-md flex items-center justify-center">
                                            <div class="w-4 h-4 bg-indigo-200/50 rounded-sm"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Ketik -->
                            <div class="bg-indigo-50/70 rounded-2xl p-4 text-center border border-indigo-100/60">
                                <div class="text-3xl text-indigo-500 mb-2">
                                    <i class="fas fa-keyboard"></i>
                                </div>
                                <p class="text-xs font-semibold text-slate-700">Ketik NIS</p>
                                <p class="text-[10px] text-slate-400">manual cepat</p>
                                <div class="mt-2 flex justify-center">
                                    <div class="w-10 h-10 bg-white rounded-lg shadow-inner flex items-center justify-center border border-slate-200/60">
                                        <span class="text-[10px] font-mono text-slate-500">12345</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-t border-slate-200/70 flex items-center justify-between text-xs text-slate-400">
                            <span><i class="far fa-check-circle text-indigo-400 mr-1"></i> realtime</span>
                            <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-[10px] font-medium">dua cara</span>
                            <span><i class="far fa-clock text-indigo-400 mr-1"></i> 3 detik</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== FOOTER ========== -->
    <footer class="w-full border-t border-slate-200/60 py-4 text-center text-[10px] text-slate-400 bg-white/40 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 flex flex-wrap items-center justify-center gap-3">
            <span>© 2026 Sistem Absensi</span>
            <span class="w-px h-3 bg-slate-300"></span>
            <span>Hadirin.web</span>
        </div>
    </footer>

</body>
</html>