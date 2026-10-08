<!DOCTYPE html>
<html lang="id">
@php
    /*
    | Halaman depan aplikasi, beralamat di `/`. Pengunjung umum dan pengguna yang
    | sudah login melihat isi yang sama persis; yang membedakan hanya navbar dan
    | isi tombol masuk, mengikuti sesi masing-masing.
    */
    $user = auth()->user();

    // Menu & kartu "Absen Siswa" hanya untuk akun yang punya profil di tabel
    // `siswas`. Pemeriksaannya sama dengan AbsensiSiswaController.
    $bisaAbsen = $user?->siswa !== null;

    // Sama seperti `navbar.blade.php`: area CMS dan dashboard guru dilindungi
    // middleware `role`, jadi link-nya hanya untuk akun yang boleh membukanya.
    // Kartu peran di bawah tetap tampil untuk semua pengunjung.
    $bukaCms = in_array($user?->role, ['Admin', 'Operator'], true);
    $bukaGuru = $user?->role === 'Guru';

    /*
    | Kartu peran "Siswa" sengaja selalu tampil, bukan ikut disembunyikan
    | bersama menunya. Kartu ini adalah pintu masuk untuk memilih peran,
    | jadi orang yang belum login justru yang paling butuh melihatnya.
    | Yang menyesuaikan hanya tujuan kliknya, supaya halaman absensi tidak
    | pernah bocor ke akun yang tidak berhak (lihat `MenuAbsensiTest`).
    */
    $tujuanSiswa = $bisaAbsen ? route('absensi.index') : ($user === null ? route('login') : route('guru.dashboard'));

    $labelSiswa = match (true) {
        $bisaAbsen => 'Absen Sekarang',
        $user === null => 'Klik',
        default => 'Bukan Akun Siswa',
    };

    // Tombol hero mengikuti sesi: tamu diarahkan ke login, sedangkan pengguna
    // yang sudah masuk langsung ke berandanya. Tanpa ini, akun yang sudah masuk
    // akan mendarat di middleware `guest` dan cuma dipantulkan balik ke beranda.
    $tujuanMasuk = match ($user?->role) {
        'Admin', 'Operator' => route('cms.dashboard'),
        'Guru' => route('guru.dashboard'),
        'Siswa' => $bisaAbsen ? route('absensi.index') : route('beranda'),
        default => route('beranda'),
    };
@endphp

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hadirin.web · Beranda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet" />
    <style>
        /*
         | Link Inter di atas hanya mengunduh satu file, yaitu weight 400. Itu
         | alasan judul utama biasanya tampil tipis meski class-nya `font-extrabold`.
         |
         | Daripada menukar link itu ke seluruh rentang weight — yang ikut
         | menebalkan badge, banner, dan judul kartu di halaman ini — bobot 800
         | dimuat sebagai keluarga font terpisah di bawah. Jadi hanya judul
         | utama yang memakai `.judul-utama`, selebihnya tetap seperti semula.
         */
        @font-face {
            font-family: 'InterTebal';
            font-style: normal;
            font-weight: 800;
            font-display: swap;
            src: url('https://fonts.gstatic.com/s/inter/v20/UcC73FwrK3iLTeHuS_fjbvMwCp50Yjca25L7SUc.woff2') format('woff2');
            unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
        }

        @font-face {
            font-family: 'InterTebal';
            font-style: normal;
            font-weight: 800;
            font-display: swap;
            src: url('https://fonts.gstatic.com/s/inter/v20/UcC73FwrK3iLTeHuS_fjbvMwCp50Yjca1ZL7.woff2') format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .judul-utama {
            font-family: 'InterTebal', 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex flex-col">

    <!-- ========== NAVBAR ========== -->
    @include('navbar')

    <!-- ========== BANNER ========== -->
    {{-- Banner gradien ini yang dulunya menghias beranda sebelum digabung dengan
         halaman awal. Isinya sekarang sangat mirip dengan hero di bawahnya,
         jadi tetap dipertahankan karena masih berfungsi sebagai pengenalan
         singkat sebelum pengunjung sampai ke bagian yang bisa diklik. --}}
    <section class="gradient-bg text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20 text-center">
            <div
                class="inline-block bg-white/20 backdrop-blur-sm px-4 py-1.5 rounded-full text-xs font-semibold mb-4">
                <i class="fas fa-star mr-1"></i> Selamat Datang di Platform Absensi Digital
            </div>
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold mb-4 tracking-tight">
                ABSENSI SEKOLAH <span class="text-yellow-300">DIGITAL</span>
            </h2>
            <p class="text-base md:text-lg text-white/90 max-w-3xl mx-auto">
                Satu kartu, dua cara hadir: <span class="font-semibold text-yellow-200">scan</span> atau <span
                    class="font-semibold text-yellow-200">ketik</span>.
            </p>
        </div>
    </section>

    <!-- ========== HERO ========== -->
    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-16">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

                <!-- Kiri: Teks -->
                <div class="space-y-6">
                    <div
                        class="inline-block bg-indigo-100/70 text-indigo-800 text-xs font-semibold px-4 py-1.5 rounded-full border border-indigo-200/60">
                        <i class="fas fa-qrcode mr-2"></i> Absensi modern
                    </div>

                    <h1
                        class="judul-utama text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight text-slate-800">
                        Satu kartu,<br>
                        <span class="text-indigo-600">dua cara hadir</span>
                        <span class="text-slate-700">: scan atau ketik.</span>
                    </h1>

                    <p class="text-lg sm:text-xl text-slate-600 max-w-lg leading-relaxed">
                        {{-- Kalimat ini berupa ajakan untuk tamu, tapi berubah jadi
                             sapaan begitu penggunanya sudah masuk. --}}
                        @if ($user === null)
                            Lakukan Login Terlebih Dahulu Untuk Memulai Absensi Digital Mu!
                        @else
                            Selamat datang, {{ $user->name }}. Kelola kehadiran sekolah dari satu tempat.
                        @endif
                    </p>

                    <div class="pt-2">
                        <a href="{{ $tujuanMasuk }}"
                            class="inline-flex items-center gap-3 bg-indigo-600 hover:bg-indigo-700 text-white text-base sm:text-lg font-semibold px-8 py-4 rounded-2xl shadow-lg shadow-indigo-200/60 transition transform hover:-translate-y-0.5">
                            {{ $user === null ? 'GET STARTED' : 'LANJUTKAN' }}
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
                                <span class="text-xs font-mono text-slate-400 bg-slate-100 px-3 py-1 rounded-full">
                                    Hadirin.web
                                </span>
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
                                        <div
                                            class="w-10 h-10 bg-white rounded-lg shadow-inner flex items-center justify-center border border-slate-200/60">
                                            <div
                                                class="w-6 h-6 border-2 border-indigo-300 rounded-md flex items-center justify-center">
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
                                        <div
                                            class="w-10 h-10 bg-white rounded-lg shadow-inner flex items-center justify-center border border-slate-200/60">
                                            <span class="text-[10px] font-mono text-slate-500">12345</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="mt-5 pt-4 border-t border-slate-200/70 flex items-center justify-between text-xs text-slate-400">
                                <span><i class="far fa-check-circle text-indigo-400 mr-1"></i> realtime</span>
                                <span
                                    class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-[10px] font-medium">dua
                                    cara</span>
                                <span><i class="far fa-clock text-indigo-400 mr-1"></i> 3 detik</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deskripsi -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6 md:p-8">
                <div class="flex items-start gap-4">
                    <div class="hidden sm:block text-4xl text-indigo-500">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-slate-800 mb-2">Apa itu Hadirin.web?</h2>
                        <p class="text-sm text-slate-600 leading-relaxed max-w-4xl">
                            Hadirin.web merupakan alternatif pengganti buku absensi kertas dengan pencatatan otomatis dan
                            terpusat.
                            Siswa cukup memindai kode QR atau memasukkan ID unik miliknya.
                            <span class="text-indigo-600 font-medium">— guru dan wali murid langsung melihat status
                                kehadiran secara real-time.</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pilih Peran -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
            <div class="mb-8">
                <h3 class="text-2xl font-bold text-slate-800 text-center mb-2">Pilihlah Peranmu Disisni</h3>
                <p class="text-sm text-slate-500 text-center mb-8">Tentukan cara masuk sesuai peranmu di sekolah!</p>
            </div>

            <!-- Cards Peran -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Card Siswa -->
                {{-- Kartu ini tidak pernah disembunyikan: pilih peran harus bisa
                     dilakukan sebelum login. Yang dijaga hanya link tujuan, agar
                     URL halaman absensi tidak bocor ke akun non-siswa. --}}
                <div
                    class="bg-white rounded-2xl shadow-md border border-slate-200/60 p-6 text-center card-hover transition-all duration-300">
                    <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-graduate text-3xl text-indigo-600"></i>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 mb-2">Siswa</h4>
                    <p class="text-sm text-slate-500 mb-4">Absen mandiri lewat scan QRCode atau kode unik.</p>

                    @if ($bisaAbsen)
                        <a href="{{ route('absensi.index') }}"
                            class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-xl transition shadow-md shadow-indigo-200/60">
                            <i class="fas fa-right-to-bracket mr-1"></i> {{ $labelSiswa }}
                        </a>
                    @elseif ($user === null)
                        {{-- Belum login: arahkan ke login, bukan ke halaman absensi,
                             supaya middleware tetap yang menjaga halamannya. --}}
                        <a href="{{ route('login') }}"
                            class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-xl transition shadow-md shadow-indigo-200/60">
                            <i class="fas fa-right-to-bracket mr-1"></i> {{ $labelSiswa }}
                        </a>
                    @else
                        {{-- Sudah login tapi bukan siswa: jelaskan, jangan paksa. --}}
                        <span
                            class="inline-block cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-6 py-2.5 font-medium text-slate-400"
                            title="Gunakan akun siswa untuk absen.">
                            <i class="fas fa-lock mr-1"></i> {{ $labelSiswa }}
                        </span>
                    @endif
                </div>

                <!-- Card Guru / Wali Kelas -->
                <div
                    class="bg-white rounded-2xl shadow-md border border-slate-200/60 p-6 text-center card-hover transition-all duration-300 md:scale-105 md:shadow-lg">
                    <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chalkboard-teacher text-3xl text-emerald-600"></i>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 mb-2">Guru / Wali Kelas</h4>
                    <p class="text-sm text-slate-500 mb-4">Memantau kehadiran Real-Time dan unduh rekap bulanan.</p>
                    <a href="{{ route('guru.dashboard') }}"
                        class="inline-block bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-6 py-2.5 rounded-xl transition shadow-md shadow-emerald-200/60">
                        <i class="fas fa-right-to-bracket mr-1"></i> Klik
                    </a>
                </div>

                <!-- Card Admin / Kepsek -->
                <div
                    class="bg-white rounded-2xl shadow-md border border-slate-200/60 p-6 text-center card-hover transition-all duration-300">
                    <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-shield text-3xl text-purple-600"></i>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 mb-2">Admin / Kepsek</h4>
                    <p class="text-sm text-slate-500 mb-4">Kelola data seluruh siswa dan lihat laporan menyeluruh.</p>
                    <a href="{{ route('cms.dashboard') }}"
                        class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-medium px-6 py-2.5 rounded-xl transition shadow-md shadow-purple-200/60">
                        <i class="fas fa-right-to-bracket mr-1"></i> Klik
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- ========== FOOTER ========== -->
    <footer
        class="w-full border-t border-slate-200/60 py-6 mt-8 text-center text-xs text-slate-400 bg-white/40 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 flex flex-wrap items-center justify-center gap-3">
            <span>© 2026 Sistem Absensi</span>
            <span class="w-px h-3 bg-slate-300"></span>
            <span>Hadirin.web</span>
        </div>
    </footer>

</body>

</html>