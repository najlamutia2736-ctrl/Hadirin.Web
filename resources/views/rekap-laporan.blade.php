<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hadirin.web · Rekap & Laporan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; }
        .gradient-header { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
        .stat-card { transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(124, 58, 237, 0.15); }
        .table-row-hover:hover { background-color: #f1f5f9; }
        .badge-hadir { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 999px; font-size: 11px; }
        .badge-izin { background: #fef9c3; color: #854d0e; padding: 4px 10px; border-radius: 999px; font-size: 11px; }
        .badge-sakit { background: #fce4ec; color: #b91c1c; padding: 4px 10px; border-radius: 999px; font-size: 11px; }
        .badge-alpha { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 999px; font-size: 11px; }
        .avatar-circle { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; }
        .progress-bar { height: 8px; border-radius: 4px; background: #e2e8f0; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
        .rekap-tab { color: #64748b; background: transparent; border: none; cursor: pointer; white-space: nowrap; }
        .rekap-tab:hover { background: #f1f5f9; color: #334155; }
        .rekap-tab.active { background: linear-gradient(135deg, #a855f7, #6366f1); color: white; box-shadow: 0 4px 12px rgba(168, 85, 247, 0.3); }
        .rekap-content.hidden { display: none; }
        .live-dot { animation: livePulse 1.5s infinite; }
        @keyframes livePulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.3; transform: scale(0.8); } }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">

    <!-- ========== NAVBAR ========== -->
    <header class="w-full bg-white/80 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-40">
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
                    <a href="{{ route('beranda') }}" class="hover:text-indigo-600 transition">Beranda</a>
                    <a href="{{ route('absen.siswa.qr') }}" class="hover:text-indigo-600 transition">Absen Siswa</a>
                    <a href="{{ route('guru.dashboard') }}" class="hover:text-indigo-600 transition">Dashboard Guru</a>
                    <a href="{{ route('cms.dashboard') }}" class="hover:text-indigo-600 transition">Dashboard Admin</a>
                    <a href="{{ route('cms.rekap') }}" class="hover:text-indigo-600 transition text-indigo-600 font-semibold">Rekap</a>
                </div>

                <!-- Tombol User -->
                <div class="hidden md:block">
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-600 flex items-center gap-2">
                            <i class="fas fa-user-circle text-indigo-600 text-lg"></i>
                            <span id="userNavName">Najla Mutia</span>
                        </span>
                        <button onclick="logout()" class="text-sm text-red-500 hover:text-red-700 transition">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div class="md:hidden flex items-center gap-3">
                    <span class="text-sm font-medium text-slate-600">
                        <i class="fas fa-user-circle text-indigo-600"></i>
                        <span id="userNavNameMobile">Najla</span>
                    </span>
                    <button onclick="toggleMobileMenu()" class="text-slate-500 hover:text-indigo-600 transition">
                        <i class="fas fa-bars text-xl" id="mobileMenuIcon"></i>
                    </button>
                </div>
            </nav>

            <!-- Mobile Dropdown -->
            <div id="mobileMenu" class="hidden md:hidden border-t border-slate-200/60 bg-white/95 backdrop-blur-sm">
                <div class="px-4 py-3 space-y-1">
                    <a href="{{ route('beranda') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50">
                        <i class="fas fa-home w-5"></i> Beranda
                    </a>
                    <a href="{{ route('absen.siswa.qr') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50">
                        <i class="fas fa-user-graduate w-5"></i> Absen Siswa
                    </a>
                    <a href="{{ route('guru.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50">
                        <i class="fas fa-chalkboard-teacher w-5"></i> Dashboard Guru
                    </a>
                    <a href="{{ route('cms.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50">
                        <i class="fas fa-user-shield w-5"></i> Dashboard Admin
                    </a>
                    <a href="{{ route('cms.rekap') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-indigo-600 bg-indigo-50">
                        <i class="fas fa-file-alt w-5"></i> Rekap
                    </a>
                    <div class="border-t border-slate-200/60 my-2"></div>
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-red-500 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt w-5"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8">

        <!-- HEADER REKAP (sama seperti dashboard admin) -->
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-6 mb-6 text-white relative overflow-hidden">
            <div class="absolute right-0 top-0 opacity-10">
                <i class="fas fa-file-alt text-9xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-purple-100 text-xs mb-1">Sistem Pelaporan</p>
                <h2 class="text-2xl font-bold mb-2">Rekap & Laporan</h2>
                <p class="text-sm text-purple-100/90 max-w-2xl">
                    Rekapitulasi data presensi, siswa, guru, dan kelas. Filter berdasarkan periode dan ekspor dalam berbagai format.
                </p>
                <div class="flex flex-wrap gap-2 mt-4">
                    <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs">
                        <i class="fas fa-calendar-day mr-1"></i> <span id="laporanDate">-</span>
                    </span>
                    <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs">
                        <i class="fas fa-database mr-1"></i> <span id="laporanTotal">0</span> Data Terkumpul
                    </span>
                </div>
            </div>
        </div>

        <!-- STAT CARDS REKAP -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clipboard-list text-purple-600 text-sm"></i>
                            </div>
                            <p class="text-xs text-slate-500 font-medium">Total Presensi</p>
                        </div>
                        <p class="text-2xl font-bold text-slate-800" id="statTotalPresensi">0</p>
                        <p class="text-[10px] text-purple-500 mt-1"><i class="fas fa-database"></i> Semua data</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-50 rounded-full flex items-center justify-center">
                        <i class="fas fa-clipboard-check text-purple-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-check text-emerald-600 text-sm"></i>
                            </div>
                            <p class="text-xs text-slate-500 font-medium">Rata Kehadiran</p>
                        </div>
                        <p class="text-2xl font-bold text-slate-800" id="statRataKehadiran">0%</p>
                        <p class="text-[10px] text-emerald-500 mt-1"><i class="fas fa-arrow-up"></i> Dari semua kelas</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-line text-emerald-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-blue-600 text-sm"></i>
                            </div>
                            <p class="text-xs text-slate-500 font-medium">Total Siswa</p>
                        </div>
                        <p class="text-2xl font-bold text-slate-800" id="statTotalSiswaLaporan">0</p>
                        <p class="text-[10px] text-blue-500 mt-1"><i class="fas fa-user-graduate"></i> Terdaftar</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-graduate text-blue-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-school text-amber-600 text-sm"></i>
                            </div>
                            <p class="text-xs text-slate-500 font-medium">Total Kelas</p>
                        </div>
                        <p class="text-2xl font-bold text-slate-800" id="statTotalKelasLaporan">0</p>
                        <p class="text-[10px] text-amber-500 mt-1"><i class="fas fa-chalkboard-teacher"></i> Aktif</p>
                    </div>
                    <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center">
                        <i class="fas fa-school text-amber-500 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB NAVIGASI REKAP -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-2 mb-6 overflow-x-auto">
            <div class="flex gap-1 min-w-max">
                <button onclick="switchRekapTab('presensi')" id="tab-rekap-presensi" class="rekap-tab active flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-clipboard-list"></i> Rekap Presensi
                </button>
                <button onclick="switchRekapTab('siswa')" id="tab-rekap-siswa" class="rekap-tab flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-user-graduate"></i> Rekap Siswa
                </button>
                <button onclick="switchRekapTab('guru')" id="tab-rekap-guru" class="rekap-tab flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-chalkboard-teacher"></i> Rekap Guru
                </button>
                <button onclick="switchRekapTab('kelas')" id="tab-rekap-kelas" class="rekap-tab flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-school"></i> Rekap Kelas
                </button>
            </div>
        </div>

        <!-- KONTEN TAB: REKAP PRESENSI -->
        <div id="rekap-content-presensi" class="rekap-content">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-slate-800">
                            <i class="fas fa-clipboard-list text-purple-500 mr-2"></i> Rekap Presensi Siswa
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">Rekapitulasi kehadiran semua siswa</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <select id="filterPeriodeRekap" class="px-3 py-2 text-xs border border-slate-200 rounded-lg bg-slate-50 outline-none">
                            <option value="all">Semua Periode</option>
                            <option value="hari-ini">Hari Ini</option>
                            <option value="minggu-ini">Minggu Ini</option>
                            <option value="bulan-ini">Bulan Ini</option>
                        </select>
                        <button onclick="exportRekapPresensi()" class="px-3 py-2 text-xs bg-gradient-to-r from-purple-500 to-emerald-600 text-white rounded-lg hover:shadow-lg transition">
                            <i class="fas fa-file-excel mr-1"></i> Export
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50/80 border-b">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Siswa</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Kelas</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Hadir</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Izin</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Sakit</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Alpha</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">% Hadir</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="rekapPresensiBody">
                            <!-- Data akan diisi via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- KONTEN TAB: REKAP SISWA -->
        <div id="rekap-content-siswa" class="rekap-content hidden">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-slate-800">
                            <i class="fas fa-user-graduate text-purple-500 mr-2"></i> Rekap Data Siswa
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">Statistik siswa per kelas dan gender</p>
                    </div>
                    <button onclick="exportRekapSiswa()" class="px-3 py-2 text-xs bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-lg hover:shadow-lg transition">
                        <i class="fas fa-file-excel mr-1"></i> Export
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50/80 border-b">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Kelas</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Laki-laki</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Perempuan</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Rata Kehadiran</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="rekapSiswaBody">
                            <!-- Data akan diisi via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- KONTEN TAB: REKAP GURU -->
        <div id="rekap-content-guru" class="rekap-content hidden">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-slate-800">
                            <i class="fas fa-chalkboard-teacher text-emerald-500 mr-2"></i> Rekap Data Guru
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">Statistik guru berdasarkan mata pelajaran</p>
                    </div>
                    <button onclick="exportRekapGuru()" class="px-3 py-2 text-xs bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-lg hover:shadow-lg transition">
                        <i class="fas fa-file-excel mr-1"></i> Export
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50/80 border-b">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Mata Pelajaran</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Jumlah Guru</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Laki-laki</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Perempuan</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Rata Pengalaman</th>
                            </tr>
                        </thead>
                        <tbody id="rekapGuruBody">
                            <!-- Data akan diisi via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- KONTEN TAB: REKAP KELAS -->
        <div id="rekap-content-kelas" class="rekap-content hidden">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-slate-800">
                            <i class="fas fa-school text-amber-500 mr-2"></i> Rekap Data Kelas
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">Rekapitulasi kelas per tingkat dan jurusan</p>
                    </div>
                    <button onclick="exportRekapKelas()" class="px-3 py-2 text-xs bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition">
                        <i class="fas fa-file-excel mr-1"></i> Export
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50/80 border-b">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Kelas</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Tingkat</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Jurusan</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Jumlah Siswa</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Wali Kelas</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody id="rekapKelasBody">
                            <!-- Data akan diisi via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- QUICK ACTIONS LAPORAN -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <button onclick="goToPresensi()" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 text-left hover:shadow-md hover:border-purple-200 transition group">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center group-hover:bg-purple-500 transition">
                        <i class="fas fa-clipboard-list text-purple-600 group-hover:text-white text-lg transition"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-800 text-sm">Data Presensi</p>
                        <p class="text-xs text-slate-500">Lihat detail presensi</p>
                    </div>
                    <i class="fas fa-arrow-right text-slate-300 ml-auto group-hover:text-purple-500 transition"></i>
                </div>
            </button>
            <button onclick="goToSiswa()" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 text-left hover:shadow-md hover:border-emerald-200 transition group">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center group-hover:bg-emerald-500 transition">
                        <i class="fas fa-user-graduate text-emerald-600 group-hover:text-white text-lg transition"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-800 text-sm">Data Siswa</p>
                        <p class="text-xs text-slate-500">Kelola data siswa</p>
                    </div>
                    <i class="fas fa-arrow-right text-slate-300 ml-auto group-hover:text-emerald-500 transition"></i>
                </div>
            </button>
            <button onclick="goToKelas()" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 text-left hover:shadow-md hover:border-amber-200 transition group">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center group-hover:bg-amber-500 transition">
                        <i class="fas fa-school text-amber-600 group-hover:text-white text-lg transition"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-800 text-sm">Data Kelas</p>
                        <p class="text-xs text-slate-500">Kelola data kelas</p>
                    </div>
                    <i class="fas fa-arrow-right text-slate-300 ml-auto group-hover:text-amber-500 transition"></i>
                </div>
            </button>
        </div>

        <!-- Tombol Kembali -->
        <div class="mt-4 text-center">
            <a href="{{ route('beranda') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>

    </main>

    <!-- ========== FOOTER ========== -->
    <footer class="w-full border-t border-slate-200/60 py-4 text-center text-[10px] text-slate-400 bg-white/40 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 flex flex-wrap items-center justify-center gap-3">
            <span>© 2026 Sistem Absensi</span>
            <span class="w-px h-3 bg-slate-300"></span>
            <span>Hadirin.web</span>
            <span class="w-px h-3 bg-slate-300"></span>
            <span><i class="fas fa-image mr-1"></i> Rekap&Laporan.png</span>
        </div>
    </footer>

    <!-- ========== JAVASCRIPT ========== -->
    <script>
        // ============================================================
        // DATA DUMMY (disinkronkan dengan Dashboard Admin)
        // ============================================================
        let dataPresensi = [
            { id: 1, nama: 'Najla Mutia', kelas: 'XII.RPL', tanggal: '2025-01-15', jamMasuk: '07:15', status: 'Hadir', metode: 'Scan QR' },
            { id: 2, nama: 'Yasmin', kelas: 'XII.RPL', tanggal: '2025-01-15', jamMasuk: '07:18', status: 'Hadir', metode: 'ID Unik' },
            { id: 3, nama: 'Alex Pratama', kelas: 'XII.RPL', tanggal: '2025-01-15', jamMasuk: '07:20', status: 'Hadir', metode: 'Scan QR' },
            { id: 4, nama: 'Dina Fitria', kelas: 'XII.RPL', tanggal: '2025-01-15', jamMasuk: '08:30', status: 'Izin', metode: 'Izin/Sakit' },
            { id: 5, nama: 'Arjuna Wijaya', kelas: 'XII.RPL', tanggal: '2025-01-15', jamMasuk: '-', status: 'Sakit', metode: 'Izin/Sakit' },
            { id: 6, nama: 'Rizky Ramadhan', kelas: 'XII.TKJ', tanggal: '2025-01-15', jamMasuk: '07:10', status: 'Hadir', metode: 'Scan QR' },
            { id: 7, nama: 'Siti Nurhaliza', kelas: 'XII.TKJ', tanggal: '2025-01-15', jamMasuk: '07:22', status: 'Hadir', metode: 'ID Unik' },
            { id: 8, nama: 'Budi Santoso', kelas: 'XII.TKJ', tanggal: '2025-01-15', jamMasuk: '-', status: 'Alpha', metode: '-' },
            { id: 9, nama: 'Citra Dewi', kelas: 'XI.RPL', tanggal: '2025-01-15', jamMasuk: '07:12', status: 'Hadir', metode: 'Scan QR' },
            { id: 10, nama: 'Eko Prasetyo', kelas: 'XI.RPL', tanggal: '2025-01-15', jamMasuk: '07:25', status: 'Hadir', metode: 'ID Unik' },
            { id: 11, nama: 'Fitriani', kelas: 'XI.RPL', tanggal: '2025-01-15', jamMasuk: '09:00', status: 'Izin', metode: 'Izin/Sakit' },
            { id: 12, nama: 'Gilang Permana', kelas: 'XI.TKJ', tanggal: '2025-01-15', jamMasuk: '07:19', status: 'Hadir', metode: 'Scan QR' },
            { id: 13, nama: 'Hana Salsabila', kelas: 'XI.TKJ', tanggal: '2025-01-15', jamMasuk: '07:21', status: 'Hadir', metode: 'ID Unik' },
            { id: 14, nama: 'Irfan Hakim', kelas: 'XI.TKJ', tanggal: '2025-01-15', jamMasuk: '-', status: 'Sakit', metode: 'Izin/Sakit' },
            { id: 15, nama: 'Joko Widodo', kelas: 'XII.RPL', tanggal: '2025-01-14', jamMasuk: '07:11', status: 'Hadir', metode: 'Scan QR' },
            { id: 16, nama: 'Kartika Sari', kelas: 'XII.RPL', tanggal: '2025-01-14', jamMasuk: '07:16', status: 'Hadir', metode: 'ID Unik' },
            { id: 17, nama: 'Lukman Hakim', kelas: 'XII.TKJ', tanggal: '2025-01-14', jamMasuk: '-', status: 'Alpha', metode: '-' },
            { id: 18, nama: 'Maya Anggraini', kelas: 'XI.RPL', tanggal: '2025-01-14', jamMasuk: '07:14', status: 'Hadir', metode: 'Scan QR' },
            { id: 19, nama: 'Nanda Pratama', kelas: 'XI.TKJ', tanggal: '2025-01-14', jamMasuk: '07:23', status: 'Hadir', metode: 'ID Unik' },
            { id: 20, nama: 'Oki Setiana', kelas: 'XII.RPL', tanggal: '2025-01-13', jamMasuk: '07:13', status: 'Hadir', metode: 'Scan QR' }
        ];

        let dataSiswa = [
            { id: 1, nama: 'Najla Mutia', kelas: 'XII.RPL', gender: 'P', kehadiran: 100 },
            { id: 2, nama: 'Yasmin', kelas: 'XII.RPL', gender: 'P', kehadiran: 98 },
            { id: 3, nama: 'Alex Pratama', kelas: 'XII.RPL', gender: 'L', kehadiran: 97 },
            { id: 4, nama: 'Dina Fitria', kelas: 'XII.RPL', gender: 'P', kehadiran: 95 },
            { id: 5, nama: 'Arjuna Wijaya', kelas: 'XII.RPL', gender: 'L', kehadiran: 92 },
            { id: 6, nama: 'Rizky Ramadhan', kelas: 'XII.TKJ', gender: 'L', kehadiran: 94 },
            { id: 7, nama: 'Siti Nurhaliza', kelas: 'XII.TKJ', gender: 'P', kehadiran: 96 },
            { id: 8, nama: 'Budi Santoso', kelas: 'XII.TKJ', gender: 'L', kehadiran: 88 },
            { id: 9, nama: 'Citra Dewi', kelas: 'XI.RPL', gender: 'P', kehadiran: 98 },
            { id: 10, nama: 'Eko Prasetyo', kelas: 'XI.RPL', gender: 'L', kehadiran: 95 },
            { id: 11, nama: 'Fitriani', kelas: 'XI.RPL', gender: 'P', kehadiran: 90 },
            { id: 12, nama: 'Gilang Permana', kelas: 'XI.TKJ', gender: 'L', kehadiran: 93 },
            { id: 13, nama: 'Hana Salsabila', kelas: 'XI.TKJ', gender: 'P', kehadiran: 97 },
            { id: 14, nama: 'Irfan Hakim', kelas: 'XI.TKJ', gender: 'L', kehadiran: 89 },
            { id: 15, nama: 'Joko Widodo', kelas: 'XII.RPL', gender: 'L', kehadiran: 91 },
            { id: 16, nama: 'Kartika Sari', kelas: 'XII.RPL', gender: 'P', kehadiran: 96 },
            { id: 17, nama: 'Lukman Hakim', kelas: 'XII.TKJ', gender: 'L', kehadiran: 85 },
            { id: 18, nama: 'Maya Anggraini', kelas: 'XI.RPL', gender: 'P', kehadiran: 94 },
            { id: 19, nama: 'Nanda Pratama', kelas: 'XI.TKJ', gender: 'L', kehadiran: 92 },
            { id: 20, nama: 'Oki Setiana', kelas: 'XII.RPL', gender: 'P', kehadiran: 97 }
        ];

        let dataGuru = [
            { id: 1, nama: 'Budi Hartono, S.Pd', mapel: 'Matematika', gender: 'L', pengalaman: 15 },
            { id: 2, nama: 'Siti Aminah, M.Pd', mapel: 'Bahasa Indonesia', gender: 'P', pengalaman: 12 },
            { id: 3, nama: 'Ahmad Fauzi, S.Pd', mapel: 'Bahasa Inggris', gender: 'L', pengalaman: 10 },
            { id: 4, nama: 'Dewi Lestari, S.Si', mapel: 'Fisika', gender: 'P', pengalaman: 11 },
            { id: 5, nama: 'Rudi Santoso, M.Si', mapel: 'Kimia', gender: 'L', pengalaman: 18 },
            { id: 6, nama: 'Rina Marlina, S.Pd', mapel: 'Biologi', gender: 'P', pengalaman: 9 },
            { id: 7, nama: 'Andi Prasetyo, S.Kom', mapel: 'Pemrograman', gender: 'L', pengalaman: 10 },
            { id: 8, nama: 'Maya Sari, S.Kom', mapel: 'Jaringan', gender: 'P', pengalaman: 8 },
            { id: 9, nama: 'Hendra Gunawan, S.Ds', mapel: 'Multimedia', gender: 'L', pengalaman: 9 },
            { id: 10, nama: 'Yuni Astuti, S.Pd', mapel: 'Sejarah', gender: 'P', pengalaman: 14 }
        ];

        let dataKelas = [
            { id: 1, nama: 'XII.RPL 1', tingkat: 'XII', jurusan: 'RPL', wali: 'Budi Hartono, S.Pd', jumlahSiswa: 32, kehadiran: 96 },
            { id: 2, nama: 'XII.RPL 2', tingkat: 'XII', jurusan: 'RPL', wali: 'Andi Prasetyo, S.Kom', jumlahSiswa: 30, kehadiran: 94 },
            { id: 3, nama: 'XII.TKJ 1', tingkat: 'XII', jurusan: 'TKJ', wali: 'Maya Sari, S.Kom', jumlahSiswa: 30, kehadiran: 88 },
            { id: 4, nama: 'XI.RPL 1', tingkat: 'XI', jurusan: 'RPL', wali: 'Siti Aminah, M.Pd', jumlahSiswa: 35, kehadiran: 92 }
        ];

        // ============================================================
        // INIT
        // ============================================================
        function initLaporanDate() {
            const today = new Date();
            const dateStr = today.toLocaleDateString('id-ID', { 
                weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' 
            });
            const el = document.getElementById('laporanDate');
            if (el) el.textContent = dateStr;
        }

        // Switch antar tab rekap
        function switchRekapTab(tab) {
            document.querySelectorAll('.rekap-tab').forEach(btn => btn.classList.remove('active'));
            const activeBtn = document.getElementById('tab-rekap-' + tab);
            if (activeBtn) activeBtn.classList.add('active');

            document.querySelectorAll('.rekap-content').forEach(el => el.classList.add('hidden'));
            const content = document.getElementById('rekap-content-' + tab);
            if (content) content.classList.remove('hidden');

            if (tab === 'presensi') renderRekapPresensi();
            else if (tab === 'siswa') renderRekapSiswa();
            else if (tab === 'guru') renderRekapGuru();
            else if (tab === 'kelas') renderRekapKelas();
        }

        // Statistik utama rekap
        function renderStatistikRekap() {
            const totalPresensi = dataPresensi.length;
            const totalSiswa = dataSiswa.length;
            const totalKelas = dataKelas.length;
            const rataKehadiran = totalKelas > 0
                ? Math.round(dataKelas.reduce((a, b) => a + b.kehadiran, 0) / totalKelas)
                : 0;

            const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
            setTxt('statTotalPresensi', totalPresensi);
            setTxt('statRataKehadiran', rataKehadiran + '%');
            setTxt('statTotalSiswaLaporan', totalSiswa);
            setTxt('statTotalKelasLaporan', totalKelas);
            setTxt('laporanTotal', totalPresensi + totalSiswa + dataGuru.length + totalKelas);
        }

        // Render Rekap Presensi
        function renderRekapPresensi() {
            const tbody = document.getElementById('rekapPresensiBody');
            if (!tbody) return;

            const grouped = {};
            dataPresensi.forEach(p => {
                const key = p.nama;
                if (!grouped[key]) {
                    grouped[key] = { nama: p.nama, kelas: p.kelas, hadir: 0, izin: 0, sakit: 0, alpha: 0, total: 0 };
                }
                grouped[key].total++;
                if (p.status === 'Hadir') grouped[key].hadir++;
                else if (p.status === 'Izin') grouped[key].izin++;
                else if (p.status === 'Sakit') grouped[key].sakit++;
                else grouped[key].alpha++;
            });

            const list = Object.values(grouped);

            if (list.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-2 text-slate-400">
                                <i class="fas fa-inbox text-4xl"></i>
                                <p class="text-sm">Belum ada data presensi</p>
                            </div>
                        </td>
                    </tr>`;
                return;
            }

            tbody.innerHTML = list.map((item, i) => {
                const persen = item.total > 0 ? Math.round((item.hadir / item.total) * 100) : 0;
                const persenBg = persen >= 90 ? 'bg-emerald-100 text-emerald-700' : persen >= 75 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700';

                return `
                    <tr class="table-row-hover border-b">
                        <td class="px-4 py-3 text-sm text-slate-500">${i + 1}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="avatar-circle bg-purple-100 text-purple-600">${item.nama.charAt(0)}</div>
                                <div>
                                    <p class="text-sm font-medium text-slate-800">${item.nama}</p>
                                    <p class="text-[10px] text-slate-400">Total: ${item.total} data</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-slate-600">${item.kelas}</td>
                        <td class="px-4 py-3 text-center text-sm font-semibold text-emerald-600">${item.hadir}</td>
                        <td class="px-4 py-3 text-center text-sm text-amber-600">${item.izin}</td>
                        <td class="px-4 py-3 text-center text-sm text-rose-600">${item.sakit}</td>
                        <td class="px-4 py-3 text-center text-sm text-slate-500">${item.alpha}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium ${persenBg}">${persen}%</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="detailRekapSiswa('${item.nama}')" class="btn-edit text-xs" title="Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>`;
            }).join('');
        }

        // Render Rekap Siswa
        function renderRekapSiswa() {
            const tbody = document.getElementById('rekapSiswaBody');
            if (!tbody) return;

            const grouped = {};
            dataSiswa.forEach(s => {
                if (!grouped[s.kelas]) {
                    grouped[s.kelas] = { kelas: s.kelas, total: 0, laki: 0, perempuan: 0, totalKehadiran: 0 };
                }
                grouped[s.kelas].total++;
                grouped[s.kelas].totalKehadiran += s.kehadiran;
                if (s.gender === 'L') grouped[s.kelas].laki++;
                else grouped[s.kelas].perempuan++;
            });

            const list = Object.values(grouped);

            if (list.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-2 text-slate-400">
                                <i class="fas fa-inbox text-4xl"></i>
                                <p class="text-sm">Belum ada data siswa</p>
                            </div>
                        </td>
                    </tr>`;
                return;
            }

            tbody.innerHTML = list.map((item, i) => {
                const rataKehadiran = item.total > 0 ? Math.round(item.totalKehadiran / item.total) : 0;
                const persenBg = rataKehadiran >= 95 ? 'bg-emerald-100 text-emerald-700' : rataKehadiran >= 85 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700';

                return `
                    <tr class="table-row-hover border-b">
                        <td class="px-4 py-3 text-sm text-slate-500">${i + 1}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="avatar-circle bg-purple-100 text-purple-600">${item.kelas.charAt(0)}</div>
                                <p class="text-sm font-medium text-slate-800">${item.kelas}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold text-slate-700">${item.total}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-700">
                                <i class="fas fa-mars"></i> ${item.laki}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-pink-100 text-pink-700">
                                <i class="fas fa-venus"></i> ${item.perempuan}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium ${persenBg}">${rataKehadiran}%</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="goToSiswa()" class="btn-edit text-xs" title="Lihat Siswa">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>`;
            }).join('');
        }

        // Render Rekap Guru
        function renderRekapGuru() {
            const tbody = document.getElementById('rekapGuruBody');
            if (!tbody) return;

            const grouped = {};
            dataGuru.forEach(g => {
                if (!grouped[g.mapel]) {
                    grouped[g.mapel] = { mapel: g.mapel, total: 0, laki: 0, perempuan: 0, totalPengalaman: 0 };
                }
                grouped[g.mapel].total++;
                grouped[g.mapel].totalPengalaman += g.pengalaman;
                if (g.gender === 'L') grouped[g.mapel].laki++;
                else grouped[g.mapel].perempuan++;
            });

            const list = Object.values(grouped);

            if (list.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-2 text-slate-400">
                                <i class="fas fa-inbox text-4xl"></i>
                                <p class="text-sm">Belum ada data guru</p>
                            </div>
                        </td>
                    </tr>`;
                return;
            }

            tbody.innerHTML = list.map((item, i) => {
                const rataPengalaman = item.total > 0 ? Math.round(item.totalPengalaman / item.total) : 0;

                return `
                    <tr class="table-row-hover border-b">
                        <td class="px-4 py-3 text-sm text-slate-500">${i + 1}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="avatar-circle bg-emerald-100 text-emerald-600">${item.mapel.charAt(0)}</div>
                                <p class="text-sm font-medium text-slate-800">${item.mapel}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold text-slate-700">${item.total}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-700">
                                <i class="fas fa-mars"></i> ${item.laki}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-pink-100 text-pink-700">
                                <i class="fas fa-venus"></i> ${item.perempuan}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold text-amber-600">${rataPengalaman} thn</td>
                    </tr>`;
            }).join('');
        }

        // Render Rekap Kelas
        function renderRekapKelas() {
            const tbody = document.getElementById('rekapKelasBody');
            if (!tbody) return;

            if (dataKelas.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-2 text-slate-400">
                                <i class="fas fa-inbox text-4xl"></i>
                                <p class="text-sm">Belum ada data kelas</p>
                            </div>
                        </td>
                    </tr>`;
                return;
            }

            tbody.innerHTML = dataKelas.map((k, i) => {
                const persenBg = k.kehadiran >= 95 ? 'bg-emerald-100 text-emerald-700' : k.kehadiran >= 85 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700';
                const tingkatBadge = k.tingkat === 'XII' ? 'bg-rose-100 text-rose-700' : k.tingkat === 'XI' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700';
                const jurusanBadge = k.jurusan === 'RPL' ? 'bg-purple-100 text-purple-700' : k.jurusan === 'TKJ' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700';

                return `
                    <tr class="table-row-hover border-b">
                        <td class="px-4 py-3 text-sm text-slate-500">${i + 1}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="avatar-circle bg-amber-100 text-amber-600">${k.nama.charAt(0)}</div>
                                <p class="text-sm font-medium text-slate-800">${k.nama}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium ${tingkatBadge}">Kelas ${k.tingkat}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium ${jurusanBadge}">${k.jurusan}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold text-slate-700">${k.jumlahSiswa}</td>
                        <td class="px-4 py-3 text-center text-sm text-slate-600">${k.wali}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium ${persenBg}">${k.kehadiran}%</span>
                        </td>
                    </tr>`;
            }).join('');
        }

        // Detail rekap siswa
        function detailRekapSiswa(nama) {
            const data = dataPresensi.filter(p => p.nama === nama);
            if (data.length === 0) return;
            
            let msg = `📊 Detail Rekap: ${nama}\n\n`;
            data.forEach((d, i) => {
                msg += `${i + 1}. ${d.tanggal} - ${d.status} (${d.metode})\n`;
            });
            alert(msg);
        }

        // Export helpers
        function downloadCSV(csv, prefix) {
            const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            const dateStr = new Date().toISOString().split('T')[0];
            a.href = url;
            a.download = `${prefix}_${dateStr}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        function exportRekapPresensi() {
            const grouped = {};
            dataPresensi.forEach(p => {
                if (!grouped[p.nama]) {
                    grouped[p.nama] = { nama: p.nama, kelas: p.kelas, hadir: 0, izin: 0, sakit: 0, alpha: 0 };
                }
                if (p.status === 'Hadir') grouped[p.nama].hadir++;
                else if (p.status === 'Izin') grouped[p.nama].izin++;
                else if (p.status === 'Sakit') grouped[p.nama].sakit++;
                else grouped[p.nama].alpha++;
            });
            let csv = 'No,Nama,Kelas,Hadir,Izin,Sakit,Alpha,Persentase\n';
            Object.values(grouped).forEach((g, i) => {
                const total = g.hadir + g.izin + g.sakit + g.alpha;
                const persen = total > 0 ? Math.round((g.hadir / total) * 100) : 0;
                csv += `${i + 1},${g.nama},${g.kelas},${g.hadir},${g.izin},${g.sakit},${g.alpha},${persen}%\n`;
            });
            downloadCSV(csv, 'rekap_presensi');
        }

        function exportRekapSiswa() {
            const grouped = {};
            dataSiswa.forEach(s => {
                if (!grouped[s.kelas]) {
                    grouped[s.kelas] = { kelas: s.kelas, total: 0, laki: 0, perempuan: 0, kehadiran: 0 };
                }
                grouped[s.kelas].total++;
                grouped[s.kelas].kehadiran += s.kehadiran;
                if (s.gender === 'L') grouped[s.kelas].laki++;
                else grouped[s.kelas].perempuan++;
            });
            let csv = 'No,Kelas,Total,Laki-laki,Perempuan,Rata Kehadiran\n';
            Object.values(grouped).forEach((g, i) => {
                const rata = g.total > 0 ? Math.round(g.kehadiran / g.total) : 0;
                csv += `${i + 1},${g.kelas},${g.total},${g.laki},${g.perempuan},${rata}%\n`;
            });
            downloadCSV(csv, 'rekap_siswa');
        }

        function exportRekapGuru() {
            const grouped = {};
            dataGuru.forEach(g => {
                if (!grouped[g.mapel]) {
                    grouped[g.mapel] = { mapel: g.mapel, total: 0, laki: 0, perempuan: 0, pengalaman: 0 };
                }
                grouped[g.mapel].total++;
                grouped[g.mapel].pengalaman += g.pengalaman;
                if (g.gender === 'L') grouped[g.mapel].laki++;
                else grouped[g.mapel].perempuan++;
            });
            let csv = 'No,Mata Pelajaran,Jumlah Guru,Laki-laki,Perempuan,Rata Pengalaman\n';
            Object.values(grouped).forEach((g, i) => {
                const rata = g.total > 0 ? Math.round(g.pengalaman / g.total) : 0;
                csv += `${i + 1},${g.mapel},${g.total},${g.laki},${g.perempuan},${rata} thn\n`;
            });
            downloadCSV(csv, 'rekap_guru');
        }

        function exportRekapKelas() {
            let csv = 'No,Nama Kelas,Tingkat,Jurusan,Jumlah Siswa,Wali Kelas,Kehadiran\n';
            dataKelas.forEach((k, i) => {
                csv += `${i + 1},${k.nama},${k.tingkat},${k.jurusan},${k.jumlahSiswa},${k.wali},${k.kehadiran}%\n`;
            });
            downloadCSV(csv, 'rekap_kelas');
        }

        // Navigasi cepat
        function goToPresensi() { window.location.href = "{{ route('cms.dashboard') }}"; }
        function goToSiswa() { window.location.href = "{{ route('cms.dashboard') }}"; }
        function goToKelas() { window.location.href = "{{ route('cms.dashboard') }}"; }

        // Logout
        function logout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = "{{ route('login') }}";
            }
        }

        // Mobile menu
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const icon = document.getElementById('mobileMenuIcon');
            if (!menu) return;
            menu.classList.toggle('hidden');
            if (icon) {
                if (menu.classList.contains('hidden')) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                } else {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                }
            }
        }

        // Update navbar name dari localStorage
        function updateNavbarName() {
            const nama = localStorage.getItem('user_nama');
            if (nama) {
                const navName = document.getElementById('userNavName');
                const navNameMobile = document.getElementById('userNavNameMobile');
                if (navName) navName.textContent = nama;
                if (navNameMobile) navNameMobile.textContent = nama.split(' ')[0];
            }
        }

        // ============================================================
        // INIT
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Halaman Rekap & Laporan siap!');
            initLaporanDate();
            renderStatistikRekap();
            switchRekapTab('presensi');
            updateNavbarName();
        });
    </script>

</body>
</html>