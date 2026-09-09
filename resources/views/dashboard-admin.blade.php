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
        .sidebar {
            width: 280px;
            min-height: 100vh;
            background: linear-gradient(180deg, #251b4b 0%, #4b2e81 100%);
            transition: transform 0.3s ease;
            overflow-y: auto;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            color: rgba(255,255,255,0.6);
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
        }
        .sidebar-link:hover { background: rgba(255,255,255,0.08); color: white; }
        .sidebar-link.active { background: rgba(255,255,255,0.12); color: white; }
        .sidebar-link i { width: 20px; font-size: 16px; }
        .sidebar-section-title {
            color: rgba(255,255,255,0.3);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 20px 20px 8px;
        }
        .stat-card { transition: all 0.3s ease; cursor: pointer; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(124, 58, 237, 0.15); }
        .table-row-hover:hover { background-color: #f1f5f9; }
        .badge-hadir { background: #dcfce7; color: #166534; }
        .badge-izin { background: #fef9c3; color: #854d0e; }
        .badge-sakit { background: #fce4ec; color: #b91c1c; }
        .badge-alpha { background: #fee2e2; color: #991b1b; }
        .card-hover { transition: all 0.3s ease; cursor: pointer; }
        .card-hover:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(124, 58, 237, 0.15); }
        .chart-container { position: relative; height: 250px; }
        .chart-container-lg { position: relative; height: 280px; }
        .live-dot { animation: livePulse 1.5s ease-in-out infinite; }
        @keyframes livePulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.3; transform: scale(0.8); } }
        .main-content { flex: 1; min-height: 100vh; }
        .toggle-sidebar { display: none; }
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
                    <div class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center text-white font-bold text-xl">H</div>
                    <div>
                        <span class="text-white font-bold text-lg">Hadirin</span>
                        <span class="text-indigo-300 text-xs block">Administrator</span>
                    </div>
                </div>
            </div>
            <nav class="p-4">
                <div class="sidebar-section-title">Dashboard</div>
                <a href="#" class="sidebar-link active" onclick="showSection('dashboard')">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <div class="sidebar-section-title">Manajemen</div>
                <a href="#" class="sidebar-link" onclick="showSection('presensi')">
                    <i class="fas fa-clipboard-list"></i> Data Presensi
                </a>
                <a href="#" class="sidebar-link" onclick="showSection('siswa')">
                    <i class="fas fa-user-graduate"></i> Data Siswa
                </a>
                <a href="#" class="sidebar-link" onclick="showSection('guru')">
                    <i class="fas fa-chalkboard-teacher"></i> Data Guru
                </a>
                <div class="sidebar-section-title">Sistem</div>
                <a href="#" class="sidebar-link" onclick="showSection('sistem')">
                    <i class="fas fa-cog"></i> Pengaturan
                </a>
            </nav>
            <div class="p-4 border-t border-white/10 mt-auto">
                <button onclick="logout()" class="sidebar-link w-full text-left hover:bg-red-500/20 hover:text-red-400">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <div class="main-content">

            <!-- TOP BAR -->
            <header class="bg-white/80 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-40">
                <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button onclick="toggleSidebar()" class="toggle-sidebar text-slate-600 hover:text-indigo-600 transition p-2 rounded-lg hover:bg-slate-100">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Dashboard Admin</h2>
                            <p class="text-xs text-slate-500 flex items-center gap-2">
                                <i class="fas fa-calendar-alt"></i>
                                <span id="currentDate">Memuat...</span>
                                <span class="text-emerald-500 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full live-dot"></span> Live
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-slate-600 hidden sm:block">
                            <i class="fas fa-clock text-indigo-500"></i>
                            <span id="currentTime">Memuat...</span>
                        </span>
                        <span class="text-sm text-slate-600"><i class="fas fa-user-circle text-purple-600 text-lg"></i> Admin</span>
                    </div>
                </div>
            </header>

            <!-- ============================================================ -->
            <!-- DASHBOARD SECTION -->
            <!-- ============================================================ -->
            <div id="section-dashboard" class="p-4 sm:p-6 lg:p-8">

                <!-- STATISTIK -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 text-center stat-card">
                        <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-full mx-auto mb-2">
                            <i class="fas fa-users text-xl text-purple-600"></i>
                        </div>
                        <p class="text-2xl font-bold text-purple-700" id="statTotalSiswa">125</p>
                        <p class="text-xs text-slate-500">Total Siswa</p>
                        <span class="text-[10px] text-emerald-500"><i class="fas fa-arrow-up"></i> 12%</span>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 text-center stat-card">
                        <div class="flex items-center justify-center w-12 h-12 bg-emerald-100 rounded-full mx-auto mb-2">
                            <i class="fas fa-user-graduate text-xl text-emerald-600"></i>
                        </div>
                        <p class="text-2xl font-bold text-emerald-700" id="statTotalGuru">48</p>
                        <p class="text-xs text-slate-500">Total Guru</p>
                        <span class="text-[10px] text-emerald-500"><i class="fas fa-arrow-up"></i> 5%</span>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 text-center stat-card">
                        <div class="flex items-center justify-center w-12 h-12 bg-amber-100 rounded-full mx-auto mb-2">
                            <i class="fas fa-clock text-xl text-amber-600"></i>
                        </div>
                        <p class="text-2xl font-bold text-amber-700" id="statHadirHariIni">107</p>
                        <p class="text-xs text-slate-500">Hadir Hari Ini</p>
                        <span class="text-[10px] text-amber-500"><i class="fas fa-arrow-down"></i> 3%</span>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 text-center stat-card">
                        <div class="flex items-center justify-center w-12 h-12 bg-rose-100 rounded-full mx-auto mb-2">
                            <i class="fas fa-exclamation-triangle text-xl text-rose-600"></i>
                        </div>
                        <p class="text-2xl font-bold text-rose-700" id="statAlpha">4</p>
                        <p class="text-xs text-slate-500">Alpha</p>
                        <span class="text-[10px] text-rose-500"><i class="fas fa-arrow-up"></i> 2%</span>
                    </div>
                </div>

                <!-- ========================================================== -->
                <!-- 3 GRAFIK: BAR, LINE, PIE (BERDASARKAN REKAP PER KELAS) -->
                <!-- ========================================================== -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                    <!-- GRAFIK 1: BAR CHART - Kehadiran per Kelas -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-slate-800">
                                <i class="fas fa-chart-bar text-purple-500 mr-2"></i>
                                Kehadiran per Kelas (Bulan Ini)
                            </h3>
                            <div class="flex items-center gap-2">
                                <select id="bulanSelect" class="text-xs border border-slate-200 rounded-lg px-2 py-1 bg-slate-50" onchange="updateAllCharts()">
                                    <option value="8">Agustus 2026</option>
                                    <option value="9">September 2026</option>
                                    <option value="10">Oktober 2026</option>
                                </select>
                                <span class="text-[10px] text-emerald-500 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full live-dot"></span> Real-Time
                                </span>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>

                    <!-- GRAFIK 2: LINE CHART - Tren Kehadiran 6 Bulan -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-slate-800">
                                <i class="fas fa-chart-line text-purple-500 mr-2"></i>
                                Tren Kehadiran (6 Bulan)
                            </h3>
                            <span class="text-[10px] text-emerald-500 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full live-dot"></span> Real-Time
                            </span>
                        </div>
                        <div class="chart-container">
                            <canvas id="lineChart"></canvas>
                        </div>
                    </div>

                </div>

                <!-- GRAFIK 3: PIE/DOUGHNUT CHART - Perbandingan Status -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                        <h3 class="font-semibold text-slate-800 mb-4">
                            <i class="fas fa-chart-pie text-purple-500 mr-2"></i>
                            Perbandingan Status Kehadiran
                        </h3>
                        <div class="chart-container-lg" style="height:220px;">
                            <canvas id="pieChart"></canvas>
                        </div>
                        <div class="flex justify-center gap-4 mt-3 text-xs">
                            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-emerald-500 rounded-full"></span> Hadir</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-amber-400 rounded-full"></span> Izin</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-400 rounded-full"></span> Sakit</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-gray-400 rounded-full"></span> Alpha</span>
                        </div>
                    </div>

                    <!-- RINGKASAN CEPAT -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6 lg:col-span-2">
                        <h3 class="font-semibold text-slate-800 mb-4">
                            <i class="fas fa-school text-purple-500 mr-2"></i>
                            Ringkasan Cepat
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-slate-50 rounded-xl p-4 text-center">
                                <p class="text-xs text-slate-500">Rata-rata Kehadiran</p>
                                <p class="text-2xl font-bold text-emerald-600" id="avgKehadiran">87%</p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4 text-center">
                                <p class="text-xs text-slate-500">Siswa Teraktif</p>
                                <p class="text-lg font-bold text-purple-600" id="siswaTeraktif">Najla</p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4 text-center">
                                <p class="text-xs text-slate-500">Kelas Terbaik</p>
                                <p class="text-lg font-bold text-emerald-600" id="kelasTerbaik">XII.RPL</p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4 text-center">
                                <p class="text-xs text-slate-500">Total Siswa</p>
                                <p class="text-2xl font-bold text-amber-600" id="totalHari">125</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RINGKASAN PER KELAS -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/60 bg-slate-50/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-table text-purple-500"></i>
                            <h3 class="font-semibold text-slate-800">Ringkasan Per Kelas</h3>
                            <span class="text-xs bg-purple-100 text-purple-600 px-2 py-0.5 rounded-full"><span id="totalKelas">4</span> Kelas</span>
                            <span class="text-xs bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full flex items-center gap-1">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full live-dot"></span> Live
                            </span>
                        </div>
                        <div class="text-xs text-slate-400">
                            <i class="fas fa-calendar-alt"></i> <span id="periodeKelas">Agustus 2026</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200/60">
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Kelas</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Hadir</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Izin</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Sakit</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Alpha</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody id="kelasTableBody"></tbody>
                        </table>
                    </div>
                </div>

            </div>

<!-- ============================================================ -->
<!-- SECTION: DATA PRESENSI (LENGKAP DENGAN SIMBOL AKSI) -->
<!-- ============================================================ -->
<div id="section-presensi" class="hidden p-4 sm:p-6 lg:p-8">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-clipboard-list text-purple-500 text-xl"></i>
                <h3 class="font-semibold text-slate-800">Data Presensi Hari Ini</h3>
                <span class="text-xs bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full flex items-center gap-1">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full live-dot"></span> Live
                </span>
                <span class="text-xs bg-purple-100 text-purple-600 px-2 py-0.5 rounded-full"><span id="totalPresensi">5</span> Data</span>
            </div>
            <button onclick="openModalPresensi()" class="btn-add"><i class="fas fa-plus mr-1"></i> Tambah Presensi</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/60">
                        <th class="px-4 py-2 text-left">No</th>
                        <th class="px-4 py-2 text-left">Nama</th>
                        <th class="px-4 py-2 text-left">NIS</th>
                        <th class="px-4 py-2 text-left">Kelas</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Waktu</th>
                        <th class="px-4 py-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="presensiTableBody">
                    <!-- Data Siswa 1 -->
                    <tr class="table-row-hover">
                        <td class="px-4 py-2">1</td>
                        <td class="px-4 py-2 font-medium text-slate-800">Najla</td>
                        <td class="px-4 py-2">12345</td>
                        <td class="px-4 py-2">XII.RPL</td>
                        <td class="px-4 py-2">
                            <span class="badge-hadir px-2 py-0.5 rounded-full text-xs font-medium">
                                <i class="fas fa-check-circle mr-1"></i> Hadir
                            </span>
                        </td>
                        <td class="px-4 py-2">08:15 WIB</td>
                        <td class="px-4 py-2 text-center">
                            <div class="action-buttons">
                                <button onclick="editPresensi(1)" class="btn-edit" title="Edit Data">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deletePresensi(1)" class="btn-delete" title="Hapus Data">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Data Siswa 2 -->
                    <tr class="table-row-hover">
                        <td class="px-4 py-2">2</td>
                        <td class="px-4 py-2 font-medium text-slate-800">Yasmin</td>
                        <td class="px-4 py-2">12346</td>
                        <td class="px-4 py-2">XII.RPL</td>
                        <td class="px-4 py-2">
                            <span class="badge-hadir px-2 py-0.5 rounded-full text-xs font-medium">
                                <i class="fas fa-check-circle mr-1"></i> Hadir
                            </span>
                        </td>
                        <td class="px-4 py-2">08:20 WIB</td>
                        <td class="px-4 py-2 text-center">
                            <div class="action-buttons">
                                <button onclick="editPresensi(2)" class="btn-edit" title="Edit Data">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deletePresensi(2)" class="btn-delete" title="Hapus Data">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Data Siswa 3 -->
                    <tr class="table-row-hover">
                        <td class="px-4 py-2">3</td>
                        <td class="px-4 py-2 font-medium text-slate-800">Dina</td>
                        <td class="px-4 py-2">12347</td>
                        <td class="px-4 py-2">XII.RPL</td>
                        <td class="px-4 py-2">
                            <span class="badge-izin px-2 py-0.5 rounded-full text-xs font-medium">
                                <i class="fas fa-pen mr-1"></i> Izin
                            </span>
                        </td>
                        <td class="px-4 py-2">-</td>
                        <td class="px-4 py-2 text-center">
                            <div class="action-buttons">
                                <button onclick="editPresensi(3)" class="btn-edit" title="Edit Data">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deletePresensi(3)" class="btn-delete" title="Hapus Data">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Data Siswa 4 -->
                    <tr class="table-row-hover">
                        <td class="px-4 py-2">4</td>
                        <td class="px-4 py-2 font-medium text-slate-800">Alex</td>
                        <td class="px-4 py-2">12348</td>
                        <td class="px-4 py-2">XII.RPL</td>
                        <td class="px-4 py-2">
                            <span class="badge-hadir px-2 py-0.5 rounded-full text-xs font-medium">
                                <i class="fas fa-check-circle mr-1"></i> Hadir
                            </span>
                        </td>
                        <td class="px-4 py-2">08:10 WIB</td>
                        <td class="px-4 py-2 text-center">
                            <div class="action-buttons">
                                <button onclick="editPresensi(4)" class="btn-edit" title="Edit Data">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deletePresensi(4)" class="btn-delete" title="Hapus Data">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Data Siswa 5 -->
                    <tr class="table-row-hover">
                        <td class="px-4 py-2">5</td>
                        <td class="px-4 py-2 font-medium text-slate-800">Arjuna</td>
                        <td class="px-4 py-2">12349</td>
                        <td class="px-4 py-2">XII.RPL</td>
                        <td class="px-4 py-2">
                            <span class="badge-sakit px-2 py-0.5 rounded-full text-xs font-medium">
                                <i class="fas fa-thermometer-half mr-1"></i> Sakit
                            </span>
                        </td>
                        <td class="px-4 py-2">-</td>
                        <td class="px-4 py-2 text-center">
                            <div class="action-buttons">
                                <button onclick="editPresensi(5)" class="btn-edit" title="Edit Data">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deletePresensi(5)" class="btn-delete" title="Hapus Data">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- SECTION: DATA SISWA (LENGKAP) -->
<!-- ============================================================ -->
<div id="section-siswa" class="hidden p-4 sm:p-6 lg:p-8">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-user-graduate text-purple-500 text-xl"></i>
                <h3 class="font-semibold text-slate-800">Data Siswa</h3>
                <span class="text-xs bg-purple-100 text-purple-600 px-2 py-0.5 rounded-full"><span id="totalSiswaCount">5</span> Siswa</span>
            </div>
            <button onclick="openModal('siswa')" class="btn-add"><i class="fas fa-plus mr-1"></i> Tambah Siswa</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/60">
                        <th class="px-4 py-2 text-left">No</th>
                        <th class="px-4 py-2 text-left">Nama</th>
                        <th class="px-4 py-2 text-left">NIS</th>
                        <th class="px-4 py-2 text-left">Kelas</th>
                        <th class="px-4 py-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="siswaTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- SECTION: DATA GURU (LENGKAP) -->
<!-- ============================================================ -->
<div id="section-guru" class="hidden p-4 sm:p-6 lg:p-8">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-chalkboard-teacher text-purple-500 text-xl"></i>
                <h3 class="font-semibold text-slate-800">Data Guru</h3>
                <span class="text-xs bg-purple-100 text-purple-600 px-2 py-0.5 rounded-full"><span id="totalGuruCount">3</span> Guru</span>
            </div>
            <button onclick="openModal('guru')" class="btn-add"><i class="fas fa-plus mr-1"></i> Tambah Guru</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/60">
                        <th class="px-4 py-2 text-left">No</th>
                        <th class="px-4 py-2 text-left">Nama</th>
                        <th class="px-4 py-2 text-left">NIP</th>
                        <th class="px-4 py-2 text-left">Mapel</th>
                        <th class="px-4 py-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="guruTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- SECTION: PENGATURAN SISTEM (LENGKAP) -->
<!-- ============================================================ -->
<div id="section-sistem" class="hidden p-4 sm:p-6 lg:p-8">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
        <div class="flex items-center gap-3 mb-4">
            <i class="fas fa-cog text-purple-500 text-xl"></i>
            <h3 class="font-semibold text-slate-800">Pengaturan Sistem</h3>
        </div>
        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                <div>
                    <p class="font-medium text-slate-700">Mode Maintenance</p>
                    <p class="text-xs text-slate-500">Nonaktifkan akses sementara untuk pemeliharaan</p>
                </div>
                <button onclick="toggleMaintenance()" class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg transition">
                    <i class="fas fa-power-off mr-1"></i> Nonaktif
                </button>
            </div>
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                <div>
                    <p class="font-medium text-slate-700">Auto Backup</p>
                    <p class="text-xs text-slate-500">Backup otomatis setiap hari pukul 00:00</p>
                </div>
                <button onclick="toggleAutoBackup()" class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm px-4 py-2 rounded-lg transition">
                    <i class="fas fa-check mr-1"></i> Aktif
                </button>
            </div>
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                <div>
                    <p class="font-medium text-slate-700">Notifikasi Email</p>
                    <p class="text-xs text-slate-500">Kirim notifikasi ke email guru setiap pagi</p>
                </div>
                <button onclick="toggleNotifikasi()" class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm px-4 py-2 rounded-lg transition">
                    <i class="fas fa-check mr-1"></i> Aktif
                </button>
            </div>
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                <div>
                    <p class="font-medium text-slate-700">Sinkronisasi Data</p>
                    <p class="text-xs text-slate-500">Sinkronkan data dengan server pusat</p>
                </div>
                <button onclick="sinkronData()" class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-4 py-2 rounded-lg transition">
                    <i class="fas fa-sync mr-1"></i> Sinkron
                </button>
            </div>
            <hr />
            <div class="flex flex-wrap gap-3">
                <button onclick="clearCache()" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-broom mr-1"></i> Bersihkan Cache
                </button>
                <button onclick="resetSystem()" class="bg-red-500 hover:bg-red-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Reset Sistem
                </button>
                <button onclick="exportData()" class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-download mr-1"></i> Export Data
                </button>
                <button onclick="importData()" class="bg-purple-500 hover:bg-purple-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-upload mr-1"></i> Import Data
                </button>
            </div>
        </div>
    </div>
</div>

    <!-- ============================================================ -->
    <!-- JAVASCRIPT LENGKAP -->
    <!-- ============================================================ -->
    <script>
        // ================================================================
        // DATA KELAS (REKAP BULANAN)
        // ================================================================
        const kelasData = [
            { kelas: 'XII.RPL', total: 32, hadir: 28, izin: 2, sakit: 1, alpha: 1 },
            { kelas: 'XII.TKJ', total: 30, hadir: 25, izin: 3, sakit: 1, alpha: 1 },
            { kelas: 'XI.RPL', total: 35, hadir: 30, izin: 2, sakit: 2, alpha: 1 },
            { kelas: 'XI.TKJ', total: 28, hadir: 24, izin: 2, sakit: 1, alpha: 1 }
        ];

        // Data tren 6 bulan
        const trenData = [78, 82, 85, 80, 88, 87];

        // Chart instances
        let barChart, lineChart, pieChart;

        // ================================================================
        // RENDER TABEL KELAS
        // ================================================================
        function renderKelasTable() {
            const tbody = document.getElementById('kelasTableBody');
            let html = '';
            kelasData.forEach(item => {
                const persentase = item.total > 0 ? Math.round((item.hadir / item.total) * 100) : 0;
                const color = persentase >= 85 ? 'emerald' : persentase >= 70 ? 'amber' : 'rose';
                html += `
                    <tr class="table-row-hover">
                        <td class="px-6 py-4 font-medium text-slate-800">${item.kelas}</td>
                        <td class="px-6 py-4 text-center text-slate-600">${item.total}</td>
                        <td class="px-6 py-4 text-center text-emerald-600 font-semibold">${item.hadir}</td>
                        <td class="px-6 py-4 text-center text-amber-600">${item.izin}</td>
                        <td class="px-6 py-4 text-center text-red-500">${item.sakit}</td>
                        <td class="px-6 py-4 text-center text-gray-500">${item.alpha}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-${color}-100 text-${color}-700">
                                ${persentase}%
                            </span>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
            document.getElementById('totalKelas').textContent = kelasData.length;
        }

        // ================================================================
        // INISIALISASI 3 GRAFIK
        // ================================================================
        function initCharts() {
            const labels = kelasData.map(k => k.kelas);
            const hadirData = kelasData.map(k => k.hadir);
            const izinData = kelasData.map(k => k.izin);
            const sakitData = kelasData.map(k => k.sakit);
            const alphaData = kelasData.map(k => k.alpha);

            // 1. BAR CHART - Kehadiran per Kelas
            const ctx1 = document.getElementById('barChart').getContext('2d');
            barChart = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Hadir',
                        data: hadirData,
                        backgroundColor: ['#818cf8', '#34d399', '#fbbf24', '#f472b6'],
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Hadir: ' + context.parsed.y + ' siswa';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 2. LINE CHART - Tren Kehadiran 6 Bulan
            const ctx2 = document.getElementById('lineChart').getContext('2d');
            lineChart = new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: ['Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu'],
                    datasets: [{
                        label: 'Kehadiran (%)',
                        data: trenData,
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124, 58, 237, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#7c3aed',
                        pointBorderColor: 'white',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Kehadiran: ' + context.parsed.y + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, max: 100, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 3. PIE/DOUGHNUT CHART - Perbandingan Status
            const totalHadir = kelasData.reduce((sum, k) => sum + k.hadir, 0);
            const totalIzin = kelasData.reduce((sum, k) => sum + k.izin, 0);
            const totalSakit = kelasData.reduce((sum, k) => sum + k.sakit, 0);
            const totalAlpha = kelasData.reduce((sum, k) => sum + k.alpha, 0);

            const ctx3 = document.getElementById('pieChart').getContext('2d');
            pieChart = new Chart(ctx3, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Izin', 'Sakit', 'Alpha'],
                    datasets: [{
                        data: [totalHadir, totalIzin, totalSakit, totalAlpha],
                        backgroundColor: ['#10b981', '#fbbf24', '#ef4444', '#94a3b8'],
                        borderColor: 'white',
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? Math.round((context.parsed / total) * 100) : 0;
                                    return context.label + ': ' + context.parsed + ' siswa (' + percentage + '%)';
                                }
                            }
                        }
                    },
                    cutout: '60%',
                }
            });
        }

        // ================================================================
        // UPDATE ALL CHARTS (SAAT BULAN BERUBAH)
        // ================================================================
        function updateAllCharts() {
            const bulan = document.getElementById('bulanSelect').value;
            const bulanNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            document.getElementById('periodeKelas').textContent = bulanNames[parseInt(bulan)] + ' 2026';

            // Update data dengan variasi random agar terlihat dinamis
            const newHadir = kelasData.map(k => {
                const variation = Math.floor(Math.random() * 4) - 2;
                return Math.max(0, Math.min(k.total, k.hadir + variation));
            });

            // Update Bar Chart
            barChart.data.datasets[0].data = newHadir;
            barChart.update();

            // Update Pie Chart dengan data total
            const totalHadir = newHadir.reduce((a, b) => a + b, 0);
            const totalIzin = kelasData.reduce((sum, k) => sum + k.izin, 0) + Math.floor(Math.random() * 3) - 1;
            const totalSakit = kelasData.reduce((sum, k) => sum + k.sakit, 0) + Math.floor(Math.random() * 2) - 1;
            const totalAlpha = kelasData.reduce((sum, k) => sum + k.alpha, 0) + Math.floor(Math.random() * 2);
            pieChart.data.datasets[0].data = [totalHadir, Math.max(0, totalIzin), Math.max(0, totalSakit), Math.max(0, totalAlpha)];
            pieChart.update();

            // Update statistik
            const total = kelasData.reduce((sum, k) => sum + k.total, 0);
            const hadir = newHadir.reduce((a, b) => a + b, 0);
            document.getElementById('statHadirHariIni').textContent = hadir;
            document.getElementById('avgKehadiran').textContent = total > 0 ? Math.round((hadir / total) * 100) + '%' : '0%';
        }

        // ================================================================
        // SIDEBAR TOGGLE & SECTION
        // ================================================================
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }

        function showSection(section) {
            document.querySelectorAll('[id^="section-"]').forEach(el => el.classList.add('hidden'));
            document.getElementById('section-' + section).classList.remove('hidden');
            document.querySelectorAll('.sidebar-link').forEach(el => el.classList.remove('active'));
            event.target.closest('.sidebar-link').classList.add('active');
            if (window.innerWidth <= 1024) toggleSidebar();
        }

        function logout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = "{{ route('login') }}";
            }
        }

        // ================================================================
        // UPDATE CLOCK REAL-TIME
        // ================================================================
        function updateClock() {
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('id-ID', {
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
            });
            document.getElementById('currentDate').textContent = now.toLocaleDateString('id-ID', {
                weekday: 'long', day: '2-digit', month: '2-digit', year: 'numeric'
            });
        }

        // ================================================================
        // INIT
        // ================================================================
        document.addEventListener('DOMContentLoaded', function() {
            renderKelasTable();
            initCharts();
            updateClock();
            setInterval(updateClock, 1000);

            // Update charts setiap 30 detik (simulasi real-time)
            setInterval(updateAllCharts, 30000);
        });
    </script>

</body>
</html>