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
.rekap-tab {
    color: #64748b;
    background: transparent;
    border: none;
    cursor: pointer;
    white-space: nowrap;
}
.rekap-tab:hover {
    background: #f1f5f9;
    color: #334155;
}
.rekap-tab.active {
    background: linear-gradient(135deg, #a855f7, #6366f1);
    color: white;
    box-shadow: 0 4px 12px rgba(168, 85, 247, 0.3);
}
.rekap-content.hidden {
    display: none;
}
.sistem-tab {
    color: #64748b;
    background: transparent;
    border: none;
    cursor: pointer;
    white-space: nowrap;
}
.sistem-tab:hover {
    background: #f1f5f9;
    color: #334155;
}
.sistem-tab.active {
    background: linear-gradient(135deg, #a855f7, #6366f1);
    color: white;
    box-shadow: 0 4px 12px rgba(168, 85, 247, 0.3);
}
.sistem-content.hidden {
    display: none;
}
.tema-option {
    cursor: pointer;
    position: relative;
}
.tema-option.active {
    border-color: rgba(255,255,255,0.9) !important;
    box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.3), 0 8px 20px rgba(0,0,0,0.15);
    transform: scale(1.05);
}
@keyframes slideInRight {
    0% { transform: translateX(120%); opacity: 0; }
    100% { transform: translateX(0); opacity: 1; }
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
                <button onclick="exportPresensi()" class="px-3 py-2 text-xs bg-gradient-to-r from-emerald-500 to-indigo-600 text-white rounded-lg hover:shadow-lg transition">
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
                <button onclick="openModalSiswa('add')" class="px-3 py-2 text-xs bg-gradient-to-r from-emerald-500 to-indigo-600 text-white rounded-lg hover:shadow-lg transition">
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
        <div class="px-6 py-4 border-b bg-gradient-to-r from-emerald-600 to-indigo-600 rounded-t-2xl flex items-center justify-between">
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

    <!-- HEADER GURU -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-6 mb-6 text-white relative overflow-hidden">
        <div class="absolute right-0 top-0 opacity-10">
            <i class="fas fa-chalkboard-teacher text-9xl"></i>
        </div>
        <div class="relative z-10">
            <p class="text-purple-100 text-xs mb-1">Manajemen Data</p>
            <h2 class="text-2xl font-bold mb-2">Data Guru</h2>
            <p class="text-sm text-purple-100/90 max-w-2xl">
                Kelola data guru, tambah guru baru, edit informasi, dan pantau mata pelajaran yang diampu.
            </p>
            <div class="flex flex-wrap gap-2 mt-4">
                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs">
                    <i class="fas fa-chalkboard-teacher mr-1"></i> <span id="guruTotal">0</span> Guru
                </span>
                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs">
                    <i class="fas fa-book mr-1"></i> <span id="guruMapel">0</span> Mapel
                </span>
            </div>
        </div>
    </div>

    <!-- STAT CARDS GURU -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chalkboard-teacher text-emerald-600 text-sm"></i>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Total Guru</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-800" id="statTotalGuru">0</p>
                    <p class="text-[10px] text-emerald-500 mt-1"><i class="fas fa-arrow-up"></i> Aktif semua</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-emerald-500 text-xl"></i>
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
                    <p class="text-2xl font-bold text-slate-800" id="statLakiGuru">0</p>
                    <p class="text-[10px] text-blue-500 mt-1"><span id="pctLakiGuru">0%</span> dari total</p>
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
                    <p class="text-2xl font-bold text-slate-800" id="statPerempuanGuru">0</p>
                    <p class="text-[10px] text-pink-500 mt-1"><span id="pctPerempuanGuru">0%</span> dari total</p>
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
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-award text-amber-600 text-sm"></i>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Rata Pengalaman</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-800" id="statPengalamanGuru">0</p>
                    <p class="text-[10px] text-amber-500 mt-1"><i class="fas fa-arrow-up"></i> Tahun</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-medal text-amber-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- TOOLBAR: SEARCH, FILTER, TAMBAH -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-2">
                <i class="fas fa-filter text-emerald-500"></i>
                <h3 class="font-semibold text-slate-800">Filter & Pencarian</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-1 lg:max-w-3xl">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" id="searchGuru" placeholder="Cari nama / NIP..."
                        class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none bg-slate-50" />
                </div>
                <select id="filterMapel" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none bg-slate-50">
                    <option value="">Semua Mapel</option>
                    <option value="Matematika">Matematika</option>
                    <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                    <option value="Bahasa Inggris">Bahasa Inggris</option>
                    <option value="Fisika">Fisika</option>
                    <option value="Kimia">Kimia</option>
                    <option value="Biologi">Biologi</option>
                    <option value="Pemrograman">Pemrograman</option>
                    <option value="Jaringan">Jaringan</option>
                    <option value="Multimedia">Multimedia</option>
                    <option value="Sejarah">Sejarah</option>
                </select>
                <select id="filterGenderGuru" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none bg-slate-50">
                    <option value="">Semua Gender</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="resetFilterGuru()" class="px-3 py-2 text-xs bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition">
                    <i class="fas fa-undo mr-1"></i> Reset
                </button>
                <button onclick="exportGuru()" class="px-3 py-2 text-xs bg-white border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-100 transition">
                    <i class="fas fa-file-export mr-1"></i> Export
                </button>
                <button onclick="openModalGuru('add')" class="px-3 py-2 text-xs bg-gradient-to-r from-emerald-500 to-indigo-600 text-white rounded-lg hover:shadow-lg transition">
                    <i class="fas fa-user-plus mr-1"></i> Tambah Guru
                </button>
            </div>
        </div>
    </div>

    <!-- GRID CARD GURU -->
    <div id="guruGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
        <!-- Data akan diisi via JavaScript -->
    </div>

    <!-- TABEL GURU -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b bg-slate-50/50 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">
                <i class="fas fa-table text-emerald-500 mr-2"></i> Daftar Guru
            </h3>
            <span class="text-xs text-slate-500" id="guruTableInfo">Menampilkan 0 data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50/80 border-b">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Guru</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">NIP</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Mapel</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Gender</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Pengalaman</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody id="guruTableBody">
                    <!-- Data akan diisi via JavaScript -->
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span class="text-xs text-slate-500" id="guruPaginationInfo">Halaman 1 dari 1</span>
            <div class="flex items-center gap-1">
                <button onclick="changePageGuru(-1)" id="btnPrevGuru" class="px-3 py-1 text-xs bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i> Prev
                </button>
                <div id="guruPaginationNumbers" class="flex items-center gap-1"></div>
                <button onclick="changePageGuru(1)" id="btnNextGuru" class="px-3 py-1 text-xs bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- QUICK ACTIONS GURU -->
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

<!-- MODAL GURU -->
<div id="modalGuru" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background: rgba(0,0,0,0.5);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b bg-gradient-to-r from-emerald-600 to-teal-600 rounded-t-2xl flex items-center justify-between">
            <h3 class="font-semibold text-white" id="modalGuruTitle">
                <i class="fas fa-user-plus mr-2"></i> Tambah Guru
            </h3>
            <button onclick="closeModalGuru()" class="text-white/80 hover:text-white transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formGuru" class="p-6 space-y-4" onsubmit="submitFormGuru(event)">
            <input type="hidden" id="guruId" />
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" id="guruNama" required
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">NIP <span class="text-red-500">*</span></label>
                    <input type="text" id="guruNip" required
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Gender <span class="text-red-500">*</span></label>
                    <select id="guruGender" required
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none bg-white">
                        <option value="">Pilih</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Mata Pelajaran <span class="text-red-500">*</span></label>
                    <select id="guruMapelInput" required
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none bg-white">
                        <option value="">Pilih</option>
                        <option value="Matematika">Matematika</option>
                        <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                        <option value="Bahasa Inggris">Bahasa Inggris</option>
                        <option value="Fisika">Fisika</option>
                        <option value="Kimia">Kimia</option>
                        <option value="Biologi">Biologi</option>
                        <option value="Pemrograman">Pemrograman</option>
                        <option value="Jaringan">Jaringan</option>
                        <option value="Multimedia">Multimedia</option>
                        <option value="Sejarah">Sejarah</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Pengalaman (Tahun)</label>
                    <input type="number" id="guruPengalaman" min="0" max="50" value="1"
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Pendidikan</label>
                    <select id="guruPendidikan"
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none bg-white">
                        <option value="S1">S1</option>
                        <option value="S2">S2</option>
                        <option value="S3">S3</option>
                        <option value="D3">D3</option>
                        <option value="D4">D4</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                    <select id="guruStatusInput"
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none bg-white">
                        <option value="Aktif">Aktif</option>
                        <option value="Cuti">Cuti</option>
                        <option value="Non-Aktif">Non-Aktif</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                <input type="email" id="guruEmail"
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none" />
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">No. Telepon</label>
                <input type="text" id="guruTelepon"
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none" />
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="button" onclick="closeModalGuru()" class="flex-1 px-4 py-2 text-sm bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition">
                    Batal
                </button>
                <button type="submit" class="flex-1 px-4 py-2 text-sm bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-lg hover:shadow-lg transition">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SECTION: DATA KELAS -->
<div id="section-kelas" class="section-content p-4 sm:p-6 lg:p-8">

    <!-- HEADER KELAS -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-6 mb-6 text-white relative overflow-hidden">
        <div class="absolute right-0 top-0 opacity-10">
            <i class="fas fa-school text-9xl"></i>
        </div>
        <div class="relative z-10">
            <p class="text-purple-100 text-xs mb-1">Manajemen Data</p>
            <h2 class="text-2xl font-bold mb-2">Data Kelas</h2>
            <p class="text-sm text-purple-100/90 max-w-2xl">
                Kelola data kelas, tambah kelas baru, edit informasi, dan pantau statistik kehadiran per kelas.
            </p>
            <div class="flex flex-wrap gap-2 mt-4">
                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs">
                    <i class="fas fa-school mr-1"></i> <span id="kelasTotal">0</span> Kelas
                </span>
                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs">
                    <i class="fas fa-users mr-1"></i> <span id="kelasSiswaTotal">0</span> Siswa
                </span>
            </div>
        </div>
    </div>

    <!-- STAT CARDS KELAS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-school text-amber-600 text-sm"></i>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Total Kelas</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-800" id="statTotalKelas">0</p>
                    <p class="text-[10px] text-emerald-500 mt-1"><i class="fas fa-arrow-up"></i> Aktif semua</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-school text-amber-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-purple-600 text-sm"></i>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Total Siswa</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-800" id="statTotalSiswaKelas">0</p>
                    <p class="text-[10px] text-purple-500 mt-1"><span id="pctSiswaKelas">0</span> rata-rata/kelas</p>
                </div>
                <div class="w-12 h-12 bg-purple-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-graduate text-purple-500 text-xl"></i>
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
                    <p class="text-2xl font-bold text-slate-800" id="statKehadiranKelas">0%</p>
                    <p class="text-[10px] text-emerald-500 mt-1"><i class="fas fa-arrow-up"></i> Rata-rata</p>
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
                            <i class="fas fa-chalkboard-teacher text-blue-600 text-sm"></i>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Wali Kelas</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-800" id="statWaliKelas">0</p>
                    <p class="text-[10px] text-blue-500 mt-1"><i class="fas fa-user-tie"></i> Terisi semua</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-tie text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- TOOLBAR: SEARCH, FILTER, TAMBAH -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-2">
                <i class="fas fa-filter text-amber-500"></i>
                <h3 class="font-semibold text-slate-800">Filter & Pencarian</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-1 lg:max-w-3xl">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" id="searchKelas" placeholder="Cari nama kelas / wali..."
                        class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none bg-slate-50" />
                </div>
                <select id="filterTingkat" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none bg-slate-50">
                    <option value="">Semua Tingkat</option>
                    <option value="X">Kelas X</option>
                    <option value="XI">Kelas XI</option>
                    <option value="XII">Kelas XII</option>
                </select>
                <select id="filterJurusan" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none bg-slate-50">
                    <option value="">Semua Jurusan</option>
                    <option value="RPL">RPL</option>
                    <option value="TKJ">TKJ</option>
                    <option value="MM">MM</option>
                    <option value="AKL">AKL</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="resetFilterKelas()" class="px-3 py-2 text-xs bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition">
                    <i class="fas fa-undo mr-1"></i> Reset
                </button>
                <button onclick="exportKelas()" class="px-3 py-2 text-xs bg-white border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-100 transition">
                    <i class="fas fa-file-export mr-1"></i> Export
                </button>
                <button onclick="openModalKelas('add')" class="px-3 py-2 text-xs bg-gradient-to-r from-emerald-600 to-indigo-600 text-white rounded-lg hover:shadow-lg transition">
                    <i class="fas fa-plus mr-1"></i> Tambah Kelas
                </button>
            </div>
        </div>
    </div>

    <!-- GRID CARD KELAS -->
    <div id="kelasGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
        <!-- Data akan diisi via JavaScript -->
    </div>

    <!-- TABEL KELAS -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b bg-slate-50/50 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">
                <i class="fas fa-table text-amber-500 mr-2"></i> Daftar Kelas
            </h3>
            <span class="text-xs text-slate-500" id="kelasTableInfo">Menampilkan 0 data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50/80 border-b">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Kelas</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Wali Kelas</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Tingkat</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Jurusan</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Jumlah Siswa</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Kehadiran</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody id="kelasTableBody">
                    <!-- Data akan diisi via JavaScript -->
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span class="text-xs text-slate-500" id="kelasPaginationInfo">Halaman 1 dari 1</span>
            <div class="flex items-center gap-1">
                <button onclick="changePageKelas(-1)" id="btnPrevKelas" class="px-3 py-1 text-xs bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i> Prev
                </button>
                <div id="kelasPaginationNumbers" class="flex items-center gap-1"></div>
                <button onclick="changePageKelas(1)" id="btnNextKelas" class="px-3 py-1 text-xs bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- QUICK ACTIONS KELAS -->
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
        <button onclick="showSection('guru', document.querySelector('[onclick*=guru]'))" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 text-left hover:shadow-md hover:border-emerald-200 transition group">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center group-hover:bg-emerald-500 transition">
                    <i class="fas fa-chalkboard-teacher text-emerald-600 group-hover:text-white text-lg transition"></i>
                </div>
                <div>
                    <p class="font-semibold text-slate-800 text-sm">Data Guru</p>
                    <p class="text-xs text-slate-500">Kelola data guru</p>
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

<!-- MODAL KELAS -->
<div id="modalKelas" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background: rgba(0,0,0,0.5);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b bg-gradient-to-r from-amber-500 to-orange-600 rounded-t-2xl flex items-center justify-between">
            <h3 class="font-semibold text-white" id="modalKelasTitle">
                <i class="fas fa-plus mr-2"></i> Tambah Kelas
            </h3>
            <button onclick="closeModalKelas()" class="text-white/80 hover:text-white transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formKelas" class="p-6 space-y-4" onsubmit="submitFormKelas(event)">
            <input type="hidden" id="kelasId" />
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Nama Kelas <span class="text-red-500">*</span></label>
                <input type="text" id="kelasNamaInput" required placeholder="Contoh: XII.RPL 1"
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Tingkat <span class="text-red-500">*</span></label>
                    <select id="kelasTingkat" required
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none bg-white">
                        <option value="">Pilih</option>
                        <option value="X">X</option>
                        <option value="XI">XI</option>
                        <option value="XII">XII</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Jurusan <span class="text-red-500">*</span></label>
                    <select id="kelasJurusan" required
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none bg-white">
                        <option value="">Pilih</option>
                        <option value="RPL">RPL</option>
                        <option value="TKJ">TKJ</option>
                        <option value="MM">MM</option>
                        <option value="AKL">AKL</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Wali Kelas <span class="text-red-500">*</span></label>
                <select id="kelasWali" required
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none bg-white">
                    <option value="">Pilih Wali Kelas</option>
                    <option value="Budi Hartono, S.Pd">Budi Hartono, S.Pd</option>
                    <option value="Siti Aminah, M.Pd">Siti Aminah, M.Pd</option>
                    <option value="Ahmad Fauzi, S.Pd">Ahmad Fauzi, S.Pd</option>
                    <option value="Dewi Lestari, S.Si">Dewi Lestari, S.Si</option>
                    <option value="Rudi Santoso, M.Si">Rudi Santoso, M.Si</option>
                    <option value="Rina Marlina, S.Pd">Rina Marlina, S.Pd</option>
                    <option value="Andi Prasetyo, S.Kom">Andi Prasetyo, S.Kom</option>
                    <option value="Maya Sari, S.Kom">Maya Sari, S.Kom</option>
                    <option value="Hendra Gunawan, S.Ds">Hendra Gunawan, S.Ds</option>
                    <option value="Yuni Astuti, S.Pd">Yuni Astuti, S.Pd</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Jumlah Siswa</label>
                    <input type="number" id="kelasJumlahSiswa" min="0" max="50" value="0"
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Kehadiran (%)</label>
                    <input type="number" id="kelasKehadiran" min="0" max="100" value="100"
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none" />
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Ruangan</label>
                <input type="text" id="kelasRuangan" placeholder="Contoh: R-101"
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none" />
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                <select id="kelasStatusInput"
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none bg-white">
                    <option value="Aktif">Aktif</option>
                    <option value="Non-Aktif">Non-Aktif</option>
                </select>
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="button" onclick="closeModalKelas()" class="flex-1 px-4 py-2 text-sm bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition">
                    Batal
                </button>
                <button type="submit" class="flex-1 px-4 py-2 text-sm bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SECTION: REKAP & LAPORAN -->
<div id="section-laporan" class="section-content p-4 sm:p-6 lg:p-8">

    <!-- HEADER REKAP -->
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
        <button onclick="showSection('presensi', document.querySelector('[onclick*=presensi]'))" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 text-left hover:shadow-md hover:border-purple-200 transition group">
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
        <button onclick="showSection('siswa', document.querySelector('[onclick*=siswa]'))" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 text-left hover:shadow-md hover:border-emerald-200 transition group">
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
        <button onclick="showSection('kelas', document.querySelector('[onclick*=kelas]'))" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 text-left hover:shadow-md hover:border-amber-200 transition group">
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
</div>

<!-- SECTION: PENGATURAN -->
<div id="section-sistem" class="section-content p-4 sm:p-6 lg:p-8">

    <!-- HEADER PENGATURAN -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-6 mb-6 text-white relative overflow-hidden">
        <div class="absolute right-0 top-0 opacity-10">
            <i class="fas fa-cog text-9xl"></i>
        </div>
        <div class="relative z-10">
            <p class="text-purple-100 text-xs mb-1">Konfigurasi Sistem</p>
            <h2 class="text-2xl font-bold mb-2">Pengaturan Sistem</h2>
            <p class="text-sm text-purple-100/90 max-w-2xl">
                Atur profil sekolah, jam absensi, notifikasi, backup data, dan preferensi tampilan sistem.
            </p>
            <div class="flex flex-wrap gap-2 mt-4">
                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs">
                    <i class="fas fa-shield-alt mr-1"></i> Sistem v1.0
                </span>
                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs">
                    <i class="fas fa-clock mr-1"></i> <span id="sistemLastUpdate">Belum disimpan</span>
                </span>
            </div>
        </div>
    </div>

    <!-- STAT CARDS PENGATURAN -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-database text-purple-600 text-sm"></i>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Total Data</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-800" id="statTotalDataSistem">0</p>
                    <p class="text-[10px] text-purple-500 mt-1"><i class="fas fa-hdd"></i> Tersimpan</p>
                </div>
                <div class="w-12 h-12 bg-purple-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-database text-purple-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-hdd text-emerald-600 text-sm"></i>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Penggunaan Storage</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-800" id="statStorageSistem">0 KB</p>
                    <p class="text-[10px] text-emerald-500 mt-1"><i class="fas fa-check"></i> Aman</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-server text-emerald-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-shield text-blue-600 text-sm"></i>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Status Sistem</p>
                    </div>
                    <p class="text-2xl font-bold text-emerald-600" id="statStatusSistem">Aktif</p>
                    <p class="text-[10px] text-blue-500 mt-1"><i class="fas fa-check-circle"></i> Normal</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-shield-alt text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-amber-600 text-sm"></i>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Versi Aplikasi</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-800" id="statVersiSistem">1.0.0</p>
                    <p class="text-[10px] text-amber-500 mt-1"><i class="fas fa-tag"></i> Beta</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-code-branch text-amber-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB NAVIGASI PENGATURAN -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-2 mb-6 overflow-x-auto">
        <div class="flex gap-1 min-w-max">
            <button onclick="switchSistemTab('profil')" id="tab-sistem-profil" class="sistem-tab active flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                <i class="fas fa-school"></i> Profil Sekolah
            </button>
            <button onclick="switchSistemTab('absensi')" id="tab-sistem-absensi" class="sistem-tab flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                <i class="fas fa-clock"></i> Jam Absensi
            </button>
            <button onclick="switchSistemTab('notifikasi')" id="tab-sistem-notifikasi" class="sistem-tab flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                <i class="fas fa-bell"></i> Notifikasi
            </button>
            <button onclick="switchSistemTab('tampilan')" id="tab-sistem-tampilan" class="sistem-tab flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                <i class="fas fa-palette"></i> Tampilan
            </button>
            <button onclick="switchSistemTab('backup')" id="tab-sistem-backup" class="sistem-tab flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                <i class="fas fa-database"></i> Backup & Reset
            </button>
        </div>
    </div>

    <!-- KONTEN TAB: PROFIL SEKOLAH -->
    <div id="sistem-content-profil" class="sistem-content">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-200/60">
                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-school text-purple-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-800">Profil Sekolah</h3>
                    <p class="text-xs text-slate-500">Informasi identitas sekolah</p>
                </div>
            </div>

            <form id="formProfilSekolah" onsubmit="simpanProfilSekolah(event)" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Nama Sekolah <span class="text-red-500">*</span></label>
                        <input type="text" id="setNamaSekolah" required
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">NPSN</label>
                        <input type="text" id="setNpsn"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Alamat Sekolah</label>
                    <textarea id="setAlamat" rows="2"
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Telepon</label>
                        <input type="text" id="setTelepon"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                        <input type="email" id="setEmail"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Website</label>
                        <input type="text" id="setWebsite"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Kepala Sekolah</label>
                        <input type="text" id="setKepsek"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Tahun Ajaran</label>
                        <input type="text" id="setTahunAjaran" placeholder="2025/2026"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none" />
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-3 border-t border-slate-200/60">
                    <button type="submit" class="px-5 py-2 text-sm bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-lg hover:shadow-lg transition">
                        <i class="fas fa-save mr-1"></i> Simpan Profil
                    </button>
                    <button type="button" onclick="resetProfilSekolah()" class="px-5 py-2 text-sm bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- KONTEN TAB: JAM ABSENSI -->
    <div id="sistem-content-absensi" class="sistem-content hidden">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-200/60">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-emerald-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-800">Jam Absensi</h3>
                    <p class="text-xs text-slate-500">Atur waktu masuk, pulang, dan batas keterlambatan</p>
                </div>
            </div>

            <form id="formJamAbsensi" onsubmit="simpanJamAbsensi(event)" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Jam Masuk <span class="text-red-500">*</span></label>
                        <input type="time" id="setJamMasuk" required value="07:00"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Batas Terlambat <span class="text-red-500">*</span></label>
                        <input type="time" id="setJamTerlambat" required value="07:15"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Jam Pulang <span class="text-red-500">*</span></label>
                        <input type="time" id="setJamPulang" required value="15:00"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Hari Aktif Absensi</label>
                        <select id="setHariAktif"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none bg-white">
                            <option value="senin-jumat">Senin - Jumat</option>
                            <option value="senin-sabtu">Senin - Sabtu</option>
                            <option value="semua">Semua Hari</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Auto Close Absen</label>
                        <select id="setAutoClose"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none bg-white">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Non-Aktif</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Toleransi (menit)</label>
                        <input type="number" id="setToleransi" min="0" max="60" value="15"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none" />
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
                        <div class="text-xs text-amber-800">
                            <p class="font-semibold mb-1">Catatan:</p>
                            <p>Siswa yang absen melebihi <strong>Jam Terlambat</strong> akan ditandai sebagai <strong>Terlambat</strong> namun tetap dihitung hadir.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-3 border-t border-slate-200/60">
                    <button type="submit" class="px-5 py-2 text-sm bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-lg hover:shadow-lg transition">
                        <i class="fas fa-save mr-1"></i> Simpan Pengaturan
                    </button>
                    <button type="button" onclick="resetJamAbsensi()" class="px-5 py-2 text-sm bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- KONTEN TAB: NOTIFIKASI -->
    <div id="sistem-content-notifikasi" class="sistem-content hidden">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-200/60">
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-bell text-amber-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-800">Notifikasi</h3>
                    <p class="text-xs text-slate-500">Atur notifikasi yang ingin ditampilkan</p>
                </div>
            </div>

            <div class="space-y-3">
                <label class="flex items-center justify-between p-4 bg-slate-50 hover:bg-slate-100 rounded-lg cursor-pointer transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-check text-emerald-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">Notifikasi Absen Masuk</p>
                            <p class="text-xs text-slate-500">Tampilkan notifikasi saat siswa absen masuk</p>
                        </div>
                    </div>
                    <input type="checkbox" id="notifAbsenMasuk" class="w-5 h-5 rounded accent-purple-500" checked />
                </label>

                <label class="flex items-center justify-between p-4 bg-slate-50 hover:bg-slate-100 rounded-lg cursor-pointer transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-medical text-amber-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">Notifikasi Pengajuan Izin/Sakit</p>
                            <p class="text-xs text-slate-500">Tampilkan notifikasi saat siswa mengajukan izin</p>
                        </div>
                    </div>
                    <input type="checkbox" id="notifIzin" class="w-5 h-5 rounded accent-purple-500" checked />
                </label>

                <label class="flex items-center justify-between p-4 bg-slate-50 hover:bg-slate-100 rounded-lg cursor-pointer transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-rose-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-times text-rose-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">Notifikasi Alpha</p>
                            <p class="text-xs text-slate-500">Tampilkan notifikasi saat ada siswa alpha</p>
                        </div>
                    </div>
                    <input type="checkbox" id="notifAlpha" class="w-5 h-5 rounded accent-purple-500" />
                </label>

                <label class="flex items-center justify-between p-4 bg-slate-50 hover:bg-slate-100 rounded-lg cursor-pointer transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">Laporan Harian Otomatis</p>
                            <p class="text-xs text-slate-500">Kirim laporan kehadiran setiap akhir hari</p>
                        </div>
                    </div>
                    <input type="checkbox" id="notifLaporan" class="w-5 h-5 rounded accent-purple-500" />
                </label>

                <label class="flex items-center justify-between p-4 bg-slate-50 hover:bg-slate-100 rounded-lg cursor-pointer transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-envelope text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">Notifikasi Email</p>
                            <p class="text-xs text-slate-500">Kirim pemberitahuan ke email admin</p>
                        </div>
                    </div>
                    <input type="checkbox" id="notifEmail" class="w-5 h-5 rounded accent-purple-500" />
                </label>
            </div>

            <div class="flex items-center gap-3 pt-4 mt-4 border-t border-slate-200/60">
                <button onclick="simpanNotifikasi()" class="px-5 py-2 text-sm bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition">
                    <i class="fas fa-save mr-1"></i> Simpan Notifikasi
                </button>
                <button onclick="resetNotifikasi()" class="px-5 py-2 text-sm bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition">
                    <i class="fas fa-undo mr-1"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- KONTEN TAB: TAMPILAN -->
    <div id="sistem-content-tampilan" class="sistem-content hidden">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-200/60">
                <div class="w-10 h-10 bg-pink-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-palette text-pink-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-800">Tampilan & Tema</h3>
                    <p class="text-xs text-slate-500">Atur tema warna dan preferensi tampilan</p>
                </div>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-3">Warna Tema Utama</label>
                    <div class="flex flex-wrap gap-3" id="temaWarnaOptions">
                        <button type="button" onclick="pilihTema('purple')" data-tema="purple" class="tema-option w-12 h-12 rounded-xl bg-purple-500 border-4 border-purple-300 shadow-md transition hover:scale-110"></button>
                        <button type="button" onclick="pilihTema('blue')" data-tema="blue" class="tema-option w-12 h-12 rounded-xl bg-blue-500 border-4 border-transparent shadow-md transition hover:scale-110"></button>
                        <button type="button" onclick="pilihTema('emerald')" data-tema="emerald" class="tema-option w-12 h-12 rounded-xl bg-emerald-500 border-4 border-transparent shadow-md transition hover:scale-110"></button>
                        <button type="button" onclick="pilihTema('amber')" data-tema="amber" class="tema-option w-12 h-12 rounded-xl bg-amber-500 border-4 border-transparent shadow-md transition hover:scale-110"></button>
                        <button type="button" onclick="pilihTema('rose')" data-tema="rose" class="tema-option w-12 h-12 rounded-xl bg-rose-500 border-4 border-transparent shadow-md transition hover:scale-110"></button>
                        <button type="button" onclick="pilihTema('indigo')" data-tema="indigo" class="tema-option w-12 h-12 rounded-xl bg-indigo-500 border-4 border-transparent shadow-md transition hover:scale-110"></button>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Pilih warna utama sistem. Tema aktif: <span id="temaAktif" class="font-semibold text-purple-600">Purple</span></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Font Size</label>
                        <select id="setFontSize" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent outline-none bg-white">
                            <option value="kecil">Kecil</option>
                            <option value="sedang" selected>Sedang</option>
                            <option value="besar">Besar</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Sidebar Default</label>
                        <select id="setSidebar" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent outline-none bg-white">
                            <option value="terbuka" selected>Terbuka</option>
                            <option value="tertutup">Tertutup</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Bahasa Sistem</label>
                    <select id="setBahasa" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent outline-none bg-white">
                        <option value="id" selected>Bahasa Indonesia</option>
                        <option value="en">English</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 mt-4 border-t border-slate-200/60">
                <button onclick="simpanTampilan()" class="px-5 py-2 text-sm bg-gradient-to-r from-pink-500 to-rose-600 text-white rounded-lg hover:shadow-lg transition">
                    <i class="fas fa-save mr-1"></i> Simpan Tampilan
                </button>
                <button onclick="resetTampilan()" class="px-5 py-2 text-sm bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition">
                    <i class="fas fa-undo mr-1"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- KONTEN TAB: BACKUP & RESET -->
    <div id="sistem-content-backup" class="sistem-content hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

            <!-- Kartu Backup -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-slate-200/60">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-download text-emerald-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Backup Data</h3>
                        <p class="text-xs text-slate-500">Simpan semua data ke file JSON</p>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mb-4">
                    Backup mencakup: <strong>Data Siswa</strong>, <strong>Data Guru</strong>, <strong>Data Kelas</strong>, <strong>Data Presensi</strong>, dan <strong>Pengaturan Sistem</strong>.
                </p>
                <div class="bg-slate-50 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="text-slate-600">Total Data:</span>
                        <span class="font-semibold text-slate-800" id="backupTotalData">0 item</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">Perkiraan Ukuran:</span>
                        <span class="font-semibold text-slate-800" id="backupSize">0 KB</span>
                    </div>
                </div>
                <button onclick="backupData()" class="w-full px-5 py-2.5 text-sm bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-lg hover:shadow-lg transition">
                    <i class="fas fa-download mr-1"></i> Backup Sekarang
                </button>
            </div>

            <!-- Kartu Restore -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-slate-200/60">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-upload text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Restore Data</h3>
                        <p class="text-xs text-slate-500">Kembalikan data dari file backup</p>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mb-4">
                    ⚠️ <strong>Peringatan:</strong> Restore akan <strong>menimpa semua data</strong> saat ini. Pastikan Anda sudah backup terlebih dahulu.
                </p>
                <input type="file" id="restoreFile" accept=".json"
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none mb-4" />
                <button onclick="restoreData()" class="w-full px-5 py-2.5 text-sm bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg hover:shadow-lg transition">
                    <i class="fas fa-upload mr-1"></i> Restore Data
                </button>
            </div>

            <!-- Kartu Reset Data -->
            <div class="bg-white rounded-2xl shadow-sm border-2 border-rose-200 p-6 md:col-span-2">
                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-rose-200">
                    <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-rose-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-rose-700">Reset Data</h3>
                        <p class="text-xs text-rose-500">Hapus data tertentu (tidak dapat dikembalikan!)</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                    <button onclick="resetDataSpesifik('presensi')" class="px-4 py-3 text-sm bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg transition text-left">
                        <i class="fas fa-clipboard-list mr-2"></i> Reset Data Presensi
                    </button>
                    <button onclick="resetDataSpesifik('siswa')" class="px-4 py-3 text-sm bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg transition text-left">
                        <i class="fas fa-user-graduate mr-2"></i> Reset Data Siswa
                    </button>
                    <button onclick="resetDataSpesifik('guru')" class="px-4 py-3 text-sm bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg transition text-left">
                        <i class="fas fa-chalkboard-teacher mr-2"></i> Reset Data Guru
                    </button>
                    <button onclick="resetDataSpesifik('kelas')" class="px-4 py-3 text-sm bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg transition text-left">
                        <i class="fas fa-school mr-2"></i> Reset Data Kelas
                    </button>
                </div>
                <button onclick="resetSemuaData()" class="w-full px-5 py-3 text-sm bg-gradient-to-r from-rose-500 to-red-600 text-white rounded-lg hover:shadow-lg transition font-semibold">
                    <i class="fas fa-trash-alt mr-1"></i> RESET SEMUA DATA SISTEM
                </button>
            </div>
        </div>
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

            // Inisialisasi section saat dibuka
            if (nama === 'presensi') {
                initPresensiSection();
            } else if (nama === 'siswa') {
                initSiswaSection();
            } else if (nama === 'guru') {
                initGuruSection();
            } else if (nama === 'kelas') {
                initKelasSection();
            }else if (nama === 'laporan') {
                 initLaporanSection(); 
            }else if (nama === 'sistem') {
                 initSistemSection();  // ← TAMBAHKAN INI
            }
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

/* ============================================================ */
/* DATA GURU - Logic                                            */
/* ============================================================ */

// Data dummy guru
let dataGuru = [
    { id: 1, nama: 'Budi Hartono, S.Pd', nip: '198501012010011001', mapel: 'Matematika', gender: 'L', pengalaman: 15, pendidikan: 'S1', email: 'budi.h@hadirin.id', telepon: '0813-2222-0001', status: 'Aktif' },
    { id: 2, nama: 'Siti Aminah, M.Pd', nip: '198702022011012002', mapel: 'Bahasa Indonesia', gender: 'P', pengalaman: 12, pendidikan: 'S2', email: 'siti.a@hadirin.id', telepon: '0813-2222-0002', status: 'Aktif' },
    { id: 3, nama: 'Ahmad Fauzi, S.Pd', nip: '199003032012011003', mapel: 'Bahasa Inggris', gender: 'L', pengalaman: 10, pendidikan: 'S1', email: 'ahmad.f@hadirin.id', telepon: '0813-2222-0003', status: 'Aktif' },
    { id: 4, nama: 'Dewi Lestari, S.Si', nip: '198804042013012004', mapel: 'Fisika', gender: 'P', pengalaman: 11, pendidikan: 'S1', email: 'dewi.l@hadirin.id', telepon: '0813-2222-0004', status: 'Aktif' },
    { id: 5, nama: 'Rudi Santoso, M.Si', nip: '198205052009011005', mapel: 'Kimia', gender: 'L', pengalaman: 18, pendidikan: 'S2', email: 'rudi.s@hadirin.id', telepon: '0813-2222-0005', status: 'Aktif' },
    { id: 6, nama: 'Rina Marlina, S.Pd', nip: '199106062014012006', mapel: 'Biologi', gender: 'P', pengalaman: 9, pendidikan: 'S1', email: 'rina.m@hadirin.id', telepon: '0813-2222-0006', status: 'Aktif' },
    { id: 7, nama: 'Andi Prasetyo, S.Kom', nip: '198907072013011007', mapel: 'Pemrograman', gender: 'L', pengalaman: 10, pendidikan: 'S1', email: 'andi.p@hadirin.id', telepon: '0813-2222-0007', status: 'Aktif' },
    { id: 8, nama: 'Maya Sari, S.Kom', nip: '199208082015012008', mapel: 'Jaringan', gender: 'P', pengalaman: 8, pendidikan: 'S1', email: 'maya.s@hadirin.id', telepon: '0813-2222-0008', status: 'Aktif' },
    { id: 9, nama: 'Hendra Gunawan, S.Ds', nip: '199009092014011009', mapel: 'Multimedia', gender: 'L', pengalaman: 9, pendidikan: 'S1', email: 'hendra.g@hadirin.id', telepon: '0813-2222-0009', status: 'Aktif' },
    { id: 10, nama: 'Yuni Astuti, S.Pd', nip: '198603102010012010', mapel: 'Sejarah', gender: 'P', pengalaman: 14, pendidikan: 'S1', email: 'yuni.a@hadirin.id', telepon: '0813-2222-0010', status: 'Aktif' },
    { id: 11, nama: 'Firman Syah, S.Pd', nip: '199411112016011011', mapel: 'Matematika', gender: 'L', pengalaman: 7, pendidikan: 'S1', email: 'firman.s@hadirin.id', telepon: '0813-2222-0011', status: 'Aktif' },
    { id: 12, nama: 'Lilis Suryani, M.Pd', nip: '198712122011012012', mapel: 'Bahasa Indonesia', gender: 'P', pengalaman: 13, pendidikan: 'S2', email: 'lilis.s@hadirin.id', telepon: '0813-2222-0012', status: 'Aktif' },
    { id: 13, nama: 'Bayu Setiawan, S.Pd', nip: '199305132015011013', mapel: 'Bahasa Inggris', gender: 'L', pengalaman: 8, pendidikan: 'S1', email: 'bayu.s@hadirin.id', telepon: '0813-2222-0013', status: 'Aktif' },
    { id: 14, nama: 'Nurul Hidayah, S.Si', nip: '199106142014012014', mapel: 'Fisika', gender: 'P', pengalaman: 9, pendidikan: 'S1', email: 'nurul.h@hadirin.id', telepon: '0813-2222-0014', status: 'Cuti' },
    { id: 15, nama: 'Wahyu Kurniawan, S.Kom', nip: '199007152013011015', mapel: 'Pemrograman', gender: 'L', pengalaman: 10, pendidikan: 'S1', email: 'wahyu.k@hadirin.id', telepon: '0813-2222-0015', status: 'Aktif' }
];

let filteredGuru = [...dataGuru];
let currentPageGuru = 1;
let editingGuruId = null;
const rowsPerPageGuru = 8;

// Warna avatar guru
function avatarColorGuru(nama) {
    const colors = [
        'bg-emerald-100 text-emerald-600',
        'bg-teal-100 text-teal-600',
        'bg-cyan-100 text-cyan-600',
        'bg-blue-100 text-blue-600',
        'bg-indigo-100 text-indigo-600',
        'bg-purple-100 text-purple-600',
        'bg-pink-100 text-pink-600',
        'bg-amber-100 text-amber-600'
    ];
    const idx = nama.charCodeAt(0) % colors.length;
    return colors[idx];
}

// Badge mapel
function mapelBadge(mapel) {
    const map = {
        'Matematika': 'bg-purple-100 text-purple-700',
        'Bahasa Indonesia': 'bg-amber-100 text-amber-700',
        'Bahasa Inggris': 'bg-blue-100 text-blue-700',
        'Fisika': 'bg-indigo-100 text-indigo-700',
        'Kimia': 'bg-emerald-100 text-emerald-700',
        'Biologi': 'bg-teal-100 text-teal-700',
        'Pemrograman': 'bg-cyan-100 text-cyan-700',
        'Jaringan': 'bg-rose-100 text-rose-700',
        'Multimedia': 'bg-pink-100 text-pink-700',
        'Sejarah': 'bg-orange-100 text-orange-700'
    };
    const cls = map[mapel] || 'bg-slate-100 text-slate-700';
    return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium ${cls}">${mapel}</span>`;
}

// Render statistik guru
function renderStatistikGuru() {
    const total = filteredGuru.length;
    const laki = filteredGuru.filter(g => g.gender === 'L').length;
    const perempuan = filteredGuru.filter(g => g.gender === 'P').length;
    const totalMapel = [...new Set(dataGuru.map(g => g.mapel))].length;
    const rataPengalaman = total > 0
        ? Math.round(filteredGuru.reduce((a, b) => a + b.pengalaman, 0) / total)
        : 0;

    const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    setTxt('statTotalGuru', total);
    setTxt('statLakiGuru', laki);
    setTxt('statPerempuanGuru', perempuan);
    setTxt('statPengalamanGuru', rataPengalaman);
    setTxt('guruTotal', dataGuru.length);
    setTxt('guruMapel', totalMapel);

    const pct = (n) => total > 0 ? Math.round((n / total) * 100) + '%' : '0%';
    setTxt('pctLakiGuru', pct(laki));
    setTxt('pctPerempuanGuru', pct(perempuan));
}

// Render grid card guru
function renderGridGuru() {
    const grid = document.getElementById('guruGrid');
    if (!grid) return;

    const start = (currentPageGuru - 1) * rowsPerPageGuru;
    const end = start + rowsPerPageGuru;
    const pageData = filteredGuru.slice(start, end);

    if (pageData.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full bg-white rounded-2xl border border-slate-200/60 p-12 text-center">
                <div class="flex flex-col items-center gap-2 text-slate-400">
                    <i class="fas fa-user-slash text-4xl"></i>
                    <p class="text-sm">Tidak ada data guru yang cocok</p>
                    <button onclick="resetFilterGuru()" class="text-xs text-emerald-600 hover:underline">Reset filter</button>
                </div>
            </div>`;
        return;
    }

    grid.innerHTML = pageData.map(g => {
        const genderIcon = g.gender === 'L' ? 'fa-mars text-blue-500' : 'fa-venus text-pink-500';
        const genderText = g.gender === 'L' ? 'Laki-laki' : 'Perempuan';
        const statusBadge = g.status === 'Aktif'
            ? '<span class="badge-hadir">Aktif</span>'
            : g.status === 'Cuti'
            ? '<span class="badge-izin">Cuti</span>'
            : '<span class="badge-alpha">Non-Aktif</span>';

        return `
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 hover:shadow-lg hover:border-emerald-200 transition group">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="avatar-circle ${avatarColorGuru(g.nama)} w-12 h-12 text-base flex-shrink-0">${g.nama.charAt(0)}</div>
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-800 text-sm leading-tight truncate">${g.nama}</p>
                        <p class="text-[10px] text-slate-400 truncate">NIP: ${g.nip}</p>
                    </div>
                </div>
                ${statusBadge}
            </div>

            <div class="mb-3">${mapelBadge(g.mapel)}</div>

            <div class="space-y-2 text-xs mb-3">
                <div class="flex items-center gap-2 text-slate-600">
                    <i class="fas ${genderIcon} w-4"></i>
                    <span>${genderText}</span>
                </div>
                <div class="flex items-center gap-2 text-slate-600">
                    <i class="fas fa-graduation-cap text-slate-400 w-4"></i>
                    <span>${g.pendidikan}</span>
                </div>
                <div class="flex items-center gap-2 text-slate-600">
                    <i class="fas fa-briefcase text-slate-400 w-4"></i>
                    <span>${g.pengalaman} tahun pengalaman</span>
                </div>
                <div class="flex items-center gap-2 text-slate-600 truncate">
                    <i class="fas fa-envelope text-slate-400 w-4"></i>
                    <span class="truncate">${g.email}</span>
                </div>
                <div class="flex items-center gap-2 text-slate-600">
                    <i class="fas fa-phone text-slate-400 w-4"></i>
                    <span>${g.telepon}</span>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                <button onclick="detailGuru(${g.id})" class="flex-1 px-2 py-1.5 text-[11px] bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition">
                    <i class="fas fa-eye mr-1"></i> Detail
                </button>
                <button onclick="openModalGuru('edit', ${g.id})" class="flex-1 px-2 py-1.5 text-[11px] bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
                    <i class="fas fa-edit mr-1"></i> Edit
                </button>
                <button onclick="hapusGuru(${g.id})" class="px-2 py-1.5 text-[11px] bg-red-500 hover:bg-red-600 text-white rounded-lg transition" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>`;
    }).join('');
}

// Render tabel guru
function renderTabelGuru() {
    const tbody = document.getElementById('guruTableBody');
    if (!tbody) return;

    const start = (currentPageGuru - 1) * rowsPerPageGuru;
    const end = start + rowsPerPageGuru;
    const pageData = filteredGuru.slice(start, end);

    if (pageData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center gap-2 text-slate-400">
                        <i class="fas fa-inbox text-4xl"></i>
                        <p class="text-sm">Tidak ada data guru yang cocok</p>
                        <button onclick="resetFilterGuru()" class="text-xs text-emerald-600 hover:underline">Reset filter</button>
                    </div>
                </td>
            </tr>`;
    } else {
        tbody.innerHTML = pageData.map((g, i) => {
            const genderBadge = g.gender === 'L'
                ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-700"><i class="fas fa-mars"></i> L</span>'
                : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-pink-100 text-pink-700"><i class="fas fa-venus"></i> P</span>';
            const statusBadge = g.status === 'Aktif'
                ? '<span class="badge-hadir">Aktif</span>'
                : g.status === 'Cuti'
                ? '<span class="badge-izin">Cuti</span>'
                : '<span class="badge-alpha">Non-Aktif</span>';

            return `
            <tr class="table-row-hover border-b">
                <td class="px-6 py-4 text-sm text-slate-500">${start + i + 1}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="avatar-circle ${avatarColorGuru(g.nama)}">${g.nama.charAt(0)}</div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">${g.nama}</p>
                            <p class="text-[10px] text-slate-400">${g.email}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm font-mono text-slate-600">${g.nip}</td>
                <td class="px-6 py-4 text-center">${mapelBadge(g.mapel)}</td>
                <td class="px-6 py-4 text-center">${genderBadge}</td>
                <td class="px-6 py-4 text-center text-sm font-semibold text-slate-700">${g.pengalaman} thn</td>
                <td class="px-6 py-4 text-center">${statusBadge}</td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="detailGuru(${g.id})" class="btn-edit text-xs" title="Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="openModalGuru('edit', ${g.id})" class="btn-edit text-xs" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="hapusGuru(${g.id})" class="btn-delete text-xs" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    const info = document.getElementById('guruTableInfo');
    if (info) info.textContent = `Menampilkan ${pageData.length} dari ${filteredGuru.length} data`;

    renderPaginationGuru();
}

// Render pagination guru
function renderPaginationGuru() {
    const totalPages = Math.ceil(filteredGuru.length / rowsPerPageGuru) || 1;
    const info = document.getElementById('guruPaginationInfo');
    if (info) info.textContent = `Halaman ${currentPageGuru} dari ${totalPages}`;

    const btnPrev = document.getElementById('btnPrevGuru');
    const btnNext = document.getElementById('btnNextGuru');
    if (btnPrev) btnPrev.disabled = currentPageGuru <= 1;
    if (btnNext) btnNext.disabled = currentPageGuru >= totalPages;

    const numbersEl = document.getElementById('guruPaginationNumbers');
    if (numbersEl) {
        let html = '';
        const maxShow = 5;
        let startPage = Math.max(1, currentPageGuru - Math.floor(maxShow / 2));
        let endPage = Math.min(totalPages, startPage + maxShow - 1);
        if (endPage - startPage + 1 < maxShow) startPage = Math.max(1, endPage - maxShow + 1);

        for (let i = startPage; i <= endPage; i++) {
            const active = i === currentPageGuru;
            html += `<button onclick="goToPageGuru(${i})" class="px-3 py-1 text-xs rounded-lg transition ${active ? 'bg-emerald-500 text-white' : 'bg-white border border-slate-200 hover:bg-slate-100'}">${i}</button>`;
        }
        numbersEl.innerHTML = html;
    }
}

function goToPageGuru(page) {
    currentPageGuru = page;
    renderGridGuru();
    renderTabelGuru();
}

function changePageGuru(delta) {
    const totalPages = Math.ceil(filteredGuru.length / rowsPerPageGuru) || 1;
    const newPage = currentPageGuru + delta;
    if (newPage >= 1 && newPage <= totalPages) {
        currentPageGuru = newPage;
        renderGridGuru();
        renderTabelGuru();
    }
}

// Filter guru
function applyFilterGuru() {
    const search = (document.getElementById('searchGuru')?.value || '').toLowerCase();
    const mapel = document.getElementById('filterMapel')?.value || '';
    const gender = document.getElementById('filterGenderGuru')?.value || '';

    filteredGuru = dataGuru.filter(g => {
        const matchSearch = g.nama.toLowerCase().includes(search) || g.nip.toLowerCase().includes(search);
        const matchMapel = !mapel || g.mapel === mapel;
        const matchGender = !gender || g.gender === gender;
        return matchSearch && matchMapel && matchGender;
    });

    currentPageGuru = 1;
    renderStatistikGuru();
    renderGridGuru();
    renderTabelGuru();
}

function resetFilterGuru() {
    const el = (id) => document.getElementById(id);
    if (el('searchGuru')) el('searchGuru').value = '';
    if (el('filterMapel')) el('filterMapel').value = '';
    if (el('filterGenderGuru')) el('filterGenderGuru').value = '';
    filteredGuru = [...dataGuru];
    currentPageGuru = 1;
    renderStatistikGuru();
    renderGridGuru();
    renderTabelGuru();
}

// Modal tambah/edit guru
function openModalGuru(mode, id = null) {
    const modal = document.getElementById('modalGuru');
    const title = document.getElementById('modalGuruTitle');
    if (!modal) return;

    editingGuruId = null;

    if (mode === 'edit' && id) {
        const g = dataGuru.find(x => x.id === id);
        if (!g) return;
        editingGuruId = id;
        if (title) title.innerHTML = '<i class="fas fa-edit mr-2"></i> Edit Guru';
        document.getElementById('guruId').value = g.id;
        document.getElementById('guruNama').value = g.nama;
        document.getElementById('guruNip').value = g.nip;
        document.getElementById('guruGender').value = g.gender;
        document.getElementById('guruMapelInput').value = g.mapel;
        document.getElementById('guruPengalaman').value = g.pengalaman;
        document.getElementById('guruPendidikan').value = g.pendidikan;
        document.getElementById('guruStatusInput').value = g.status;
        document.getElementById('guruEmail').value = g.email;
        document.getElementById('guruTelepon').value = g.telepon;
    } else {
        if (title) title.innerHTML = '<i class="fas fa-user-plus mr-2"></i> Tambah Guru';
        document.getElementById('formGuru').reset();
        document.getElementById('guruId').value = '';
        document.getElementById('guruPengalaman').value = 1;
        document.getElementById('guruPendidikan').value = 'S1';
        document.getElementById('guruStatusInput').value = 'Aktif';
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModalGuru() {
    const modal = document.getElementById('modalGuru');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    editingGuruId = null;
}

// Submit form guru (tambah / edit)
function submitFormGuru(event) {
    event.preventDefault();

    const nama = document.getElementById('guruNama').value.trim();
    const nip = document.getElementById('guruNip').value.trim();
    const gender = document.getElementById('guruGender').value;
    const mapel = document.getElementById('guruMapelInput').value;
    const pengalaman = parseInt(document.getElementById('guruPengalaman').value) || 0;
    const pendidikan = document.getElementById('guruPendidikan').value;
    const status = document.getElementById('guruStatusInput').value;
    const email = document.getElementById('guruEmail').value.trim() || `${nama.toLowerCase().split(',')[0].replace(/\s+/g, '.')}@hadirin.id`;
    const telepon = document.getElementById('guruTelepon').value.trim() || '0813-0000-0000';

    if (!nama || !nip || !gender || !mapel) {
        alert('⚠️ Mohon lengkapi semua field yang wajib diisi!');
        return;
    }

    if (editingGuruId) {
        // Mode edit
        const idx = dataGuru.findIndex(x => x.id === editingGuruId);
        if (idx > -1) {
            dataGuru[idx] = {
                ...dataGuru[idx],
                nama, nip, gender, mapel, pengalaman, pendidikan, status, email, telepon
            };
        }
        alert('✅ Data guru berhasil diperbarui!');
    } else {
        // Mode tambah
        const newId = dataGuru.length > 0 ? Math.max(...dataGuru.map(g => g.id)) + 1 : 1;
        dataGuru.unshift({
            id: newId,
            nama, nip, gender, mapel, pengalaman, pendidikan, status, email, telepon
        });
        alert('✅ Guru baru berhasil ditambahkan!');
    }

    closeModalGuru();
    applyFilterGuru();
}

// Detail guru
function detailGuru(id) {
    const g = dataGuru.find(x => x.id === id);
    if (!g) return;
    const genderText = g.gender === 'L' ? 'Laki-laki' : 'Perempuan';
    alert(
        `👨‍🏫 Detail Guru\n\n` +
        `Nama        : ${g.nama}\n` +
        `NIP         : ${g.nip}\n` +
        `Mapel       : ${g.mapel}\n` +
        `Gender      : ${genderText}\n` +
        `Pendidikan  : ${g.pendidikan}\n` +
        `Pengalaman  : ${g.pengalaman} tahun\n` +
        `Email       : ${g.email}\n` +
        `Telepon     : ${g.telepon}\n` +
        `Status      : ${g.status}`
    );
}

// Hapus guru
function hapusGuru(id) {
    const g = dataGuru.find(x => x.id === id);
    if (!g) return;
    if (!confirm(`Yakin ingin menghapus guru "${g.nama}"?`)) return;
    const index = dataGuru.findIndex(x => x.id === id);
    if (index > -1) {
        dataGuru.splice(index, 1);
        applyFilterGuru();
        alert('✅ Data guru berhasil dihapus');
    }
}

// Export guru
function exportGuru() {
    let csv = 'No,Nama,NIP,Mapel,Gender,Pendidikan,Pengalaman,Email,Telepon,Status\n';
    filteredGuru.forEach((g, i) => {
        csv += `${i + 1},${g.nama},${g.nip},${g.mapel},${g.gender},${g.pendidikan},${g.pengalaman} thn,${g.email},${g.telepon},${g.status}\n`;
    });
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `data_guru_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
    alert('✅ Data guru berhasil diexport ke CSV');
}

// Inisialisasi section guru
function initGuruSection() {
    applyFilterGuru();

    // Event listener untuk filter
    ['searchGuru', 'filterMapel', 'filterGenderGuru'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', applyFilterGuru);
            el.addEventListener('change', applyFilterGuru);
        }
    });
}

/* ============================================================ */
/* DATA KELAS - Logic                                           */
/* ============================================================ */

// Data dummy kelas
let dataKelas = [
    { id: 1, nama: 'XII.RPL 1', tingkat: 'XII', jurusan: 'RPL', wali: 'Budi Hartono, S.Pd', jumlahSiswa: 32, kehadiran: 96, ruangan: 'R-101', status: 'Aktif' },
    { id: 2, nama: 'XII.RPL 2', tingkat: 'XII', jurusan: 'RPL', wali: 'Andi Prasetyo, S.Kom', jumlahSiswa: 30, kehadiran: 94, ruangan: 'R-102', status: 'Aktif' },
    { id: 3, nama: 'XII.TKJ 1', tingkat: 'XII', jurusan: 'TKJ', wali: 'Maya Sari, S.Kom', jumlahSiswa: 30, kehadiran: 88, ruangan: 'R-103', status: 'Aktif' },
    { id: 4, nama: 'XII.TKJ 2', tingkat: 'XII', jurusan: 'TKJ', wali: 'Rina Marlina, S.Pd', jumlahSiswa: 28, kehadiran: 86, ruangan: 'R-104', status: 'Aktif' },
    { id: 5, nama: 'XI.RPL 1', tingkat: 'XI', jurusan: 'RPL', wali: 'Siti Aminah, M.Pd', jumlahSiswa: 35, kehadiran: 92, ruangan: 'R-105', status: 'Aktif' },
    { id: 6, nama: 'XI.RPL 2', tingkat: 'XI', jurusan: 'RPL', wali: 'Firman Syah, S.Pd', jumlahSiswa: 33, kehadiran: 91, ruangan: 'R-106', status: 'Aktif' },
    { id: 7, nama: 'XI.TKJ 1', tingkat: 'XI', jurusan: 'TKJ', wali: 'Ahmad Fauzi, S.Pd', jumlahSiswa: 28, kehadiran: 86, ruangan: 'R-107', status: 'Aktif' },
    { id: 8, nama: 'XI.TKJ 2', tingkat: 'XI', jurusan: 'TKJ', wali: 'Bayu Setiawan, S.Pd', jumlahSiswa: 27, kehadiran: 85, ruangan: 'R-108', status: 'Aktif' },
    { id: 9, nama: 'X.RPL 1', tingkat: 'X', jurusan: 'RPL', wali: 'Lilis Suryani, M.Pd', jumlahSiswa: 34, kehadiran: 93, ruangan: 'R-201', status: 'Aktif' },
    { id: 10, nama: 'X.RPL 2', tingkat: 'X', jurusan: 'RPL', wali: 'Hendra Gunawan, S.Ds', jumlahSiswa: 32, kehadiran: 92, ruangan: 'R-202', status: 'Aktif' },
    { id: 11, nama: 'X.TKJ 1', tingkat: 'X', jurusan: 'TKJ', wali: 'Rudi Santoso, M.Si', jumlahSiswa: 30, kehadiran: 90, ruangan: 'R-203', status: 'Aktif' },
    { id: 12, nama: 'X.MM 1', tingkat: 'X', jurusan: 'MM', wali: 'Yuni Astuti, S.Pd', jumlahSiswa: 28, kehadiran: 89, ruangan: 'R-204', status: 'Aktif' }
];

let filteredKelas = [...dataKelas];
let currentPageKelas = 1;
let editingKelasId = null;
const rowsPerPageKelas = 8;

// Warna avatar kelas
function avatarColorKelas(nama) {
    const colors = [
        'bg-amber-100 text-amber-600',
        'bg-orange-100 text-orange-600',
        'bg-rose-100 text-rose-600',
        'bg-purple-100 text-purple-600',
        'bg-blue-100 text-blue-600',
        'bg-emerald-100 text-emerald-600',
        'bg-teal-100 text-teal-600',
        'bg-indigo-100 text-indigo-600'
    ];
    const idx = nama.charCodeAt(0) % colors.length;
    return colors[idx];
}

// Badge jurusan
function jurusanBadge(jurusan) {
    const map = {
        'RPL': 'bg-purple-100 text-purple-700',
        'TKJ': 'bg-blue-100 text-blue-700',
        'MM': 'bg-pink-100 text-pink-700',
        'AKL': 'bg-emerald-100 text-emerald-700'
    };
    const cls = map[jurusan] || 'bg-slate-100 text-slate-700';
    return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium ${cls}">${jurusan}</span>`;
}

// Badge tingkat
function tingkatBadge(tingkat) {
    const map = {
        'X': 'bg-emerald-100 text-emerald-700',
        'XI': 'bg-amber-100 text-amber-700',
        'XII': 'bg-rose-100 text-rose-700'
    };
    const cls = map[tingkat] || 'bg-slate-100 text-slate-700';
    return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium ${cls}">Kelas ${tingkat}</span>`;
}

// Render statistik kelas
function renderStatistikKelas() {
    const total = filteredKelas.length;
    const totalSiswa = filteredKelas.reduce((a, b) => a + b.jumlahSiswa, 0);
    const rataSiswa = total > 0 ? Math.round(totalSiswa / total) : 0;
    const rataKehadiran = total > 0
        ? Math.round(filteredKelas.reduce((a, b) => a + b.kehadiran, 0) / total)
        : 0;
    const totalWali = filteredKelas.filter(k => k.wali && k.wali !== '').length;

    const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    setTxt('statTotalKelas', total);
    setTxt('statTotalSiswaKelas', totalSiswa);
    setTxt('pctSiswaKelas', rataSiswa);
    setTxt('statKehadiranKelas', rataKehadiran + '%');
    setTxt('statWaliKelas', totalWali);
    setTxt('kelasTotal', dataKelas.length);
    setTxt('kelasSiswaTotal', dataKelas.reduce((a, b) => a + b.jumlahSiswa, 0));
}

// Render grid card kelas
function renderGridKelas() {
    const grid = document.getElementById('kelasGrid');
    if (!grid) return;

    const start = (currentPageKelas - 1) * rowsPerPageKelas;
    const end = start + rowsPerPageKelas;
    const pageData = filteredKelas.slice(start, end);

    if (pageData.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full bg-white rounded-2xl border border-slate-200/60 p-12 text-center">
                <div class="flex flex-col items-center gap-2 text-slate-400">
                    <i class="fas fa-school text-4xl"></i>
                    <p class="text-sm">Tidak ada data kelas yang cocok</p>
                    <button onclick="resetFilterKelas()" class="text-xs text-amber-600 hover:underline">Reset filter</button>
                </div>
            </div>`;
        return;
    }

    grid.innerHTML = pageData.map(k => {
        const statusBadge = k.status === 'Aktif'
            ? '<span class="badge-hadir">Aktif</span>'
            : '<span class="badge-alpha">Non-Aktif</span>';
        const progressColor = k.kehadiran >= 95 ? 'bg-emerald-500' : k.kehadiran >= 90 ? 'bg-amber-500' : 'bg-rose-500';

        return `
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 hover:shadow-lg hover:border-amber-200 transition group">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="avatar-circle ${avatarColorKelas(k.nama)} w-12 h-12 text-base">${k.nama.charAt(0)}</div>
                    <div>
                        <p class="font-semibold text-slate-800 text-sm leading-tight">${k.nama}</p>
                        <p class="text-[10px] text-slate-400">${k.ruangan || '-'}</p>
                    </div>
                </div>
                ${statusBadge}
            </div>

            <div class="flex items-center gap-2 mb-3 flex-wrap">
                ${tingkatBadge(k.tingkat)}
                ${jurusanBadge(k.jurusan)}
            </div>

            <div class="space-y-2 text-xs mb-3">
                <div class="flex items-center gap-2 text-slate-600">
                    <i class="fas fa-user-tie text-amber-500 w-4"></i>
                    <span class="truncate">${k.wali}</span>
                </div>
                <div class="flex items-center gap-2 text-slate-600">
                    <i class="fas fa-users text-purple-500 w-4"></i>
                    <span>${k.jumlahSiswa} siswa</span>
                </div>
                <div class="flex items-center gap-2 text-slate-600">
                    <i class="fas fa-door-open text-slate-400 w-4"></i>
                    <span>${k.ruangan || '-'}</span>
                </div>
            </div>

            <div class="mb-3">
                <div class="flex justify-between text-[10px] mb-1">
                    <span class="text-slate-500">Kehadiran</span>
                    <span class="font-semibold text-slate-700">${k.kehadiran}%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill ${progressColor}" style="width: ${k.kehadiran}%"></div>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                <button onclick="detailKelas(${k.id})" class="flex-1 px-2 py-1.5 text-[11px] bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition">
                    <i class="fas fa-eye mr-1"></i> Detail
                </button>
                <button onclick="openModalKelas('edit', ${k.id})" class="flex-1 px-2 py-1.5 text-[11px] bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
                    <i class="fas fa-edit mr-1"></i> Edit
                </button>
                <button onclick="hapusKelas(${k.id})" class="px-2 py-1.5 text-[11px] bg-red-500 hover:bg-red-600 text-white rounded-lg transition" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>`;
    }).join('');
}

// Render tabel kelas
function renderTabelKelas() {
    const tbody = document.getElementById('kelasTableBody');
    if (!tbody) return;

    const start = (currentPageKelas - 1) * rowsPerPageKelas;
    const end = start + rowsPerPageKelas;
    const pageData = filteredKelas.slice(start, end);

    if (pageData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center gap-2 text-slate-400">
                        <i class="fas fa-inbox text-4xl"></i>
                        <p class="text-sm">Tidak ada data kelas yang cocok</p>
                        <button onclick="resetFilterKelas()" class="text-xs text-amber-600 hover:underline">Reset filter</button>
                    </div>
                </td>
            </tr>`;
    } else {
        tbody.innerHTML = pageData.map((k, i) => {
            const statusBadge = k.status === 'Aktif'
                ? '<span class="badge-hadir">Aktif</span>'
                : '<span class="badge-alpha">Non-Aktif</span>';
            const progressColor = k.kehadiran >= 95 ? 'text-emerald-600' : k.kehadiran >= 90 ? 'text-amber-600' : 'text-rose-600';

            return `
            <tr class="table-row-hover border-b">
                <td class="px-6 py-4 text-sm text-slate-500">${start + i + 1}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="avatar-circle ${avatarColorKelas(k.nama)}">${k.nama.charAt(0)}</div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">${k.nama}</p>
                            <p class="text-[10px] text-slate-400">${k.ruangan || '-'}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">${k.wali}</td>
                <td class="px-6 py-4 text-center">${tingkatBadge(k.tingkat)}</td>
                <td class="px-6 py-4 text-center">${jurusanBadge(k.jurusan)}</td>
                <td class="px-6 py-4 text-center text-sm font-semibold text-slate-700">${k.jumlahSiswa}</td>
                <td class="px-6 py-4 text-center">
                    <span class="text-sm font-semibold ${progressColor}">${k.kehadiran}%</span>
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="detailKelas(${k.id})" class="btn-edit text-xs" title="Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="openModalKelas('edit', ${k.id})" class="btn-edit text-xs" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="hapusKelas(${k.id})" class="btn-delete text-xs" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    const info = document.getElementById('kelasTableInfo');
    if (info) info.textContent = `Menampilkan ${pageData.length} dari ${filteredKelas.length} data`;

    renderPaginationKelas();
}

// Render pagination kelas
function renderPaginationKelas() {
    const totalPages = Math.ceil(filteredKelas.length / rowsPerPageKelas) || 1;
    const info = document.getElementById('kelasPaginationInfo');
    if (info) info.textContent = `Halaman ${currentPageKelas} dari ${totalPages}`;

    const btnPrev = document.getElementById('btnPrevKelas');
    const btnNext = document.getElementById('btnNextKelas');
    if (btnPrev) btnPrev.disabled = currentPageKelas <= 1;
    if (btnNext) btnNext.disabled = currentPageKelas >= totalPages;

    const numbersEl = document.getElementById('kelasPaginationNumbers');
    if (numbersEl) {
        let html = '';
        const maxShow = 5;
        let startPage = Math.max(1, currentPageKelas - Math.floor(maxShow / 2));
        let endPage = Math.min(totalPages, startPage + maxShow - 1);
        if (endPage - startPage + 1 < maxShow) startPage = Math.max(1, endPage - maxShow + 1);

        for (let i = startPage; i <= endPage; i++) {
            const active = i === currentPageKelas;
            html += `<button onclick="goToPageKelas(${i})" class="px-3 py-1 text-xs rounded-lg transition ${active ? 'bg-amber-500 text-white' : 'bg-white border border-slate-200 hover:bg-slate-100'}">${i}</button>`;
        }
        numbersEl.innerHTML = html;
    }
}

function goToPageKelas(page) {
    currentPageKelas = page;
    renderGridKelas();
    renderTabelKelas();
}

function changePageKelas(delta) {
    const totalPages = Math.ceil(filteredKelas.length / rowsPerPageKelas) || 1;
    const newPage = currentPageKelas + delta;
    if (newPage >= 1 && newPage <= totalPages) {
        currentPageKelas = newPage;
        renderGridKelas();
        renderTabelKelas();
    }
}

// Filter kelas
function applyFilterKelas() {
    const search = (document.getElementById('searchKelas')?.value || '').toLowerCase();
    const tingkat = document.getElementById('filterTingkat')?.value || '';
    const jurusan = document.getElementById('filterJurusan')?.value || '';

    filteredKelas = dataKelas.filter(k => {
        const matchSearch = k.nama.toLowerCase().includes(search) || k.wali.toLowerCase().includes(search);
        const matchTingkat = !tingkat || k.tingkat === tingkat;
        const matchJurusan = !jurusan || k.jurusan === jurusan;
        return matchSearch && matchTingkat && matchJurusan;
    });

    currentPageKelas = 1;
    renderStatistikKelas();
    renderGridKelas();
    renderTabelKelas();
}

function resetFilterKelas() {
    const el = (id) => document.getElementById(id);
    if (el('searchKelas')) el('searchKelas').value = '';
    if (el('filterTingkat')) el('filterTingkat').value = '';
    if (el('filterJurusan')) el('filterJurusan').value = '';
    filteredKelas = [...dataKelas];
    currentPageKelas = 1;
    renderStatistikKelas();
    renderGridKelas();
    renderTabelKelas();
}

// Modal tambah/edit kelas
function openModalKelas(mode, id = null) {
    const modal = document.getElementById('modalKelas');
    const title = document.getElementById('modalKelasTitle');
    if (!modal) return;

    editingKelasId = null;

    if (mode === 'edit' && id) {
        const k = dataKelas.find(x => x.id === id);
        if (!k) return;
        editingKelasId = id;
        if (title) title.innerHTML = '<i class="fas fa-edit mr-2"></i> Edit Kelas';
        document.getElementById('kelasId').value = k.id;
        document.getElementById('kelasNamaInput').value = k.nama;
        document.getElementById('kelasTingkat').value = k.tingkat;
        document.getElementById('kelasJurusan').value = k.jurusan;
        document.getElementById('kelasWali').value = k.wali;
        document.getElementById('kelasJumlahSiswa').value = k.jumlahSiswa;
        document.getElementById('kelasKehadiran').value = k.kehadiran;
        document.getElementById('kelasRuangan').value = k.ruangan || '';
        document.getElementById('kelasStatusInput').value = k.status;
    } else {
        if (title) title.innerHTML = '<i class="fas fa-plus mr-2"></i> Tambah Kelas';
        document.getElementById('formKelas').reset();
        document.getElementById('kelasId').value = '';
        document.getElementById('kelasJumlahSiswa').value = 0;
        document.getElementById('kelasKehadiran').value = 100;
        document.getElementById('kelasStatusInput').value = 'Aktif';
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModalKelas() {
    const modal = document.getElementById('modalKelas');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    editingKelasId = null;
}

// Submit form kelas (tambah / edit)
function submitFormKelas(event) {
    event.preventDefault();

    const nama = document.getElementById('kelasNamaInput').value.trim();
    const tingkat = document.getElementById('kelasTingkat').value;
    const jurusan = document.getElementById('kelasJurusan').value;
    const wali = document.getElementById('kelasWali').value;
    const jumlahSiswa = parseInt(document.getElementById('kelasJumlahSiswa').value) || 0;
    const kehadiran = parseInt(document.getElementById('kelasKehadiran').value) || 0;
    const ruangan = document.getElementById('kelasRuangan').value.trim() || '-';
    const status = document.getElementById('kelasStatusInput').value;

    if (!nama || !tingkat || !jurusan || !wali) {
        alert('⚠️ Mohon lengkapi semua field yang wajib diisi!');
        return;
    }

    if (editingKelasId) {
        // Mode edit
        const idx = dataKelas.findIndex(x => x.id === editingKelasId);
        if (idx > -1) {
            dataKelas[idx] = {
                ...dataKelas[idx],
                nama, tingkat, jurusan, wali, jumlahSiswa, kehadiran, ruangan, status
            };
        }
        alert('✅ Data kelas berhasil diperbarui!');
    } else {
        // Mode tambah
        const newId = dataKelas.length > 0 ? Math.max(...dataKelas.map(k => k.id)) + 1 : 1;
        dataKelas.unshift({
            id: newId,
            nama, tingkat, jurusan, wali, jumlahSiswa, kehadiran, ruangan, status
        });
        alert('✅ Kelas baru berhasil ditambahkan!');
    }

    closeModalKelas();
    applyFilterKelas();
}

// Detail kelas
function detailKelas(id) {
    const k = dataKelas.find(x => x.id === id);
    if (!k) return;
    alert(
        `🏫 Detail Kelas\n\n` +
        `Nama Kelas   : ${k.nama}\n` +
        `Tingkat      : ${k.tingkat}\n` +
        `Jurusan      : ${k.jurusan}\n` +
        `Wali Kelas   : ${k.wali}\n` +
        `Jumlah Siswa : ${k.jumlahSiswa}\n` +
        `Kehadiran    : ${k.kehadiran}%\n` +
        `Ruangan      : ${k.ruangan || '-'}\n` +
        `Status       : ${k.status}`
    );
}

// Hapus kelas
function hapusKelas(id) {
    const k = dataKelas.find(x => x.id === id);
    if (!k) return;
    if (!confirm(`Yakin ingin menghapus kelas "${k.nama}"?`)) return;
    const index = dataKelas.findIndex(x => x.id === id);
    if (index > -1) {
        dataKelas.splice(index, 1);
        applyFilterKelas();
        alert('✅ Data kelas berhasil dihapus');
    }
}

// Export kelas
function exportKelas() {
    let csv = 'No,Nama Kelas,Tingkat,Jurusan,Wali Kelas,Jumlah Siswa,Kehadiran,Ruangan,Status\n';
    filteredKelas.forEach((k, i) => {
        csv += `${i + 1},${k.nama},${k.tingkat},${k.jurusan},${k.wali},${k.jumlahSiswa},${k.kehadiran}%,${k.ruangan || '-'},${k.status}\n`;
    });
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `data_kelas_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
    alert('✅ Data kelas berhasil diexport ke CSV');
}

// Inisialisasi section kelas
function initKelasSection() {
    applyFilterKelas();

    // Event listener untuk filter
    ['searchKelas', 'filterTingkat', 'filterJurusan'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', applyFilterKelas);
            el.addEventListener('change', applyFilterKelas);
        }
    });
}

/* ============================================================ */
/* REKAP & LAPORAN - Logic                                       */
/* ============================================================ */

let currentRekapTab = 'presensi';

// Inisialisasi tanggal laporan
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
    currentRekapTab = tab;
    
    // Update button active state
    document.querySelectorAll('.rekap-tab').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.getElementById('tab-rekap-' + tab);
    if (activeBtn) activeBtn.classList.add('active');
    
    // Show/hide content
    document.querySelectorAll('.rekap-content').forEach(el => el.classList.add('hidden'));
    const content = document.getElementById('rekap-content-' + tab);
    if (content) content.classList.remove('hidden');
    
    // Render sesuai tab
    if (tab === 'presensi') renderRekapPresensi();
    else if (tab === 'siswa') renderRekapSiswa();
    else if (tab === 'guru') renderRekapGuru();
    else if (tab === 'kelas') renderRekapKelas();
}

// Render statistik utama rekap
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

    // Kelompokkan presensi per siswa
    const grouped = {};
    dataPresensi.forEach(p => {
        const key = p.nama;
        if (!grouped[key]) {
            grouped[key] = {
                nama: p.nama,
                kelas: p.kelas,
                hadir: 0, izin: 0, sakit: 0, alpha: 0,
                total: 0
            };
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
        const persenColor = persen >= 90 ? 'text-emerald-600' : persen >= 75 ? 'text-amber-600' : 'text-rose-600';
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

// Render Rekap Siswa (per kelas)
function renderRekapSiswa() {
    const tbody = document.getElementById('rekapSiswaBody');
    if (!tbody) return;

    // Kelompokkan siswa per kelas
    const grouped = {};
    dataSiswa.forEach(s => {
        if (!grouped[s.kelas]) {
            grouped[s.kelas] = {
                kelas: s.kelas,
                total: 0, laki: 0, perempuan: 0,
                totalKehadiran: 0
            };
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
                    <button onclick="showSection('siswa', document.querySelector('[onclick*=siswa]'))" class="btn-edit text-xs" title="Lihat Siswa">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>`;
    }).join('');
}

// Render Rekap Guru (per mata pelajaran)
function renderRekapGuru() {
    const tbody = document.getElementById('rekapGuruBody');
    if (!tbody) return;

    // Kelompokkan guru per mapel
    const grouped = {};
    dataGuru.forEach(g => {
        if (!grouped[g.mapel]) {
            grouped[g.mapel] = {
                mapel: g.mapel,
                total: 0, laki: 0, perempuan: 0,
                totalPengalaman: 0
            };
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
        const jurusanBadge = k.jurusan === 'RPL' ? 'bg-purple-100 text-purple-700' : k.jurusan === 'TKJ' ? 'bg-blue-100 text-blue-700' : k.jurusan === 'MM' ? 'bg-pink-100 text-pink-700' : 'bg-emerald-100 text-emerald-700';

        return `
            <tr class="table-row-hover border-b">
                <td class="px-4 py-3 text-sm text-slate-500">${i + 1}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="avatar-circle bg-amber-100 text-amber-600">${k.nama.charAt(0)}</div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">${k.nama}</p>
                            <p class="text-[10px] text-slate-400">${k.ruangan || '-'}</p>
                        </div>
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

// Detail rekap siswa (alert)
function detailRekapSiswa(nama) {
    const data = dataPresensi.filter(p => p.nama === nama);
    if (data.length === 0) return;
    
    let msg = `📊 Detail Rekap: ${nama}\n\n`;
    data.forEach((d, i) => {
        msg += `${i + 1}. ${d.tanggal} - ${d.status} (${d.metode})\n`;
    });
    alert(msg);
}

// Export Rekap Presensi
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

// Export Rekap Siswa
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

// Export Rekap Guru
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

// Export Rekap Kelas
function exportRekapKelas() {
    let csv = 'No,Nama Kelas,Tingkat,Jurusan,Jumlah Siswa,Wali Kelas,Kehadiran\n';
    dataKelas.forEach((k, i) => {
        csv += `${i + 1},${k.nama},${k.tingkat},${k.jurusan},${k.jumlahSiswa},${k.wali},${k.kehadiran}%\n`;
    });

    downloadCSV(csv, 'rekap_kelas');
}

// Helper: Download CSV
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

// Inisialisasi Section Laporan
function initLaporanSection() {
    initLaporanDate();
    renderStatistikRekap();
    
    // Set default tab
    switchRekapTab('presensi');
}

/* ============================================================ */
/* PENGATURAN SISTEM - Logic                                     */
/* ============================================================ */

const STORAGE_KEY_SISTEM = 'hadirin_pengaturan';

let currentSistemTab = 'profil';

// Pengaturan default
const defaultPengaturan = {
    profil: {
        namaSekolah: 'SMK Negeri 1 Hadirin',
        npsn: '12345678',
        alamat: 'Jl. Pendidikan No. 1, Jakarta',
        telepon: '021-1234567',
        email: 'info@hadirin.id',
        website: 'www.hadirin.id',
        kepsek: 'Dr. H. Ahmad, M.Pd',
        tahunAjaran: '2025/2026'
    },
    absensi: {
        jamMasuk: '07:00',
        jamTerlambat: '07:15',
        jamPulang: '15:00',
        hariAktif: 'senin-jumat',
        autoClose: 'aktif',
        toleransi: 15
    },
    notifikasi: {
        absenMasuk: true,
        izin: true,
        alpha: false,
        laporan: false,
        email: false
    },
    tampilan: {
        tema: 'purple',
        fontSize: 'sedang',
        sidebar: 'terbuka',
        bahasa: 'id'
    }
};

// Load pengaturan dari localStorage
function loadPengaturan() {
    try {
        const saved = JSON.parse(localStorage.getItem(STORAGE_KEY_SISTEM) || 'null');
        if (saved) {
            return {
                profil: { ...defaultPengaturan.profil, ...(saved.profil || {}) },
                absensi: { ...defaultPengaturan.absensi, ...(saved.absensi || {}) },
                notifikasi: { ...defaultPengaturan.notifikasi, ...(saved.notifikasi || {}) },
                tampilan: { ...defaultPengaturan.tampilan, ...(saved.tampilan || {}) },
                lastUpdate: saved.lastUpdate || null
            };
        }
    } catch (e) {
        console.warn('Gagal load pengaturan:', e);
    }
    return { ...defaultPengaturan, lastUpdate: null };
}

// Simpan pengaturan ke localStorage
function savePengaturan(data) {
    data.lastUpdate = new Date().toISOString();
    localStorage.setItem(STORAGE_KEY_SISTEM, JSON.stringify(data));
}

let pengaturanSistem = loadPengaturan();

// Switch antar tab pengaturan
function switchSistemTab(tab) {
    currentSistemTab = tab;

    // Update button active state
    document.querySelectorAll('.sistem-tab').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.getElementById('tab-sistem-' + tab);
    if (activeBtn) activeBtn.classList.add('active');

    // Show/hide content
    document.querySelectorAll('.sistem-content').forEach(el => el.classList.add('hidden'));
    const content = document.getElementById('sistem-content-' + tab);
    if (content) content.classList.remove('hidden');

    // Load data ke form
    if (tab === 'profil') loadProfilSekolah();
    else if (tab === 'absensi') loadJamAbsensi();
    else if (tab === 'notifikasi') loadNotifikasi();
    else if (tab === 'tampilan') loadTampilan();
    else if (tab === 'backup') loadBackupInfo();
}

// ============ PROFIL SEKOLAH ============
function loadProfilSekolah() {
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
    set('setNamaSekolah', pengaturanSistem.profil.namaSekolah);
    set('setNpsn', pengaturanSistem.profil.npsn);
    set('setAlamat', pengaturanSistem.profil.alamat);
    set('setTelepon', pengaturanSistem.profil.telepon);
    set('setEmail', pengaturanSistem.profil.email);
    set('setWebsite', pengaturanSistem.profil.website);
    set('setKepsek', pengaturanSistem.profil.kepsek);
    set('setTahunAjaran', pengaturanSistem.profil.tahunAjaran);
}

function simpanProfilSekolah(event) {
    event.preventDefault();
    pengaturanSistem.profil = {
        namaSekolah: document.getElementById('setNamaSekolah').value.trim(),
        npsn: document.getElementById('setNpsn').value.trim(),
        alamat: document.getElementById('setAlamat').value.trim(),
        telepon: document.getElementById('setTelepon').value.trim(),
        email: document.getElementById('setEmail').value.trim(),
        website: document.getElementById('setWebsite').value.trim(),
        kepsek: document.getElementById('setKepsek').value.trim(),
        tahunAjaran: document.getElementById('setTahunAjaran').value.trim()
    };
    savePengaturan(pengaturanSistem);
    updateSistemLastUpdate();
    showSistemToast('✅ Profil sekolah berhasil disimpan!');
}

function resetProfilSekolah() {
    if (!confirm('Reset profil sekolah ke default?')) return;
    pengaturanSistem.profil = { ...defaultPengaturan.profil };
    savePengaturan(pengaturanSistem);
    loadProfilSekolah();
    updateSistemLastUpdate();
    showSistemToast('🔄 Profil sekolah direset ke default');
}

// ============ JAM ABSENSI ============
function loadJamAbsensi() {
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
    set('setJamMasuk', pengaturanSistem.absensi.jamMasuk);
    set('setJamTerlambat', pengaturanSistem.absensi.jamTerlambat);
    set('setJamPulang', pengaturanSistem.absensi.jamPulang);
    set('setHariAktif', pengaturanSistem.absensi.hariAktif);
    set('setAutoClose', pengaturanSistem.absensi.autoClose);
    set('setToleransi', pengaturanSistem.absensi.toleransi);
}

function simpanJamAbsensi(event) {
    event.preventDefault();
    pengaturanSistem.absensi = {
        jamMasuk: document.getElementById('setJamMasuk').value,
        jamTerlambat: document.getElementById('setJamTerlambat').value,
        jamPulang: document.getElementById('setJamPulang').value,
        hariAktif: document.getElementById('setHariAktif').value,
        autoClose: document.getElementById('setAutoClose').value,
        toleransi: parseInt(document.getElementById('setToleransi').value) || 15
    };
    savePengaturan(pengaturanSistem);
    updateSistemLastUpdate();
    showSistemToast('✅ Pengaturan jam absensi berhasil disimpan!');
}

function resetJamAbsensi() {
    if (!confirm('Reset jam absensi ke default?')) return;
    pengaturanSistem.absensi = { ...defaultPengaturan.absensi };
    savePengaturan(pengaturanSistem);
    loadJamAbsensi();
    updateSistemLastUpdate();
    showSistemToast('🔄 Jam absensi direset ke default');
}

// ============ NOTIFIKASI ============
function loadNotifikasi() {
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.checked = !!val; };
    set('notifAbsenMasuk', pengaturanSistem.notifikasi.absenMasuk);
    set('notifIzin', pengaturanSistem.notifikasi.izin);
    set('notifAlpha', pengaturanSistem.notifikasi.alpha);
    set('notifLaporan', pengaturanSistem.notifikasi.laporan);
    set('notifEmail', pengaturanSistem.notifikasi.email);
}

function simpanNotifikasi() {
    pengaturanSistem.notifikasi = {
        absenMasuk: document.getElementById('notifAbsenMasuk').checked,
        izin: document.getElementById('notifIzin').checked,
        alpha: document.getElementById('notifAlpha').checked,
        laporan: document.getElementById('notifLaporan').checked,
        email: document.getElementById('notifEmail').checked
    };
    savePengaturan(pengaturanSistem);
    updateSistemLastUpdate();
    showSistemToast('✅ Pengaturan notifikasi berhasil disimpan!');
}

function resetNotifikasi() {
    if (!confirm('Reset pengaturan notifikasi ke default?')) return;
    pengaturanSistem.notifikasi = { ...defaultPengaturan.notifikasi };
    savePengaturan(pengaturanSistem);
    loadNotifikasi();
    updateSistemLastUpdate();
    showSistemToast('🔄 Notifikasi direset ke default');
}

// ============ TAMPILAN ============
let selectedTema = pengaturanSistem.tampilan.tema || 'purple';

function pilihTema(tema) {
    selectedTema = tema;
    document.querySelectorAll('.tema-option').forEach(el => {
        if (el.dataset.tema === tema) el.classList.add('active');
        else el.classList.remove('active');
    });
    const namaTema = {
        purple: 'Purple', blue: 'Blue', emerald: 'Emerald',
        amber: 'Amber', rose: 'Rose', indigo: 'Indigo'
    };
    const elAktif = document.getElementById('temaAktif');
    if (elAktif) elAktif.textContent = namaTema[tema] || 'Purple';
}

function loadTampilan() {
    selectedTema = pengaturanSistem.tampilan.tema || 'purple';
    pilihTema(selectedTema);
    const setV = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
    setV('setFontSize', pengaturanSistem.tampilan.fontSize);
    setV('setSidebar', pengaturanSistem.tampilan.sidebar);
    setV('setBahasa', pengaturanSistem.tampilan.bahasa);
}

function simpanTampilan() {
    pengaturanSistem.tampilan = {
        tema: selectedTema,
        fontSize: document.getElementById('setFontSize').value,
        sidebar: document.getElementById('setSidebar').value,
        bahasa: document.getElementById('setBahasa').value
    };
    savePengaturan(pengaturanSistem);
    updateSistemLastUpdate();
    showSistemToast('✅ Pengaturan tampilan berhasil disimpan!');
}

function resetTampilan() {
    if (!confirm('Reset tampilan ke default?')) return;
    pengaturanSistem.tampilan = { ...defaultPengaturan.tampilan };
    savePengaturan(pengaturanSistem);
    loadTampilan();
    updateSistemLastUpdate();
    showSistemToast('🔄 Tampilan direset ke default');
}

// ============ BACKUP & RESET ============
function loadBackupInfo() {
    const total = (dataSiswa?.length || 0) + (dataGuru?.length || 0) + (dataKelas?.length || 0) + (dataPresensi?.length || 0);
    const el1 = document.getElementById('backupTotalData');
    if (el1) el1.textContent = total + ' item';

    // Hitung perkiraan ukuran
    let size = 0;
    try {
        size = JSON.stringify({
            siswa: dataSiswa, guru: dataGuru, kelas: dataKelas,
            presensi: dataPresensi, pengaturan: pengaturanSistem
        }).length;
    } catch (e) {}
    const sizeKB = (size / 1024).toFixed(2);
    const el2 = document.getElementById('backupSize');
    if (el2) el2.textContent = sizeKB + ' KB';
}

function backupData() {
    try {
        const backup = {
            _meta: {
                app: 'Hadirin.web',
                version: '1.0.0',
                exported: new Date().toISOString()
            },
            dataSiswa: dataSiswa || [],
            dataGuru: dataGuru || [],
            dataKelas: dataKelas || [],
            dataPresensi: dataPresensi || [],
            pengaturan: pengaturanSistem
        };

        const blob = new Blob([JSON.stringify(backup, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        const dateStr = new Date().toISOString().split('T')[0];
        a.href = url;
        a.download = `hadirin_backup_${dateStr}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        showSistemToast('✅ Backup berhasil di-download!');
    } catch (e) {
        console.error('Backup error:', e);
        alert('❌ Gagal melakukan backup: ' + e.message);
    }
}

function restoreData() {
    const input = document.getElementById('restoreFile');
    const file = input?.files?.[0];
    if (!file) {
        alert('⚠️ Pilih file backup terlebih dahulu!');
        return;
    }
    if (!file.name.endsWith('.json')) {
        alert('⚠️ File harus berekstensi .json');
        return;
    }
    if (!confirm('⚠️ PERINGATAN!\n\nRestore akan MENIMPA semua data saat ini.\n\nLanjutkan?')) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const backup = JSON.parse(e.target.result);
            if (!backup._meta || backup._meta.app !== 'Hadirin.web') {
                alert('❌ File tidak valid! Pastikan file backup dari aplikasi Hadirin.web.');
                return;
            }

            // Restore data
            if (Array.isArray(backup.dataSiswa)) dataSiswa = backup.dataSiswa;
            if (Array.isArray(backup.dataGuru)) dataGuru = backup.dataGuru;
            if (Array.isArray(backup.dataKelas)) dataKelas = backup.dataKelas;
            if (Array.isArray(backup.dataPresensi)) dataPresensi.length = 0, dataPresensi.push(...backup.dataPresensi);
            if (backup.pengaturan) {
                pengaturanSistem = { ...defaultPengaturan, ...backup.pengaturan };
                savePengaturan(pengaturanSistem);
            }

            // Re-render semua
            try { applyFilterSiswa(); } catch (e) {}
            try { applyFilterGuru(); } catch (e) {}
            try { applyFilterKelas(); } catch (e) {}
            try { applyFilterPresensi(); } catch (e) {}

            updateSistemLastUpdate();
            loadBackupInfo();
            showSistemToast('✅ Data berhasil di-restore!');
            alert('✅ Restore berhasil!\n\nSemua data telah dikembalikan dari file backup.');
        } catch (err) {
            console.error('Restore error:', err);
            alert('❌ Gagal membaca file: ' + err.message);
        }
    };
    reader.readAsText(file);
}

function resetDataSpesifik(jenis) {
    const namaJenis = {
        presensi: 'Data Presensi',
        siswa: 'Data Siswa',
        guru: 'Data Guru',
        kelas: 'Data Kelas'
    };
    const label = namaJenis[jenis] || jenis;
    if (!confirm(`⚠️ Yakin ingin menghapus SEMUA ${label}?\n\nTindakan ini TIDAK BISA dibatalkan!`)) return;
    if (!confirm(`⚠️ KONFIRMASI TERAKHIR\n\nAnda akan menghapus semua ${label}.\n\nLanjutkan?`)) return;

    try {
        if (jenis === 'presensi') { dataPresensi.length = 0; applyFilterPresensi(); }
        else if (jenis === 'siswa') { dataSiswa.length = 0; applyFilterSiswa(); }
        else if (jenis === 'guru') { dataGuru.length = 0; applyFilterGuru(); }
        else if (jenis === 'kelas') { dataKelas.length = 0; applyFilterKelas(); }

        updateSistemLastUpdate();
        loadBackupInfo();
        showSistemToast(`✅ ${label} berhasil direset!`);
    } catch (e) {
        alert('❌ Gagal reset: ' + e.message);
    }
}

function resetSemuaData() {
    if (!confirm('⚠️ PERINGATAN BESAR!\n\nAnda akan menghapus SEMUA data:\n- Data Siswa\n- Data Guru\n- Data Kelas\n- Data Presensi\n- Pengaturan Sistem\n\nData TIDAK BISA dikembalikan!')) return;
    if (!confirm('⚠️ KONFIRMASI TERAKHIR\n\nApakah Anda benar-benar yakin?\n\nKetik OK untuk lanjut.')) return;

    try {
        dataSiswa.length = 0;
        dataGuru.length = 0;
        dataKelas.length = 0;
        dataPresensi.length = 0;
        pengaturanSistem = { ...defaultPengaturan, lastUpdate: null };
        localStorage.removeItem(STORAGE_KEY_SISTEM);

        try { applyFilterSiswa(); } catch (e) {}
        try { applyFilterGuru(); } catch (e) {}
        try { applyFilterKelas(); } catch (e) {}
        try { applyFilterPresensi(); } catch (e) {}

        updateSistemLastUpdate();
        loadBackupInfo();
        showSistemToast('✅ Semua data berhasil direset!');
        alert('✅ Semua data telah dihapus. Sistem kembali ke kondisi awal.');
    } catch (e) {
        alert('❌ Gagal reset: ' + e.message);
    }
}

// ============ UTILS ============
function updateSistemLastUpdate() {
    const el = document.getElementById('sistemLastUpdate');
    if (!el) return;
    if (pengaturanSistem.lastUpdate) {
        const d = new Date(pengaturanSistem.lastUpdate);
        const tgl = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        const jam = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        el.textContent = `${tgl} ${jam}`;
    } else {
        el.textContent = 'Belum disimpan';
    }
}

function updateStatistikSistem() {
    const total = (dataSiswa?.length || 0) + (dataGuru?.length || 0) + (dataKelas?.length || 0) + (dataPresensi?.length || 0);
    const el1 = document.getElementById('statTotalDataSistem');
    if (el1) el1.textContent = total;

    let size = 0;
    try {
        size = JSON.stringify({
            siswa: dataSiswa, guru: dataGuru, kelas: dataKelas,
            presensi: dataPresensi, pengaturan: pengaturanSistem
        }).length;
    } catch (e) {}
    const sizeKB = (size / 1024).toFixed(2);
    const el2 = document.getElementById('statStorageSistem');
    if (el2) el2.textContent = sizeKB + ' KB';
}

function showSistemToast(message) {
    const oldToast = document.querySelector('.sistem-toast');
    if (oldToast) oldToast.remove();
    const toast = document.createElement('div');
    toast.className = 'sistem-toast fixed top-20 right-6 bg-purple-600 text-white px-5 py-3 rounded-xl shadow-2xl z-50 flex items-center gap-2 text-sm';
    toast.style.animation = 'slideInRight 0.3s ease-out';
    toast.innerHTML = `<i class="fas fa-check-circle"></i><span>${message}</span>`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}

// Inisialisasi Section Pengaturan
function initSistemSection() {
    // Update stats
    updateStatistikSistem();
    updateSistemLastUpdate();
    // Set tab default
    switchSistemTab('profil');
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Dashboard Admin ready!');
    setTimeout(() => { initCharts(); }, 100);
    updateClock();
    setInterval(updateClock, 1000);

    // Pre-init data section agar siap saat dibuka
    initPresensiSection();
    initSiswaSection();
    initGuruSection();
    initKelasSection();
    initLaporanSection(); 
    initSistemSection();
});
    </script>

</body>
</html>