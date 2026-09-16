<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hadirin.web · Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; }
        .sidebar { width: 280px; min-height: 100vh; background: linear-gradient(180deg, #1e1b4b 0%, #4c1d95 100%); overflow-y: auto; }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
        .sidebar-link { display: flex; align-items: center; gap: 12px; padding: 10px 20px; color: rgba(255,255,255,0.6); border-radius: 8px; transition: 0.2s; font-size: 14px; cursor: pointer; }
        .sidebar-link:hover { background: rgba(255,255,255,0.08); color: white; }
        .sidebar-link.active { background: rgba(255,255,255,0.15); color: white; border-left: 3px solid #a855f7; }
        .sidebar-section-title { color: rgba(255,255,255,0.3); font-size: 10px; text-transform: uppercase; letter-spacing: 1px; padding: 20px 20px 8px; }
        .stat-card { transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(124, 58, 237, 0.15); }
        .table-row-hover:hover { background-color: #f1f5f9; }
        .badge-hadir { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 999px; font-size: 11px; }
        .badge-izin { background: #fef9c3; color: #854d0e; padding: 4px 10px; border-radius: 999px; font-size: 11px; }
        .badge-sakit { background: #fce4ec; color: #b91c1c; padding: 4px 10px; border-radius: 999px; font-size: 11px; }
        .badge-alpha { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 999px; font-size: 11px; }
        .chart-container { position: relative; height: 280px; width: 100%; }
        .mini-chart { width: 70px; height: 45px; position: relative; }
        .live-dot { animation: livePulse 1.5s infinite; }
        @keyframes livePulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.3; transform: scale(0.8); } }
        .main-content { flex: 1; min-height: 100vh; }
        .toggle-sidebar { display: none; }
        .btn-edit { background: #3b82f6; color: white; padding: 4px 10px; border-radius: 6px; font-size: 12px; border: none; cursor: pointer; }
        .btn-delete { background: #ef4444; color: white; padding: 4px 10px; border-radius: 6px; font-size: 12px; border: none; cursor: pointer; }
        .section-content { display: none !important; }
        .section-content.active { display: block !important; }
        .avatar-circle { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; }
        .progress-bar { height: 8px; border-radius: 4px; background: #e2e8f0; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
        @media (max-width: 1024px) {
            .sidebar { position: fixed; left: 0; top: 0; z-index: 100; transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .toggle-sidebar { display: block; }
            .overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99; }
            .overlay.active { display: block; }
        }
    </style>
</head>
<body>

    <div id="overlay" class="overlay" onclick="toggleSidebar()"></div>

    <div class="flex">

        <!-- SIDEBAR -->
        <aside id="sidebar" class="sidebar flex-shrink-0">
            <div class="p-5 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-500 rounded-xl flex items-center justify-center text-white font-bold text-xl">H</div>
                    <div>
                        <span class="text-white font-bold text-lg">Hadirin</span>
                        <span class="text-purple-300 text-xs block">Administrator</span>
                    </div>
                </div>
            </div>

            <div class="p-4 mx-3 mt-3 bg-white/5 rounded-xl border border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-500/30 rounded-full flex items-center justify-center text-white font-bold">A</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-semibold truncate">Admin</p>
                        <p class="text-purple-300 text-xs truncate">Super Admin</p>
                    </div>
                </div>
            </div>

            <nav class="p-4">
                <div class="sidebar-section-title">Dashboard</div>
                <a class="sidebar-link active" onclick="showSection('dashboard', this)">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <div class="sidebar-section-title">Manajemen</div>
                <a class="sidebar-link" onclick="showSection('presensi', this)">
                    <i class="fas fa-clipboard-list"></i> Data Presensi
                </a>
                <a class="sidebar-link" onclick="showSection('siswa', this)">
                    <i class="fas fa-user-graduate"></i> Data Siswa
                </a>
                <a class="sidebar-link" onclick="showSection('guru', this)">
                    <i class="fas fa-chalkboard-teacher"></i> Data Guru
                </a>
                <a class="sidebar-link" onclick="showSection('kelas', this)">
                    <i class="fas fa-school"></i> Data Kelas
                </a>
                <div class="sidebar-section-title">Sistem</div>
                <a class="sidebar-link" onclick="showSection('laporan', this)">
                    <i class="fas fa-file-alt"></i> Rekap & Laporan
                </a>
                <a class="sidebar-link" onclick="showSection('sistem', this)">
                    <i class="fas fa-cog"></i> Pengaturan
                </a>
                <div class="sidebar-section-title">Akun</div>
                <a href="{{ route('beranda') }}" class="sidebar-link">
                    <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                </a>
                <a onclick="logout()" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <div class="main-content">

            <!-- TOP BAR -->
            <header class="bg-white/80 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-40">
                <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button onclick="toggleSidebar()" class="toggle-sidebar text-slate-600 hover:text-purple-600 p-2 rounded-lg hover:bg-slate-100">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div>
                            <nav class="text-xs text-slate-400 mb-1">
                                Home <i class="fas fa-chevron-right text-[8px] mx-1"></i> 
                                <span class="text-purple-600 font-medium" id="breadcrumb">Dashboard</span>
                            </nav>
                            <h2 class="text-lg font-bold text-slate-800" id="pageTitle">Dashboard Admin</h2>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-xs text-slate-500 hidden sm:flex items-center gap-1">
                            <span class="w-2 h-2 bg-emerald-400 rounded-full live-dot"></span> Live
                        </span>
                        <span class="text-sm text-slate-600 hidden md:flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-purple-500"></i>
                            <span id="currentDate">-</span>
                        </span>
                        <div class="flex items-center gap-2 cursor-pointer hover:bg-slate-100 px-2 py-1 rounded-lg transition">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-bold text-sm">A</div>
                            <span class="text-sm text-slate-700 font-medium hidden sm:block">Admin</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- ============================================================ -->
            <!-- SECTION: DASHBOARD -->
            <!-- ============================================================ -->
            <div id="section-dashboard" class="section-content active p-4 sm:p-6 lg:p-8">

                <!-- WELCOME BANNER -->
                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-6 mb-6 text-white relative overflow-hidden">
                    <div class="absolute right-0 top-0 opacity-10">
                        <i class="fas fa-shield-alt text-9xl"></i>
                    </div>
                    <div class="relative z-10">
                        <p class="text-purple-100 text-xs mb-1">Selamat datang kembali,</p>
                        <h2 class="text-2xl font-bold mb-2">Admin Hadirin! 👋</h2>
                        <p class="text-sm text-purple-100/90 max-w-2xl">
                            Berikut ringkasan sistem absensi digital hari ini. Semua data terupdate secara real-time.
                        </p>
                        <div class="flex flex-wrap gap-2 mt-4">
                            <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs">
                                <i class="fas fa-check-circle mr-1"></i> Sistem Normal
                            </span>
                            <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs">
                                <i class="fas fa-clock mr-1"></i> <span id="clockTime">--:--:--</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- STAT CARDS DENGAN MINI CHART -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-users text-purple-600 text-sm"></i>
                                    </div>
                                    <p class="text-xs text-slate-500 font-medium">Total Siswa</p>
                                </div>
                                <p class="text-2xl font-bold text-slate-800">1,234</p>
                                <p class="text-[10px] text-emerald-500 mt-1"><i class="fas fa-arrow-up"></i> 12% dari bulan lalu</p>
                            </div>
                            <div class="mini-chart"><canvas id="miniChart1"></canvas></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user-check text-emerald-600 text-sm"></i>
                                    </div>
                                    <p class="text-xs text-slate-500 font-medium">Hadir Hari Ini</p>
                                </div>
                                <p class="text-2xl font-bold text-slate-800">1,152</p>
                                <p class="text-[10px] text-emerald-500 mt-1"><i class="fas fa-arrow-up"></i> 5% dari kemarin</p>
                            </div>
                            <div class="mini-chart"><canvas id="miniChart2"></canvas></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-file-medical text-amber-600 text-sm"></i>
                                    </div>
                                    <p class="text-xs text-slate-500 font-medium">Izin / Sakit</p>
                                </div>
                                <p class="text-2xl font-bold text-slate-800">62</p>
                                <p class="text-[10px] text-rose-500 mt-1"><i class="fas fa-arrow-down"></i> 3% dari kemarin</p>
                            </div>
                            <div class="mini-chart"><canvas id="miniChart3"></canvas></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-exclamation-triangle text-rose-600 text-sm"></i>
                                    </div>
                                    <p class="text-xs text-slate-500 font-medium">Alpha</p>
                                </div>
                                <p class="text-2xl font-bold text-slate-800">20</p>
                                <p class="text-[10px] text-amber-500 mt-1"><i class="fas fa-arrow-up"></i> 2% dari kemarin</p>
                            </div>
                            <div class="mini-chart"><canvas id="miniChart4"></canvas></div>
                        </div>
                    </div>
                </div>

                <!-- QUICK ACTIONS -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6 mb-6">
                    <h3 class="font-semibold text-slate-800 mb-4">
                        <i class="fas fa-bolt text-amber-500 mr-2"></i> Aksi Cepat
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <button onclick="showSection('siswa', document.querySelector('[onclick*=siswa]'))" class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-4 text-center hover:shadow-lg hover:scale-105 transition">
                            <i class="fas fa-user-plus text-2xl mb-2"></i>
                            <p class="text-xs font-medium">Tambah Siswa</p>
                        </button>
                        <button onclick="showSection('guru', document.querySelector('[onclick*=guru]'))" class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-xl p-4 text-center hover:shadow-lg hover:scale-105 transition">
                            <i class="fas fa-chalkboard-teacher text-2xl mb-2"></i>
                            <p class="text-xs font-medium">Tambah Guru</p>
                        </button>
                        <button onclick="showSection('laporan', document.querySelector('[onclick*=laporan]'))" class="bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-xl p-4 text-center hover:shadow-lg hover:scale-105 transition">
                            <i class="fas fa-file-export text-2xl mb-2"></i>
                            <p class="text-xs font-medium">Export Data</p>
                        </button>
                        <button onclick="showSection('presensi', document.querySelector('[onclick*=presensi]'))" class="bg-gradient-to-br from-blue-500 to-indigo-600 text-white rounded-xl p-4 text-center hover:shadow-lg hover:scale-105 transition">
                            <i class="fas fa-clipboard-check text-2xl mb-2"></i>
                            <p class="text-xs font-medium">Lihat Presensi</p>
                        </button>
                    </div>
                </div>

                <!-- GRAFIK UTAMA -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-slate-800">
                                <i class="fas fa-chart-bar text-purple-500 mr-2"></i> Kehadiran per Kelas
                            </h3>
                            <select class="text-xs border border-slate-200 rounded-lg px-2 py-1 bg-slate-50">
                                <option>Bulan Ini</option>
                                <option>Bulan Lalu</option>
                            </select>
                        </div>
                        <div class="chart-container">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-slate-800">
                                <i class="fas fa-chart-line text-purple-500 mr-2"></i> Tren Kehadiran
                            </h3>
                            <span class="text-xs bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full inline-block"></span> Real-Time
                            </span>
                        </div>
                        <div class="chart-container">
                            <canvas id="lineChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- PIE + DOUGHNUT + RINGKASAN -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                        <h3 class="font-semibold text-slate-800 mb-4">
                            <i class="fas fa-chart-pie text-purple-500 mr-2"></i> Status
                        </h3>
                        <div class="chart-container" style="height: 200px;">
                            <canvas id="pieChart"></canvas>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                            <div class="flex items-center gap-1"><span class="w-2 h-2 bg-emerald-500 rounded-full"></span> Hadir</div>
                            <div class="flex items-center gap-1"><span class="w-2 h-2 bg-amber-400 rounded-full"></span> Izin</div>
                            <div class="flex items-center gap-1"><span class="w-2 h-2 bg-red-400 rounded-full"></span> Sakit</div>
                            <div class="flex items-center gap-1"><span class="w-2 h-2 bg-gray-400 rounded-full"></span> Alpha</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                        <h3 class="font-semibold text-slate-800 mb-4">
                            <i class="fas fa-fingerprint text-purple-500 mr-2"></i> Metode
                        </h3>
                        <div class="chart-container" style="height: 200px;">
                            <canvas id="doughnutChart"></canvas>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                            <div class="flex items-center gap-1"><span class="w-2 h-2 bg-purple-500 rounded-full"></span> Scan QR</div>
                            <div class="flex items-center gap-1"><span class="w-2 h-2 bg-blue-400 rounded-full"></span> ID Unik</div>
                            <div class="flex items-center gap-1 col-span-2"><span class="w-2 h-2 bg-amber-400 rounded-full"></span> Izin/Sakit</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                        <h3 class="font-semibold text-slate-800 mb-4">
                            <i class="fas fa-trophy text-amber-500 mr-2"></i> Kelas Terbaik
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 p-2 bg-amber-50 rounded-lg">
                                <div class="w-8 h-8 bg-amber-500 text-white rounded-full flex items-center justify-center font-bold text-xs">1</div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-slate-800">XII.RPL</p>
                                    <p class="text-[10px] text-slate-500">Kehadiran 96%</p>
                                </div>
                                <i class="fas fa-crown text-amber-500"></i>
                            </div>
                            <div class="flex items-center gap-3 p-2 bg-slate-50 rounded-lg">
                                <div class="w-8 h-8 bg-slate-400 text-white rounded-full flex items-center justify-center font-bold text-xs">2</div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-slate-800">XI.RPL</p>
                                    <p class="text-[10px] text-slate-500">Kehadiran 92%</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-2 bg-slate-50 rounded-lg">
                                <div class="w-8 h-8 bg-amber-700 text-white rounded-full flex items-center justify-center font-bold text-xs">3</div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-slate-800">XII.TKJ</p>
                                    <p class="text-[10px] text-slate-500">Kehadiran 88%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RECENT ACTIVITY + TOP SISWA -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-slate-800">
                                <i class="fas fa-history text-purple-500 mr-2"></i> Aktivitas Terbaru
                            </h3>
                            <span class="text-xs bg-purple-100 text-purple-600 px-2 py-0.5 rounded-full">
                                <span class="w-1.5 h-1.5 bg-purple-500 rounded-full inline-block live-dot"></span> Live
                            </span>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-start gap-3 pb-3 border-b border-slate-100">
                                <div class="avatar-circle bg-purple-100 text-purple-600">N</div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-800">Najla Mutia</p>
                                    <p class="text-xs text-slate-500">Absen masuk via <span class="text-purple-600 font-medium">Scan QR</span></p>
                                </div>
                                <span class="text-[10px] text-slate-400">2 mnt lalu</span>
                            </div>
                            <div class="flex items-start gap-3 pb-3 border-b border-slate-100">
                                <div class="avatar-circle bg-emerald-100 text-emerald-600">Y</div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-800">Yasmin</p>
                                    <p class="text-xs text-slate-500">Absen masuk via <span class="text-emerald-600 font-medium">ID Unik</span></p>
                                </div>
                                <span class="text-[10px] text-slate-400">5 mnt lalu</span>
                            </div>
                            <div class="flex items-start gap-3 pb-3 border-b border-slate-100">
                                <div class="avatar-circle bg-amber-100 text-amber-600">D</div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-800">Dina Fitria</p>
                                    <p class="text-xs text-slate-500">Mengajukan <span class="text-amber-600 font-medium">Izin</span></p>
                                </div>
                                <span class="text-[10px] text-slate-400">12 mnt lalu</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="avatar-circle bg-red-100 text-red-600">A</div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-800">Arjuna Wijaya</p>
                                    <p class="text-xs text-slate-500">Mengajukan <span class="text-red-600 font-medium">Sakit</span></p>
                                </div>
                                <span class="text-[10px] text-slate-400">20 mnt lalu</span>
                            </div>
                        </div>
                    </div>

                    <!-- Top 5 Siswa -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-slate-800">
                                <i class="fas fa-star text-amber-500 mr-2"></i> Top 5 Siswa Terajin
                            </h3>
                            <span class="text-xs text-slate-400">Bulan Ini</span>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-amber-500 w-6">#1</span>
                                <div class="avatar-circle bg-indigo-100 text-indigo-600">N</div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-800">Najla Mutia</p>
                                    <p class="text-xs text-slate-500">XII.RPL</p>
                                </div>
                                <span class="text-xs font-bold text-emerald-600">100%</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-slate-400 w-6">#2</span>
                                <div class="avatar-circle bg-emerald-100 text-emerald-600">Y</div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-800">Yasmin</p>
                                    <p class="text-xs text-slate-500">XII.RPL</p>
                                </div>
                                <span class="text-xs font-bold text-emerald-600">98%</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-amber-700 w-6">#3</span>
                                <div class="avatar-circle bg-amber-100 text-amber-600">A</div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-800">Alex Pratama</p>
                                    <p class="text-xs text-slate-500">XII.RPL</p>
                                </div>
                                <span class="text-xs font-bold text-emerald-600">97%</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-slate-400 w-6">#4</span>
                                <div class="avatar-circle bg-purple-100 text-purple-600">D</div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-800">Dina Fitria</p>
                                    <p class="text-xs text-slate-500">XII.RPL</p>
                                </div>
                                <span class="text-xs font-bold text-emerald-600">95%</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-slate-400 w-6">#5</span>
                                <div class="avatar-circle bg-rose-100 text-rose-600">R</div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-800">Alex</p>
                                    <p class="text-xs text-slate-500">XII.TKJ</p>
                                </div>
                                <span class="text-xs font-bold text-emerald-600">94%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PROGRESS KELAS -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6 mb-6">
                    <h3 class="font-semibold text-slate-800 mb-4">
                        <i class="fas fa-tasks text-purple-500 mr-2"></i> Progres Kehadiran per Kelas
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-slate-700">XII.RPL</span>
                                <span class="text-emerald-600 font-semibold">96%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill bg-emerald-500" style="width: 96%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-slate-700">XI.RPL</span>
                                <span class="text-emerald-600 font-semibold">92%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill bg-emerald-500" style="width: 92%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-slate-700">XII.TKJ</span>
                                <span class="text-amber-600 font-semibold">88%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill bg-amber-500" style="width: 88%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-slate-700">XI.TKJ</span>
                                <span class="text-amber-600 font-semibold">85%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill bg-amber-500" style="width: 85%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RINGKASAN PER KELAS (TABEL) -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="px-6 py-4 border-b bg-slate-50/50">
                        <h3 class="font-semibold text-slate-800">
                            <i class="fas fa-table text-purple-500 mr-2"></i> Ringkasan Per Kelas
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50/80 border-b">
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Kelas</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Hadir</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Izin</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Sakit</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Alpha</th>
<th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-row-hover border-b">
                                    <td class="px-6 py-4 font-medium text-slate-800">XII.RPL</td>
                                    <td class="px-6 py-4 text-center">32</td>
                                    <td class="px-6 py-4 text-center text-emerald-600 font-semibold">28</td>
                                    <td class="px-6 py-4 text-center text-amber-600">2</td>
                                    <td class="px-6 py-4 text-center text-red-500">1</td>
                                    <td class="px-6 py-4 text-center text-gray-500">1</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">88%</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button class="btn-edit text-xs"><i class="fas fa-eye"></i> Lihat</button>
                                    </td>
                                </tr>
                                <tr class="table-row-hover border-b">
                                    <td class="px-6 py-4 font-medium text-slate-800">XII.TKJ</td>
                                    <td class="px-6 py-4 text-center">30</td>
                                    <td class="px-6 py-4 text-center text-emerald-600 font-semibold">25</td>
                                    <td class="px-6 py-4 text-center text-amber-600">3</td>
                                    <td class="px-6 py-4 text-center text-red-500">1</td>
                                    <td class="px-6 py-4 text-center text-gray-500">1</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">83%</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button class="btn-edit text-xs"><i class="fas fa-eye"></i> Lihat</button>
                                    </td>
                                </tr>
                                <tr class="table-row-hover border-b">
                                    <td class="px-6 py-4 font-medium text-slate-800">XI.RPL</td>
                                    <td class="px-6 py-4 text-center">35</td>
                                    <td class="px-6 py-4 text-center text-emerald-600 font-semibold">30</td>
                                    <td class="px-6 py-4 text-center text-amber-600">2</td>
                                    <td class="px-6 py-4 text-center text-red-500">2</td>
                                    <td class="px-6 py-4 text-center text-gray-500">1</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">86%</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button class="btn-edit text-xs"><i class="fas fa-eye"></i> Lihat</button>
                                    </td>
                                </tr>
                                <tr class="table-row-hover">
                                    <td class="px-6 py-4 font-medium text-slate-800">XI.TKJ</td>
                                    <td class="px-6 py-4 text-center">28</td>
                                    <td class="px-6 py-4 text-center text-emerald-600 font-semibold">24</td>
                                    <td class="px-6 py-4 text-center text-amber-600">2</td>
                                    <td class="px-6 py-4 text-center text-red-500">1</td>
                                    <td class="px-6 py-4 text-center text-gray-500">1</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">86%</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button class="btn-edit text-xs"><i class="fas fa-eye"></i> Lihat</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

<!-- SECTION: DATA PRESENSI -->
<div id="section-presensi" class="section-content p-4 sm:p-6 lg:p-8">

    <!-- HEADER PRESENSI -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-6 mb-6 text-white relative overflow-hidden">
        <div class="absolute right-0 top-0 opacity-10">
            <i class="fas fa-clipboard-check text-9xl"></i>
        </div>
        <div class="relative z-10">
            <p class="text-purple-100 text-xs mb-1">Manajemen Kehadiran</p>
            <h2 class="text-2xl font-bold mb-2">Data Presensi Siswa</h2>
            <p class="text-sm text-purple-100/90 max-w-2xl">
                Kelola dan pantau kehadiran siswa secara real-time. Filter berdasarkan tanggal, kelas, dan status kehadiran.
            </p>
            <div class="flex flex-wrap gap-2 mt-4">
                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs">
                    <i class="fas fa-calendar-day mr-1"></i> <span id="presensiDate">-</span>
                </span>
                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs">
                    <i class="fas fa-database mr-1"></i> <span id="presensiTotal">0</span> Data
                </span>
            </div>
        </div>
    </div>

    <!-- STAT CARDS PRESENSI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-check text-emerald-600 text-sm"></i>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Hadir</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-800" id="statHadir">0</p>
                    <p class="text-[10px] text-emerald-500 mt-1"><i class="fas fa-arrow-up"></i> <span id="pctHadir">0%</span> dari total</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-medical text-amber-600 text-sm"></i>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Izin</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-800" id="statIzin">0</p>
                    <p class="text-[10px] text-amber-500 mt-1"><i class="fas fa-arrow-right"></i> <span id="pctIzin">0%</span> dari total</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-envelope-open-text text-amber-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-notes-medical text-rose-600 text-sm"></i>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Sakit</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-800" id="statSakit">0</p>
                    <p class="text-[10px] text-rose-500 mt-1"><i class="fas fa-arrow-right"></i> <span id="pctSakit">0%</span> dari total</p>
                </div>
                <div class="w-12 h-12 bg-rose-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-heartbeat text-rose-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-times text-slate-600 text-sm"></i>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Alpha</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-800" id="statAlpha">0</p>
                    <p class="text-[10px] text-slate-500 mt-1"><i class="fas fa-arrow-down"></i> <span id="pctAlpha">0%</span> dari total</p>
                </div>
                <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-slash text-slate-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER & SEARCH -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-2">
                <i class="fas fa-filter text-purple-500"></i>
                <h3 class="font-semibold text-slate-800">Filter Data</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 flex-1 lg:max-w-4xl">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" id="searchPresensi" placeholder="Cari nama siswa..."
                        class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none bg-slate-50" />
                </div>
                <select id="filterKelas" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none bg-slate-50">
                    <option value="">Semua Kelas</option>
                    <option value="XII.RPL">XII.RPL</option>
                    <option value="XII.TKJ">XII.TKJ</option>
                    <option value="XI.RPL">XI.RPL</option>
                    <option value="XI.TKJ">XI.TKJ</option>
                </select>
                <select id="filterStatus" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none bg-slate-50">
                    <option value="">Semua Status</option>
                    <option value="Hadir">Hadir</option>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Alpha">Alpha</option>
                </select>
                <input type="date" id="filterTanggal"
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none bg-slate-50" />
            </div>
            <div class="flex items-center gap-2">
                <button onclick="resetFilterPresensi()" class="px-3 py-2 text-xs bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition">
                    <i class="fas fa-undo mr-1"></i> Reset
                </button>
                <button onclick="exportPresensi()" class="px-3 py-2 text-xs bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-lg hover:shadow-lg transition">
                    <i class="fas fa-file-export mr-1"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- TABEL PRESENSI -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b bg-slate-50/50 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">
                <i class="fas fa-table text-purple-500 mr-2"></i> Daftar Presensi
            </h3>
            <span class="text-xs text-slate-500" id="tableInfo">Menampilkan 0 data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50/80 border-b">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Siswa</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Kelas</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Jam Masuk</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Metode</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody id="presensiTableBody">
                    <!-- Data akan diisi via JavaScript -->
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span class="text-xs text-slate-500" id="paginationInfo">Halaman 1 dari 1</span>
            <div class="flex items-center gap-1">
                <button onclick="changePagePresensi(-1)" id="btnPrevPage" class="px-3 py-1 text-xs bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i> Prev
                </button>
                <div id="paginationNumbers" class="flex items-center gap-1"></div>
                <button onclick="changePagePresensi(1)" id="btnNextPage" class="px-3 py-1 text-xs bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- QUICK ACTIONS PRESENSI -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <button onclick="showSection('siswa', document.querySelector('[onclick*=siswa]'))" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 text-left hover:shadow-md hover:border-purple-200 transition group">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center group-hover:bg-purple-500 transition">
                    <i class="fas fa-user-graduate text-purple-600 group-hover:text-white text-lg transition"></i>
                </div>
                <div>
                    <p class="font-semibold text-slate-800 text-sm">Data Siswa</p>
                    <p class="text-xs text-slate-500">Kelola data siswa</p>
                </div>
                <i class="fas fa-arrow-right text-slate-300 ml-auto group-hover:text-purple-500 transition"></i>
            </div>
        </button>
        <button onclick="showSection('kelas', document.querySelector('[onclick*=kelas]'))" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 text-left hover:shadow-md hover:border-emerald-200 transition group">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center group-hover:bg-emerald-500 transition">
                    <i class="fas fa-school text-emerald-600 group-hover:text-white text-lg transition"></i>
                </div>
                <div>
                    <p class="font-semibold text-slate-800 text-sm">Data Kelas</p>
                    <p class="text-xs text-slate-500">Lihat daftar kelas</p>
                </div>
                <i class="fas fa-arrow-right text-slate-300 ml-auto group-hover:text-emerald-500 transition"></i>
            </div>
        </button>
        <button onclick="showSection('laporan', document.querySelector('[onclick*=laporan]'))" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 text-left hover:shadow-md hover:border-amber-200 transition group">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center group-hover:bg-amber-500 transition">
                    <i class="fas fa-file-alt text-amber-600 group-hover:text-white text-lg transition"></i>
                </div>
                <div>
                    <p class="font-semibold text-slate-800 text-sm">Rekap & Laporan</p>
                    <p class="text-xs text-slate-500">Lihat laporan lengkap</p>
                </div>
                <i class="fas fa-arrow-right text-slate-300 ml-auto group-hover:text-amber-500 transition"></i>
            </div>
        </button>
    </div>
</div>

<!-- SECTION: DATA SISWA -->
<div id="section-siswa" class="section-content p-4 sm:p-6 lg:p-8">

    <!-- HEADER SISWA -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-6 mb-6 text-white relative overflow-hidden">
        <div class="absolute right-0 top-0 opacity-10">
            <i class="fas fa-user-graduate text-9xl"></i>
        </div>
        <div class="relative z-10">
            <p class="text-purple-100 text-xs mb-1">Manajemen Data</p>
            <h2 class="text-2xl font-bold mb-2">Data Siswa</h2>
            <p class="text-sm text-purple-100/90 max-w-2xl">
                Kelola data siswa, tambah siswa baru, edit informasi, dan pantau status kehadiran mereka.
            </p>
            <div class="flex flex-wrap gap-2 mt-4">
                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs">
                    <i class="fas fa-users mr-1"></i> <span id="siswaTotal">0</span> Siswa
                </span>
                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs">
                    <i class="fas fa-school mr-1"></i> <span id="siswaKelas">0</span> Kelas
                </span>
            </div>
        </div>
    </div>

    <!-- STAT CARDS SISWA -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-graduate text-purple-600 text-sm"></i>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Total Siswa</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-800" id="statTotalSiswa">0</p>
                    <p class="text-[10px] text-emerald-500 mt-1"><i class="fas fa-arrow-up"></i> Aktif semua</p>
                </div>
                <div class="w-12 h-12 bg-purple-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-purple-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-mars text-blue-600 text-sm"></i>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Laki-laki</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-800" id="statLaki">0</p>
                    <p class="text-[10px] text-blue-500 mt-1"><span id="pctLaki">0%</span> dari total</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-male text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-8 bg-pink-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-venus text-pink-600 text-sm"></i>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Perempuan</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-800" id="statPerempuan">0</p>
                    <p class="text-[10px] text-pink-500 mt-1"><span id="pctPerempuan">0%</span> dari total</p>
                </div>
                <div class="w-12 h-12 bg-pink-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-female text-pink-500 text-xl"></i>
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
                        <p class="text-xs text-slate-500 font-medium">Kehadiran</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-800" id="statKehadiranSiswa">0%</p>
                    <p class="text-[10px] text-emerald-500 mt-1"><i class="fas fa-arrow-up"></i> Rata-rata</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-emerald-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- TOOLBAR: SEARCH, FILTER, TAMBAH -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-2">
                <i class="fas fa-filter text-purple-500"></i>
                <h3 class="font-semibold text-slate-800">Filter & Pencarian</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-1 lg:max-w-3xl">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" id="searchSiswa" placeholder="Cari nama / NIS..."
                        class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none bg-slate-50" />
                </div>
                <select id="filterKelasSiswa" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none bg-slate-50">
                    <option value="">Semua Kelas</option>
                    <option value="XII.RPL">XII.RPL</option>
                    <option value="XII.TKJ">XII.TKJ</option>
                    <option value="XI.RPL">XI.RPL</option>
                    <option value="XI.TKJ">XI.TKJ</option>
                </select>
                <select id="filterGender" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none bg-slate-50">
                    <option value="">Semua Gender</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="resetFilterSiswa()" class="px-3 py-2 text-xs bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition">
                    <i class="fas fa-undo mr-1"></i> Reset
                </button>
                <button onclick="exportSiswa()" class="px-3 py-2 text-xs bg-white border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-100 transition">
                    <i class="fas fa-file-export mr-1"></i> Export
                </button>
                <button onclick="openModalSiswa('add')" class="px-3 py-2 text-xs bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-lg hover:shadow-lg transition">
                    <i class="fas fa-user-plus mr-1"></i> Tambah Siswa
                </button>
            </div>
        </div>
    </div>

    <!-- GRID CARD SISWA -->
    <div id="siswaGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
        <!-- Data akan diisi via JavaScript -->
    </div>

    <!-- TABEL SISWA -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b bg-slate-50/50 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">
                <i class="fas fa-table text-purple-500 mr-2"></i> Daftar Siswa
            </h3>
            <span class="text-xs text-slate-500" id="siswaTableInfo">Menampilkan 0 data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50/80 border-b">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Siswa</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">NIS</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Kelas</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Gender</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Kehadiran</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody id="siswaTableBody">
                    <!-- Data akan diisi via JavaScript -->
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span class="text-xs text-slate-500" id="siswaPaginationInfo">Halaman 1 dari 1</span>
            <div class="flex items-center gap-1">
                <button onclick="changePageSiswa(-1)" id="btnPrevSiswa" class="px-3 py-1 text-xs bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i> Prev
                </button>
                <div id="siswaPaginationNumbers" class="flex items-center gap-1"></div>
                <button onclick="changePageSiswa(1)" id="btnNextSiswa" class="px-3 py-1 text-xs bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- QUICK ACTIONS SISWA -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <button onclick="showSection('presensi', document.querySelector('[onclick*=presensi]'))" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 text-left hover:shadow-md hover:border-purple-200 transition group">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center group-hover:bg-purple-500 transition">
                    <i class="fas fa-clipboard-list text-purple-600 group-hover:text-white text-lg transition"></i>
                </div>
                <div>
                    <p class="font-semibold text-slate-800 text-sm">Data Presensi</p>
                    <p class="text-xs text-slate-500">Lihat kehadiran</p>
                </div>
                <i class="fas fa-arrow-right text-slate-300 ml-auto group-hover:text-purple-500 transition"></i>
            </div>
        </button>
        <button onclick="showSection('kelas', document.querySelector('[onclick*=kelas]'))" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 text-left hover:shadow-md hover:border-emerald-200 transition group">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center group-hover:bg-emerald-500 transition">
                    <i class="fas fa-school text-emerald-600 group-hover:text-white text-lg transition"></i>
                </div>
                <div>
                    <p class="font-semibold text-slate-800 text-sm">Data Kelas</p>
                    <p class="text-xs text-slate-500">Kelola kelas</p>
                </div>
                <i class="fas fa-arrow-right text-slate-300 ml-auto group-hover:text-emerald-500 transition"></i>
            </div>
        </button>
        <button onclick="showSection('laporan', document.querySelector('[onclick*=laporan]'))" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 text-left hover:shadow-md hover:border-amber-200 transition group">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center group-hover:bg-amber-500 transition">
                    <i class="fas fa-file-alt text-amber-600 group-hover:text-white text-lg transition"></i>
                </div>
                <div>
                    <p class="font-semibold text-slate-800 text-sm">Rekap & Laporan</p>
                    <p class="text-xs text-slate-500">Laporan lengkap</p>
                </div>
                <i class="fas fa-arrow-right text-slate-300 ml-auto group-hover:text-amber-500 transition"></i>
            </div>
        </button>
    </div>
</div>

<!-- MODAL SISWA -->
<div id="modalSiswa" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background: rgba(0,0,0,0.5);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b bg-gradient-to-r from-purple-600 to-indigo-600 rounded-t-2xl flex items-center justify-between">
            <h3 class="font-semibold text-white" id="modalSiswaTitle">
                <i class="fas fa-user-plus mr-2"></i> Tambah Siswa
            </h3>
            <button onclick="closeModalSiswa()" class="text-white/80 hover:text-white transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formSiswa" class="p-6 space-y-4" onsubmit="submitFormSiswa(event)">
            <input type="hidden" id="siswaId" />
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" id="siswaNama" required
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">NIS <span class="text-red-500">*</span></label>
                    <input type="text" id="siswaNis" required
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Gender <span class="text-red-500">*</span></label>
                    <select id="siswaGender" required
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none bg-white">
                        <option value="">Pilih</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Kelas <span class="text-red-500">*</span></label>
                    <select id="siswaKelasInput" required
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none bg-white">
                        <option value="">Pilih</option>
                        <option value="XII.RPL">XII.RPL</option>
                        <option value="XII.TKJ">XII.TKJ</option>
                        <option value="XI.RPL">XI.RPL</option>
                        <option value="XI.TKJ">XI.TKJ</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                    <select id="siswaStatusInput"
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none bg-white">
                        <option value="Aktif">Aktif</option>
                        <option value="Non-Aktif">Non-Aktif</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                <input type="email" id="siswaEmail"
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none" />
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">No. Telepon</label>
                <input type="text" id="siswaTelepon"
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none" />
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="button" onclick="closeModalSiswa()" class="flex-1 px-4 py-2 text-sm bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition">
                    Batal
                </button>
                <button type="submit" class="flex-1 px-4 py-2 text-sm bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-lg hover:shadow-lg transition">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

            <!-- SECTION: DATA GURU -->
            <div id="section-guru" class="section-content p-4 sm:p-6 lg:p-8">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">
                        <i class="fas fa-chalkboard-teacher text-purple-500 mr-2"></i> Data Guru
                    </h3>
                    <p class="text-sm text-slate-500">Halaman Data Guru</p>
                </div>
            </div>

            <!-- SECTION: DATA KELAS -->
            <div id="section-kelas" class="section-content p-4 sm:p-6 lg:p-8">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">
                        <i class="fas fa-school text-purple-500 mr-2"></i> Data Kelas
                    </h3>
                    <p class="text-sm text-slate-500">Halaman Data Kelas</p>
                </div>
            </div>

            <!-- SECTION: REKAP & LAPORAN -->
            <div id="section-laporan" class="section-content p-4 sm:p-6 lg:p-8">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">
                        <i class="fas fa-file-alt text-purple-500 mr-2"></i> Rekap & Laporan
                    </h3>
                    <p class="text-sm text-slate-500">Halaman Rekap & Laporan</p>
                </div>
            </div>

            <!-- SECTION: PENGATURAN -->
            <div id="section-sistem" class="section-content p-4 sm:p-6 lg:p-8">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">
                        <i class="fas fa-cog text-purple-500 mr-2"></i> Pengaturan Sistem
                    </h3>
                    <p class="text-sm text-slate-500">Halaman Pengaturan Sistem</p>
                </div>
            </div>

        </div>
    </div>

    <!-- ============================================================ -->
    <!-- SCRIPT - INI YANG MEMBUAT GRAFIK MUNCUL -->
    <!-- ============================================================ -->
    <script>
        function initCharts() {
            console.log('📊 Init charts...');

            const miniData = [
                { id: 'miniChart1', color: '#a855f7', data: [40, 65, 45, 80, 55, 70, 90] },
                { id: 'miniChart2', color: '#10b981', data: [60, 45, 75, 50, 85, 60, 95] },
                { id: 'miniChart3', color: '#f59e0b', data: [80, 60, 90, 70, 85, 65, 75] },
                { id: 'miniChart4', color: '#ef4444', data: [30, 45, 25, 50, 35, 40, 30] }
            ];

            miniData.forEach(m => {
                const el = document.getElementById(m.id);
                if (el) {
                    new Chart(el, {
                        type: 'bar',
                        data: {
                            labels: ['', '', '', '', '', '', ''],
                            datasets: [{ data: m.data, backgroundColor: m.color, borderRadius: 2, barThickness: 4 }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { x: { display: false }, y: { display: false } }
                        }
                    });
                }
            });

            const barEl = document.getElementById('barChart');
            if (barEl) {
                new Chart(barEl, {
                    type: 'bar',
                    data: {
                        labels: ['XII.RPL', 'XII.TKJ', 'XI.RPL', 'XI.TKJ'],
                        datasets: [{
                            label: 'Hadir',
                            data: [28, 25, 30, 24],
                            backgroundColor: ['#a855f7', '#c084fc', '#ddd6fe', '#e9d5ff'],
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, max: 40, grid: { color: 'rgba(0,0,0,0.05)' } }, x: { grid: { display: false } } }
                    }
                });
                console.log('✅ Bar chart created');
            }

            const lineEl = document.getElementById('lineChart');
            if (lineEl) {
                new Chart(lineEl, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu'],
                        datasets: [{
                            label: 'Kehadiran',
                            data: [78, 82, 85, 80, 88, 87, 90, 92],
                            borderColor: '#a855f7',
                            backgroundColor: 'rgba(168, 85, 247, 0.15)',
                            fill: true, tension: 0.4,
                            pointBackgroundColor: '#a855f7',
                            pointBorderColor: 'white',
                            pointBorderWidth: 2, pointRadius: 5
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, max: 100, grid: { color: 'rgba(0,0,0,0.05)' } }, x: { grid: { display: false } } }
                    }
                });
                console.log('✅ Line chart created');
            }

            const pieEl = document.getElementById('pieChart');
            if (pieEl) {
                new Chart(pieEl, {
                    type: 'doughnut',
                    data: {
                        labels: ['Hadir', 'Izin', 'Sakit', 'Alpha'],
                        datasets: [{ data: [85, 8, 4, 3], backgroundColor: ['#10b981', '#fbbf24', '#ef4444', '#94a3b8'], borderColor: 'white', borderWidth: 3 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '65%' }
                });
            }

            const doughnutEl = document.getElementById('doughnutChart');
            if (doughnutEl) {
                new Chart(doughnutEl, {
                    type: 'doughnut',
                    data: {
                        labels: ['Scan QR', 'ID Unik', 'Izin/Sakit'],
                        datasets: [{ data: [60, 25, 15], backgroundColor: ['#a855f7', '#60a5fa', '#fbbf24'], borderColor: 'white', borderWidth: 3 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '65%' }
                });
            }
        }

        function showSection(nama, element) {
            document.querySelectorAll('.section-content').forEach(el => {
                el.classList.remove('active');
                el.style.display = 'none';
            });
            const target = document.getElementById('section-' + nama);
            if (target) { target.classList.add('active'); target.style.display = 'block'; }
            document.querySelectorAll('.sidebar-link').forEach(el => el.classList.remove('active'));
            if (element) element.classList.add('active');
            const titles = {
                'dashboard': 'Dashboard Admin', 'presensi': 'Data Presensi', 'siswa': 'Data Siswa',
                'guru': 'Data Guru', 'kelas': 'Data Kelas', 'laporan': 'Rekap & Laporan', 'sistem': 'Pengaturan Sistem'
            };
            const bc = document.getElementById('breadcrumb');
            const pt = document.getElementById('pageTitle');
            if (bc) bc.textContent = titles[nama] || 'Dashboard';
            if (pt) pt.textContent = titles[nama] || 'Dashboard Admin';
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            if (sidebar) sidebar.classList.toggle('open');
            if (overlay) overlay.classList.toggle('active');
        }

        function updateClock() {
            const now = new Date();
            const dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
            const cd = document.getElementById('currentDate');
            if (cd) cd.textContent = dateStr;
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
            const ct = document.getElementById('clockTime');
            if (ct) ct.textContent = timeStr;
        }

        function logout() {
            if (confirm('Yakin ingin logout?')) { window.location.href = "{{ route('login') }}"; }
        }

/* ============================================================ */
/* DATA PRESENSI - Logic                                        */
/* ============================================================ */

// Data dummy presensi (bisa diganti dengan data dari backend)
const dataPresensi = [
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

let filteredPresensi = [...dataPresensi];
let currentPagePresensi = 1;
const rowsPerPagePresensi = 8;

// Inisialisasi tanggal default
function initPresensiDate() {
    const today = new Date();
    const dateStr = today.toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
    const el = document.getElementById('presensiDate');
    if (el) el.textContent = dateStr;
}

// Render statistik presensi
function renderStatistikPresensi() {
    const total = filteredPresensi.length;
    const hadir = filteredPresensi.filter(d => d.status === 'Hadir').length;
    const izin = filteredPresensi.filter(d => d.status === 'Izin').length;
    const sakit = filteredPresensi.filter(d => d.status === 'Sakit').length;
    const alpha = filteredPresensi.filter(d => d.status === 'Alpha').length;

    const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    setTxt('statHadir', hadir);
    setTxt('statIzin', izin);
    setTxt('statSakit', sakit);
    setTxt('statAlpha', alpha);
    setTxt('presensiTotal', total);

    const pct = (n) => total > 0 ? Math.round((n / total) * 100) + '%' : '0%';
    setTxt('pctHadir', pct(hadir));
    setTxt('pctIzin', pct(izin));
    setTxt('pctSakit', pct(sakit));
    setTxt('pctAlpha', pct(alpha));
}

// Render tabel presensi
function renderTabelPresensi() {
    const tbody = document.getElementById('presensiTableBody');
    if (!tbody) return;

    const start = (currentPagePresensi - 1) * rowsPerPagePresensi;
    const end = start + rowsPerPagePresensi;
    const pageData = filteredPresensi.slice(start, end);

    const badgeClass = (status) => {
        switch (status) {
            case 'Hadir': return 'badge-hadir';
            case 'Izin': return 'badge-izin';
            case 'Sakit': return 'badge-sakit';
            case 'Alpha': return 'badge-alpha';
            default: return 'badge-alpha';
        }
    };

    const metodeBadge = (metode) => {
        if (metode === 'Scan QR') return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-purple-100 text-purple-700"><i class="fas fa-qrcode"></i> Scan QR</span>';
        if (metode === 'ID Unik') return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-700"><i class="fas fa-id-card"></i> ID Unik</span>';
        if (metode === 'Izin/Sakit') return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 text-amber-700"><i class="fas fa-file-medical"></i> Izin/Sakit</span>';
        return '<span class="text-slate-400 text-[10px]">-</span>';
    };

    if (pageData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center gap-2 text-slate-400">
                        <i class="fas fa-inbox text-4xl"></i>
                        <p class="text-sm">Tidak ada data presensi yang cocok</p>
                        <button onclick="resetFilterPresensi()" class="text-xs text-purple-600 hover:underline">Reset filter</button>
                    </div>
                </td>
            </tr>`;
    } else {
        tbody.innerHTML = pageData.map((d, i) => `
            <tr class="table-row-hover border-b">
                <td class="px-6 py-4 text-sm text-slate-500">${start + i + 1}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="avatar-circle bg-purple-100 text-purple-600">${d.nama.charAt(0)}</div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">${d.nama}</p>
                            <p class="text-[10px] text-slate-400">ID: SIS-${String(d.id).padStart(4, '0')}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">${d.kelas}</td>
                <td class="px-6 py-4 text-center text-sm text-slate-600">${formatTanggal(d.tanggal)}</td>
                <td class="px-6 py-4 text-center text-sm font-mono text-slate-700">${d.jamMasuk}</td>
                <td class="px-6 py-4 text-center"><span class="${badgeClass(d.status)}">${d.status}</span></td>
                <td class="px-6 py-4 text-center">${metodeBadge(d.metode)}</td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="detailPresensi(${d.id})" class="btn-edit text-xs" title="Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="hapusPresensi(${d.id})" class="btn-delete text-xs" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // Update info
    const info = document.getElementById('tableInfo');
    if (info) info.textContent = `Menampilkan ${pageData.length} dari ${filteredPresensi.length} data`;

    renderPaginationPresensi();
}

// Format tanggal
function formatTanggal(tgl) {
    const d = new Date(tgl);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

// Render pagination
function renderPaginationPresensi() {
    const totalPages = Math.ceil(filteredPresensi.length / rowsPerPagePresensi) || 1;
    const info = document.getElementById('paginationInfo');
    if (info) info.textContent = `Halaman ${currentPagePresensi} dari ${totalPages}`;

    const btnPrev = document.getElementById('btnPrevPage');
    const btnNext = document.getElementById('btnNextPage');
    if (btnPrev) btnPrev.disabled = currentPagePresensi <= 1;
    if (btnNext) btnNext.disabled = currentPagePresensi >= totalPages;

    const numbersEl = document.getElementById('paginationNumbers');
    if (numbersEl) {
        let html = '';
        const maxShow = 5;
        let startPage = Math.max(1, currentPagePresensi - Math.floor(maxShow / 2));
        let endPage = Math.min(totalPages, startPage + maxShow - 1);
        if (endPage - startPage + 1 < maxShow) startPage = Math.max(1, endPage - maxShow + 1);

        for (let i = startPage; i <= endPage; i++) {
            const active = i === currentPagePresensi;
            html += `<button onclick="goToPagePresensi(${i})" class="px-3 py-1 text-xs rounded-lg transition ${active ? 'bg-purple-500 text-white' : 'bg-white border border-slate-200 hover:bg-slate-100'}">${i}</button>`;
        }
        numbersEl.innerHTML = html;
    }
}

function goToPagePresensi(page) {
    currentPagePresensi = page;
    renderTabelPresensi();
}

function changePagePresensi(delta) {
    const totalPages = Math.ceil(filteredPresensi.length / rowsPerPagePresensi) || 1;
    const newPage = currentPagePresensi + delta;
    if (newPage >= 1 && newPage <= totalPages) {
        currentPagePresensi = newPage;
        renderTabelPresensi();
    }
}

// Filter presensi
function applyFilterPresensi() {
    const search = document.getElementById('searchPresensi')?.value.toLowerCase() || '';
    const kelas = document.getElementById('filterKelas')?.value || '';
    const status = document.getElementById('filterStatus')?.value || '';
    const tanggal = document.getElementById('filterTanggal')?.value || '';

    filteredPresensi = dataPresensi.filter(d => {
        const matchSearch = d.nama.toLowerCase().includes(search);
        const matchKelas = !kelas || d.kelas === kelas;
        const matchStatus = !status || d.status === status;
        const matchTanggal = !tanggal || d.tanggal === tanggal;
        return matchSearch && matchKelas && matchStatus && matchTanggal;
    });

    currentPagePresensi = 1;
    renderStatistikPresensi();
    renderTabelPresensi();
}

function resetFilterPresensi() {
    const el = (id) => document.getElementById(id);
    if (el('searchPresensi')) el('searchPresensi').value = '';
    if (el('filterKelas')) el('filterKelas').value = '';
    if (el('filterStatus')) el('filterStatus').value = '';
    if (el('filterTanggal')) el('filterTanggal').value = '';
    filteredPresensi = [...dataPresensi];
    currentPagePresensi = 1;
    renderStatistikPresensi();
    renderTabelPresensi();
}

// Detail presensi
function detailPresensi(id) {
    const d = dataPresensi.find(x => x.id === id);
    if (!d) return;
    alert(`📋 Detail Presensi\n\nNama: ${d.nama}\nKelas: ${d.kelas}\nTanggal: ${formatTanggal(d.tanggal)}\nJam Masuk: ${d.jamMasuk}\nStatus: ${d.status}\nMetode: ${d.metode}`);
}

// Hapus presensi
function hapusPresensi(id) {
    if (!confirm('Yakin ingin menghapus data presensi ini?')) return;
    const index = dataPresensi.findIndex(x => x.id === id);
    if (index > -1) {
        dataPresensi.splice(index, 1);
        applyFilterPresensi();
        alert('✅ Data presensi berhasil dihapus');
    }
}

// Export presensi
function exportPresensi() {
    let csv = 'No,Nama,Kelas,Tanggal,Jam Masuk,Status,Metode\n';
    filteredPresensi.forEach((d, i) => {
        csv += `${i + 1},${d.nama},${d.kelas},${d.tanggal},${d.jamMasuk},${d.status},${d.metode}\n`;
    });
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `presensi_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
    alert('✅ Data presensi berhasil diexport ke CSV');
}

// Inisialisasi presensi saat section dibuka
function initPresensiSection() {
    initPresensiDate();
    applyFilterPresensi();

    // Event listener untuk filter
    ['searchPresensi', 'filterKelas', 'filterStatus', 'filterTanggal'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', applyFilterPresensi);
            el.addEventListener('change', applyFilterPresensi);
        }
    });
}

/* ============================================================ */
/* DATA SISWA - Logic                                           */
/* ============================================================ */

// Data dummy siswa
let dataSiswa = [
    { id: 1, nama: 'Najla Mutia', nis: '2024001', kelas: 'XII.RPL', gender: 'P', email: 'najla@hadirin.id', telepon: '0812-1111-0001', kehadiran: 100, status: 'Aktif' },
    { id: 2, nama: 'Yasmin', nis: '2024002', kelas: 'XII.RPL', gender: 'P', email: 'yasmin@hadirin.id', telepon: '0812-1111-0002', kehadiran: 98, status: 'Aktif' },
    { id: 3, nama: 'Alex Pratama', nis: '2024003', kelas: 'XII.RPL', gender: 'L', email: 'alex@hadirin.id', telepon: '0812-1111-0003', kehadiran: 97, status: 'Aktif' },
    { id: 4, nama: 'Dina Fitria', nis: '2024004', kelas: 'XII.RPL', gender: 'P', email: 'dina@hadirin.id', telepon: '0812-1111-0004', kehadiran: 95, status: 'Aktif' },
    { id: 5, nama: 'Arjuna Wijaya', nis: '2024005', kelas: 'XII.RPL', gender: 'L', email: 'arjuna@hadirin.id', telepon: '0812-1111-0005', kehadiran: 92, status: 'Aktif' },
    { id: 6, nama: 'Rizky Ramadhan', nis: '2024006', kelas: 'XII.TKJ', gender: 'L', email: 'rizky@hadirin.id', telepon: '0812-1111-0006', kehadiran: 94, status: 'Aktif' },
    { id: 7, nama: 'Siti Nurhaliza', nis: '2024007', kelas: 'XII.TKJ', gender: 'P', email: 'siti@hadirin.id', telepon: '0812-1111-0007', kehadiran: 96, status: 'Aktif' },
    { id: 8, nama: 'Budi Santoso', nis: '2024008', kelas: 'XII.TKJ', gender: 'L', email: 'budi@hadirin.id', telepon: '0812-1111-0008', kehadiran: 88, status: 'Aktif' },
    { id: 9, nama: 'Citra Dewi', nis: '2024009', kelas: 'XI.RPL', gender: 'P', email: 'citra@hadirin.id', telepon: '0812-1111-0009', kehadiran: 98, status: 'Aktif' },
    { id: 10, nama: 'Eko Prasetyo', nis: '2024010', kelas: 'XI.RPL', gender: 'L', email: 'eko@hadirin.id', telepon: '0812-1111-0010', kehadiran: 95, status: 'Aktif' },
    { id: 11, nama: 'Fitriani', nis: '2024011', kelas: 'XI.RPL', gender: 'P', email: 'fitri@hadirin.id', telepon: '0812-1111-0011', kehadiran: 90, status: 'Aktif' },
    { id: 12, nama: 'Gilang Permana', nis: '2024012', kelas: 'XI.TKJ', gender: 'L', email: 'gilang@hadirin.id', telepon: '0812-1111-0012', kehadiran: 93, status: 'Aktif' },
    { id: 13, nama: 'Hana Salsabila', nis: '2024013', kelas: 'XI.TKJ', gender: 'P', email: 'hana@hadirin.id', telepon: '0812-1111-0013', kehadiran: 97, status: 'Aktif' },
    { id: 14, nama: 'Irfan Hakim', nis: '2024014', kelas: 'XI.TKJ', gender: 'L', email: 'irfan@hadirin.id', telepon: '0812-1111-0014', kehadiran: 89, status: 'Aktif' },
    { id: 15, nama: 'Joko Widodo', nis: '2024015', kelas: 'XII.RPL', gender: 'L', email: 'joko@hadirin.id', telepon: '0812-1111-0015', kehadiran: 91, status: 'Aktif' },
    { id: 16, nama: 'Kartika Sari', nis: '2024016', kelas: 'XII.RPL', gender: 'P', email: 'kartika@hadirin.id', telepon: '0812-1111-0016', kehadiran: 96, status: 'Aktif' },
    { id: 17, nama: 'Lukman Hakim', nis: '2024017', kelas: 'XII.TKJ', gender: 'L', email: 'lukman@hadirin.id', telepon: '0812-1111-0017', kehadiran: 85, status: 'Aktif' },
    { id: 18, nama: 'Maya Anggraini', nis: '2024018', kelas: 'XI.RPL', gender: 'P', email: 'maya@hadirin.id', telepon: '0812-1111-0018', kehadiran: 94, status: 'Aktif' },
    { id: 19, nama: 'Nanda Pratama', nis: '2024019', kelas: 'XI.TKJ', gender: 'L', email: 'nanda@hadirin.id', telepon: '0812-1111-0019', kehadiran: 92, status: 'Aktif' },
    { id: 20, nama: 'Oki Setiana', nis: '2024020', kelas: 'XII.RPL', gender: 'P', email: 'oki@hadirin.id', telepon: '0812-1111-0020', kehadiran: 97, status: 'Aktif' },
    { id: 21, nama: 'Putri Ayu', nis: '2024021', kelas: 'XI.RPL', gender: 'P', email: 'putri@hadirin.id', telepon: '0812-1111-0021', kehadiran: 91, status: 'Aktif' },
    { id: 22, nama: 'Qori Ramadhan', nis: '2024022', kelas: 'XI.TKJ', gender: 'L', email: 'qori@hadirin.id', telepon: '0812-1111-0022', kehadiran: 88, status: 'Aktif' },
    { id: 23, nama: 'Rina Wulandari', nis: '2024023', kelas: 'XII.TKJ', gender: 'P', email: 'rina@hadirin.id', telepon: '0812-1111-0023', kehadiran: 95, status: 'Aktif' },
    { id: 24, nama: 'Sandi Permana', nis: '2024024', kelas: 'XII.RPL', gender: 'L', email: 'sandi@hadirin.id', telepon: '0812-1111-0024', kehadiran: 93, status: 'Aktif' }
];

let filteredSiswa = [...dataSiswa];
let currentPageSiswa = 1;
let editingSiswaId = null;
const rowsPerPageSiswa = 8;

// Warna avatar berdasarkan nama
function avatarColorSiswa(nama) {
    const colors = [
        'bg-purple-100 text-purple-600',
        'bg-emerald-100 text-emerald-600',
        'bg-amber-100 text-amber-600',
        'bg-rose-100 text-rose-600',
        'bg-blue-100 text-blue-600',
        'bg-indigo-100 text-indigo-600',
        'bg-pink-100 text-pink-600',
        'bg-teal-100 text-teal-600'
    ];
    const idx = nama.charCodeAt(0) % colors.length;
    return colors[idx];
}

// Render statistik siswa
function renderStatistikSiswa() {
    const total = filteredSiswa.length;
    const laki = filteredSiswa.filter(s => s.gender === 'L').length;
    const perempuan = filteredSiswa.filter(s => s.gender === 'P').length;
    const totalKelas = [...new Set(dataSiswa.map(s => s.kelas))].length;
    const rataKehadiran = total > 0
        ? Math.round(filteredSiswa.reduce((a, b) => a + b.kehadiran, 0) / total)
        : 0;

    const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    setTxt('statTotalSiswa', total);
    setTxt('statLaki', laki);
    setTxt('statPerempuan', perempuan);
    setTxt('statKehadiranSiswa', rataKehadiran + '%');
    setTxt('siswaTotal', dataSiswa.length);
    setTxt('siswaKelas', totalKelas);

    const pct = (n) => total > 0 ? Math.round((n / total) * 100) + '%' : '0%';
    setTxt('pctLaki', pct(laki));
    setTxt('pctPerempuan', pct(perempuan));
}

// Render grid card siswa
function renderGridSiswa() {
    const grid = document.getElementById('siswaGrid');
    if (!grid) return;

    const start = (currentPageSiswa - 1) * rowsPerPageSiswa;
    const end = start + rowsPerPageSiswa;
    const pageData = filteredSiswa.slice(start, end);

    if (pageData.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full bg-white rounded-2xl border border-slate-200/60 p-12 text-center">
                <div class="flex flex-col items-center gap-2 text-slate-400">
                    <i class="fas fa-user-slash text-4xl"></i>
                    <p class="text-sm">Tidak ada data siswa yang cocok</p>
                    <button onclick="resetFilterSiswa()" class="text-xs text-purple-600 hover:underline">Reset filter</button>
                </div>
            </div>`;
        return;
    }

    grid.innerHTML = pageData.map(s => {
        const genderIcon = s.gender === 'L' ? 'fa-mars text-blue-500' : 'fa-venus text-pink-500';
        const genderText = s.gender === 'L' ? 'Laki-laki' : 'Perempuan';
        const statusBadge = s.status === 'Aktif'
            ? '<span class="badge-hadir">Aktif</span>'
            : '<span class="badge-alpha">Non-Aktif</span>';
        const progressColor = s.kehadiran >= 95 ? 'bg-emerald-500' : s.kehadiran >= 90 ? 'bg-amber-500' : 'bg-rose-500';

        return `
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 hover:shadow-lg hover:border-purple-200 transition group">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="avatar-circle ${avatarColorSiswa(s.nama)} w-12 h-12 text-base">${s.nama.charAt(0)}</div>
                    <div>
                        <p class="font-semibold text-slate-800 text-sm leading-tight">${s.nama}</p>
                        <p class="text-[10px] text-slate-400">NIS: ${s.nis}</p>
                    </div>
                </div>
                ${statusBadge}
            </div>

            <div class="space-y-2 text-xs mb-3">
                <div class="flex items-center gap-2 text-slate-600">
                    <i class="fas fa-school text-purple-500 w-4"></i>
                    <span>${s.kelas}</span>
                </div>
                <div class="flex items-center gap-2 text-slate-600">
                    <i class="fas ${genderIcon} w-4"></i>
                    <span>${genderText}</span>
                </div>
                <div class="flex items-center gap-2 text-slate-600 truncate">
                    <i class="fas fa-envelope text-slate-400 w-4"></i>
                    <span class="truncate">${s.email}</span>
                </div>
                <div class="flex items-center gap-2 text-slate-600">
                    <i class="fas fa-phone text-slate-400 w-4"></i>
                    <span>${s.telepon}</span>
                </div>
            </div>

            <div class="mb-3">
                <div class="flex justify-between text-[10px] mb-1">
                    <span class="text-slate-500">Kehadiran</span>
                    <span class="font-semibold text-slate-700">${s.kehadiran}%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill ${progressColor}" style="width: ${s.kehadiran}%"></div>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                <button onclick="detailSiswa(${s.id})" class="flex-1 px-2 py-1.5 text-[11px] bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition">
                    <i class="fas fa-eye mr-1"></i> Detail
                </button>
                <button onclick="openModalSiswa('edit', ${s.id})" class="flex-1 px-2 py-1.5 text-[11px] bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
                    <i class="fas fa-edit mr-1"></i> Edit
                </button>
                <button onclick="hapusSiswa(${s.id})" class="px-2 py-1.5 text-[11px] bg-red-500 hover:bg-red-600 text-white rounded-lg transition" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>`;
    }).join('');
}

// Render tabel siswa
function renderTabelSiswa() {
    const tbody = document.getElementById('siswaTableBody');
    if (!tbody) return;

    const start = (currentPageSiswa - 1) * rowsPerPageSiswa;
    const end = start + rowsPerPageSiswa;
    const pageData = filteredSiswa.slice(start, end);

    if (pageData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center gap-2 text-slate-400">
                        <i class="fas fa-inbox text-4xl"></i>
                        <p class="text-sm">Tidak ada data siswa yang cocok</p>
                        <button onclick="resetFilterSiswa()" class="text-xs text-purple-600 hover:underline">Reset filter</button>
                    </div>
                </td>
            </tr>`;
    } else {
        tbody.innerHTML = pageData.map((s, i) => {
            const genderBadge = s.gender === 'L'
                ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-700"><i class="fas fa-mars"></i> L</span>'
                : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-pink-100 text-pink-700"><i class="fas fa-venus"></i> P</span>';
            const statusBadge = s.status === 'Aktif'
                ? '<span class="badge-hadir">Aktif</span>'
                : '<span class="badge-alpha">Non-Aktif</span>';
            const progressColor = s.kehadiran >= 95 ? 'text-emerald-600' : s.kehadiran >= 90 ? 'text-amber-600' : 'text-rose-600';

            return `
            <tr class="table-row-hover border-b">
                <td class="px-6 py-4 text-sm text-slate-500">${start + i + 1}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="avatar-circle ${avatarColorSiswa(s.nama)}">${s.nama.charAt(0)}</div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">${s.nama}</p>
                            <p class="text-[10px] text-slate-400">${s.email}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm font-mono text-slate-600">${s.nis}</td>
                <td class="px-6 py-4 text-center text-sm text-slate-600">${s.kelas}</td>
                <td class="px-6 py-4 text-center">${genderBadge}</td>
                <td class="px-6 py-4 text-center">
                    <span class="text-sm font-semibold ${progressColor}">${s.kehadiran}%</span>
                </td>
                <td class="px-6 py-4 text-center">${statusBadge}</td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="detailSiswa(${s.id})" class="btn-edit text-xs" title="Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="openModalSiswa('edit', ${s.id})" class="btn-edit text-xs" title="Edit" style="background:#3b82f6;">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="hapusSiswa(${s.id})" class="btn-delete text-xs" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    const info = document.getElementById('siswaTableInfo');
    if (info) info.textContent = `Menampilkan ${pageData.length} dari ${filteredSiswa.length} data`;

    renderPaginationSiswa();
}

// Render pagination siswa
function renderPaginationSiswa() {
    const totalPages = Math.ceil(filteredSiswa.length / rowsPerPageSiswa) || 1;
    const info = document.getElementById('siswaPaginationInfo');
    if (info) info.textContent = `Halaman ${currentPageSiswa} dari ${totalPages}`;

    const btnPrev = document.getElementById('btnPrevSiswa');
    const btnNext = document.getElementById('btnNextSiswa');
    if (btnPrev) btnPrev.disabled = currentPageSiswa <= 1;
    if (btnNext) btnNext.disabled = currentPageSiswa >= totalPages;

    const numbersEl = document.getElementById('siswaPaginationNumbers');
    if (numbersEl) {
        let html = '';
        const maxShow = 5;
        let startPage = Math.max(1, currentPageSiswa - Math.floor(maxShow / 2));
        let endPage = Math.min(totalPages, startPage + maxShow - 1);
        if (endPage - startPage + 1 < maxShow) startPage = Math.max(1, endPage - maxShow + 1);

        for (let i = startPage; i <= endPage; i++) {
            const active = i === currentPageSiswa;
            html += `<button onclick="goToPageSiswa(${i})" class="px-3 py-1 text-xs rounded-lg transition ${active ? 'bg-purple-500 text-white' : 'bg-white border border-slate-200 hover:bg-slate-100'}">${i}</button>`;
        }
        numbersEl.innerHTML = html;
    }
}

function goToPageSiswa(page) {
    currentPageSiswa = page;
    renderGridSiswa();
    renderTabelSiswa();
}

function changePageSiswa(delta) {
    const totalPages = Math.ceil(filteredSiswa.length / rowsPerPageSiswa) || 1;
    const newPage = currentPageSiswa + delta;
    if (newPage >= 1 && newPage <= totalPages) {
        currentPageSiswa = newPage;
        renderGridSiswa();
        renderTabelSiswa();
    }
}

// Filter siswa
function applyFilterSiswa() {
    const search = (document.getElementById('searchSiswa')?.value || '').toLowerCase();
    const kelas = document.getElementById('filterKelasSiswa')?.value || '';
    const gender = document.getElementById('filterGender')?.value || '';

    filteredSiswa = dataSiswa.filter(s => {
        const matchSearch = s.nama.toLowerCase().includes(search) || s.nis.toLowerCase().includes(search);
        const matchKelas = !kelas || s.kelas === kelas;
        const matchGender = !gender || s.gender === gender;
        return matchSearch && matchKelas && matchGender;
    });

    currentPageSiswa = 1;
    renderStatistikSiswa();
    renderGridSiswa();
    renderTabelSiswa();
}

function resetFilterSiswa() {
    const el = (id) => document.getElementById(id);
    if (el('searchSiswa')) el('searchSiswa').value = '';
    if (el('filterKelasSiswa')) el('filterKelasSiswa').value = '';
    if (el('filterGender')) el('filterGender').value = '';
    filteredSiswa = [...dataSiswa];
    currentPageSiswa = 1;
    renderStatistikSiswa();
    renderGridSiswa();
    renderTabelSiswa();
}

// Modal tambah/edit siswa
function openModalSiswa(mode, id = null) {
    const modal = document.getElementById('modalSiswa');
    const title = document.getElementById('modalSiswaTitle');
    if (!modal) return;

    editingSiswaId = null;

    if (mode === 'edit' && id) {
        const s = dataSiswa.find(x => x.id === id);
        if (!s) return;
        editingSiswaId = id;
        if (title) title.innerHTML = '<i class="fas fa-edit mr-2"></i> Edit Siswa';
        document.getElementById('siswaId').value = s.id;
        document.getElementById('siswaNama').value = s.nama;
        document.getElementById('siswaNis').value = s.nis;
        document.getElementById('siswaGender').value = s.gender;
        document.getElementById('siswaKelasInput').value = s.kelas;
        document.getElementById('siswaStatusInput').value = s.status;
        document.getElementById('siswaEmail').value = s.email;
        document.getElementById('siswaTelepon').value = s.telepon;
    } else {
        if (title) title.innerHTML = '<i class="fas fa-user-plus mr-2"></i> Tambah Siswa';
        document.getElementById('formSiswa').reset();
        document.getElementById('siswaId').value = '';
        document.getElementById('siswaStatusInput').value = 'Aktif';
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModalSiswa() {
    const modal = document.getElementById('modalSiswa');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    editingSiswaId = null;
}

// Submit form siswa (tambah / edit)
function submitFormSiswa(event) {
    event.preventDefault();

    const nama = document.getElementById('siswaNama').value.trim();
    const nis = document.getElementById('siswaNis').value.trim();
    const gender = document.getElementById('siswaGender').value;
    const kelas = document.getElementById('siswaKelasInput').value;
    const status = document.getElementById('siswaStatusInput').value;
    const email = document.getElementById('siswaEmail').value.trim() || `${nama.toLowerCase().replace(/\s+/g, '.')}@hadirin.id`;
    const telepon = document.getElementById('siswaTelepon').value.trim() || '0812-0000-0000';

    if (!nama || !nis || !gender || !kelas) {
        alert('⚠️ Mohon lengkapi semua field yang wajib diisi!');
        return;
    }

    if (editingSiswaId) {
        // Mode edit
        const idx = dataSiswa.findIndex(x => x.id === editingSiswaId);
        if (idx > -1) {
            dataSiswa[idx] = {
                ...dataSiswa[idx],
                nama, nis, gender, kelas, status, email, telepon
            };
        }
        alert('✅ Data siswa berhasil diperbarui!');
    } else {
        // Mode tambah
        const newId = dataSiswa.length > 0 ? Math.max(...dataSiswa.map(s => s.id)) + 1 : 1;
        dataSiswa.unshift({
            id: newId,
            nama, nis, gender, kelas, status, email, telepon,
            kehadiran: 100
        });
        alert('✅ Siswa baru berhasil ditambahkan!');
    }

    closeModalSiswa();
    applyFilterSiswa();
}

// Detail siswa
function detailSiswa(id) {
    const s = dataSiswa.find(x => x.id === id);
    if (!s) return;
    const genderText = s.gender === 'L' ? 'Laki-laki' : 'Perempuan';
    alert(
        `👤 Detail Siswa\n\n` +
        `Nama      : ${s.nama}\n` +
        `NIS       : ${s.nis}\n` +
        `Kelas     : ${s.kelas}\n` +
        `Gender    : ${genderText}\n` +
        `Email     : ${s.email}\n` +
        `Telepon   : ${s.telepon}\n` +
        `Kehadiran : ${s.kehadiran}%\n` +
        `Status    : ${s.status}`
    );
}

// Hapus siswa
function hapusSiswa(id) {
    const s = dataSiswa.find(x => x.id === id);
    if (!s) return;
    if (!confirm(`Yakin ingin menghapus siswa "${s.nama}"?`)) return;
    const index = dataSiswa.findIndex(x => x.id === id);
    if (index > -1) {
        dataSiswa.splice(index, 1);
        applyFilterSiswa();
        alert('✅ Data siswa berhasil dihapus');
    }
}

// Export siswa
function exportSiswa() {
    let csv = 'No,Nama,NIS,Kelas,Gender,Email,Telepon,Kehadiran,Status\n';
    filteredSiswa.forEach((s, i) => {
        csv += `${i + 1},${s.nama},${s.nis},${s.kelas},${s.gender},${s.email},${s.telepon},${s.kehadiran}%,${s.status}\n`;
    });
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `data_siswa_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
    alert('✅ Data siswa berhasil diexport ke CSV');
}

// Inisialisasi section siswa
function initSiswaSection() {
    applyFilterSiswa();

    // Event listener untuk filter
    ['searchSiswa', 'filterKelasSiswa', 'filterGender'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', applyFilterSiswa);
            el.addEventListener('change', applyFilterSiswa);
        }
    });
}

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Dashboard Admin ready!');
            setTimeout(() => { initCharts(); }, 100);
            updateClock();
            setInterval(updateClock, 1000);
        });
    </script>

</body>
</html>