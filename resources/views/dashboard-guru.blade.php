<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Hadirin.web · Dashboard Guru</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet" />
<style>
body { font-family: 'Inter', sans-serif; background: #f1f5f9; }
.sidebar { width: 260px; min-height: 100vh; background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); overflow-y: auto; }
.sidebar-link { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: rgba(255,255,255,0.6); border-radius: 10px; font-size: 14px; cursor: pointer; margin: 0 8px; transition: 0.2s; }
.sidebar-link:hover { background: rgba(255,255,255,0.08); color: white; }
.sidebar-link.active { background: rgba(59,130,246,0.2); color: white; border-left: 3px solid #3b82f6; }
.sidebar-section-title { color: rgba(255,255,255,0.3); font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px 20px 8px; font-weight: 600; }
.topbar { background: white; border-bottom: 1px solid #e2e8f0; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 40; }
.stat-card { border-radius: 12px; padding: 20px; color: white; }
.stat-blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.stat-green { background: linear-gradient(135deg, #10b981, #059669); }
.stat-orange { background: linear-gradient(135deg, #f97316, #ea580c); }
.stat-red { background: linear-gradient(135deg, #ef4444, #dc2626); }
.section-content { display: none; }
.section-content.active { display: block; }
.chart-container { position: relative; height: 280px; }
.badge-hadir { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 999px; font-size: 11px; }
.badge-izin { background: #fef9c3; color: #854d0e; padding: 4px 10px; border-radius: 999px; font-size: 11px; }
.badge-sakit { background: #fce4ec; color: #b91c1c; padding: 4px 10px; border-radius: 999px; font-size: 11px; }
.badge-alpha { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 999px; font-size: 11px; }
.progress-bar { height: 8px; border-radius: 4px; background: #e2e8f0; overflow: hidden; }
.progress-fill { height: 100%; border-radius: 4px; }
.badge-scan { background: #e0e7ff; color: #4338ca; padding: 4px 10px; border-radius: 999px; font-size: 11px; }
.badge-id { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 999px; font-size: 11px; }
.badge-izin-metode { background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 999px; font-size: 11px; }
</style>
</head>
<body>

<div class="flex">

<!-- SIDEBAR -->
<aside class="sidebar flex-shrink-0">
    <div class="p-5 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center text-white font-bold text-xl">H</div>
            <div>
                <span class="text-white font-bold text-lg">Hadirin</span>
                <span class="text-blue-300 text-xs block">Dashboard Guru</span>
            </div>
        </div>
    </div>

    <div class="p-4 mx-3 mt-3 bg-white/5 rounded-xl border border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-500/30 rounded-full flex items-center justify-center text-white font-bold" id="sidebarInitial">G</div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-sm font-semibold truncate" id="sidebarGuruNama">-</p>
                <p class="text-blue-300 text-xs truncate" id="sidebarGuruKelas">-</p>
            </div>
        </div>
    </div>

    <nav class="p-4">
        <div class="sidebar-section-title">Menu Utama</div>
        <a class="sidebar-link active" onclick="showSection('dashboard', this)">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a class="sidebar-link" onclick="showSection('progres', this)">
            <i class="fas fa-chart-line"></i> Progres Absensi
        </a>
        <a class="sidebar-link" onclick="showSection('kelola', this)">
            <i class="fas fa-users-cog"></i> Kelola Data Kelas
        </a>
        <a class="sidebar-link" onclick="showSection('laporan', this)">
            <i class="fas fa-file-alt"></i> Laporan Bulanan
        </a>
        <a class="sidebar-link" onclick="showSection('realtime', this)">
            <i class="fas fa-clock"></i> Real-Time Monitoring
        </a>

        <div class="sidebar-section-title">Akun</div>
        <a href="{{ route('beranda') }}" class="sidebar-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
        </a>
        <a href="{{ route('identitas.guru') }}" class="sidebar-link">
            <i class="fas fa-user-edit"></i> Ubah Identitas
        </a>
        <a onclick="logout()" class="sidebar-link">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</aside>

<!-- MAIN CONTENT -->
<div class="flex-1 min-h-screen">
    <header class="topbar">
        <div>
            <nav class="text-xs text-slate-400 mb-1">
                Home <i class="fas fa-chevron-right text-[8px] mx-1"></i> 
                <span class="text-blue-600 font-medium" id="breadcrumb">Dashboard</span>
            </nav>
            <h2 class="text-lg font-bold text-slate-800" id="pageTitle">Dashboard</h2>
        </div>
        <span class="text-sm text-slate-600" id="currentDate">-</span>
    </header>

    <!-- SECTION DASHBOARD -->
    <div id="section-dashboard" class="section-content active p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="stat-card stat-blue">
                <p class="text-2xl font-bold" id="statTotalSiswa">0</p>
                <p class="text-xs opacity-90 mt-1">Total Siswa</p>
            </div>
            <div class="stat-card stat-green">
                <p class="text-2xl font-bold" id="statHadir">0</p>
                <p class="text-xs opacity-90 mt-1">Hadir Hari Ini</p>
            </div>
            <div class="stat-card stat-orange">
                <p class="text-2xl font-bold" id="statIzin">0</p>
                <p class="text-xs opacity-90 mt-1">Izin / Sakit</p>
            </div>
            <div class="stat-card stat-red">
                <p class="text-2xl font-bold" id="statAlpha">0</p>
                <p class="text-xs opacity-90 mt-1">Alpha</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl border p-5">
                <h3 class="font-semibold text-sm mb-4">📊 Presensi Per Bulan</h3>
                <div class="chart-container"><canvas id="barChart"></canvas></div>
            </div>
            <div class="bg-white rounded-xl border p-5">
                <h3 class="font-semibold text-sm mb-4">🥧 Ringkasan Kehadiran</h3>
                <div class="chart-container"><canvas id="pieChart"></canvas></div>
            </div>
        </div>
    </div>

    <!-- SECTION PROGRES -->
    <div id="section-progres" class="section-content p-6">
        <div class="bg-white rounded-xl border p-6">
            <h3 class="font-semibold mb-4">📈 Progres Absensi <span id="kelasProgres" class="text-blue-600">-</span></h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="bg-blue-50 rounded-xl p-4">
                    <p class="text-xs text-slate-600">Rata-rata</p>
                    <p class="text-2xl font-bold text-blue-600" id="progresAvg">0%</p>
                </div>
                <div class="bg-emerald-50 rounded-xl p-4">
                    <p class="text-xs text-slate-600">Tertinggi</p>
                    <p class="text-2xl font-bold text-emerald-600" id="progresMax">0%</p>
                </div>
                <div class="bg-amber-50 rounded-xl p-4">
                    <p class="text-xs text-slate-600">Terendah</p>
                    <p class="text-2xl font-bold text-amber-600" id="progresMin">0%</p>
                </div>
                <div class="bg-purple-50 rounded-xl p-4">
                    <p class="text-xs text-slate-600">Total</p>
                    <p class="text-2xl font-bold text-purple-600" id="progresTotal">0</p>
                </div>
            </div>
            <div id="progresList" class="space-y-4"></div>
        </div>
    </div>

    <!-- SECTION KELOLA -->
    <div id="section-kelola" class="section-content p-6">
        <div class="bg-white rounded-xl border p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold">👥 Kelola Data Kelas <span id="kelasKelola" class="text-blue-600">-</span></h3>
                <button onclick="tambahSiswa()" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg transition">
                    <i class="fas fa-plus mr-1"></i> Tambah Siswa
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b">
                            <th class="px-4 py-3 text-left">No</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">NIS</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="kelolaTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SECTION LAPORAN -->
    <div id="section-laporan" class="section-content p-6">
        <div class="bg-white rounded-xl border p-6">
            <h3 class="font-semibold mb-4">📄 Laporan Bulanan</h3>
            <div class="mb-4 flex gap-2">
                <select id="laporanBulan" class="border rounded-lg px-3 py-2 text-sm">
                    <option>Agustus 2026</option>
                    <option>September 2026</option>
                    <option>Oktober 2026</option>
                </select>
                <button onclick="window.print()" class="bg-blue-600 text-white text-sm px-4 py-2 rounded-lg">
                    <i class="fas fa-print mr-1"></i> Cetak
                </button>
                <button onclick="exportLaporanCSV()" class="bg-emerald-600 text-white text-sm px-4 py-2 rounded-lg">
                    <i class="fas fa-file-excel mr-1"></i> Export
                </button>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b">
                        <th class="px-3 py-2 text-left">No</th>
                        <th class="px-3 py-2 text-left">Nama</th>
                        <th class="px-3 py-2 text-center">Hadir</th>
                        <th class="px-3 py-2 text-center">Izin</th>
                        <th class="px-3 py-2 text-center">Sakit</th>
                        <th class="px-3 py-2 text-center">Alpha</th>
                        <th class="px-3 py-2 text-center">%</th>
                    </tr>
                </thead>
                <tbody id="laporanTableBody"></tbody>
            </table>
        </div>
    </div>

<!-- SECTION REALTIME -->
<div id="section-realtime" class="section-content p-6">
    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold">⏰ Real-Time Monitoring
                <span class="text-xs bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full ml-2">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full inline-block animate-pulse"></span> Live
                </span>
            </h3>
            <button onclick="loadRealtimeFromStorage()" class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-600 px-3 py-1.5 rounded-lg transition">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="bg-emerald-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-emerald-600" id="rtHadir">0</p>
                <p class="text-xs">Hadir</p>
            </div>
            <div class="bg-amber-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-amber-600" id="rtIzin">0</p>
                <p class="text-xs">Izin</p>
            </div>
            <div class="bg-red-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-red-600" id="rtSakit">0</p>
                <p class="text-xs">Sakit</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-gray-600" id="rtAlpha">0</p>
                <p class="text-xs">Alpha</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b">
                        <th class="px-4 py-2 text-left">Waktu</th>
                        <th class="px-4 py-2 text-left">Nama</th>
                        <th class="px-4 py-2 text-left">Kelas</th>
                        <th class="px-4 py-2 text-left">Metode</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Keterangan</th>
                    </tr>
                </thead>
                <tbody id="realtimeBody"></tbody>
            </table>
        </div>

        <div class="mt-4 text-xs text-slate-400 text-center" id="realtimeEmpty" style="display:none;">
            <i class="fas fa-inbox mr-1"></i> Belum ada siswa yang melakukan absensi hari ini
        </div>
    </div>
</div>

<script>
// ============================================================
// ✅ DATA TERPUSAT - SINKRON DENGAN ADMIN
// ============================================================
const GLOBAL_DATA_KEY = 'hadirin_global_data';

// Data default (dipakai kalau belum ada di localStorage)
const defaultSiswaPerKelas = {
    'XII.RPL': [
        { nama: 'Najla Mutia', nis: '12345' },
        { nama: 'Yasmin Zahra', nis: '12346' },
        { nama: 'Dina Karima', nis: '12347' },
        { nama: 'Alex Pratama', nis: '12348' },
        { nama: 'Arjuna Wijaya', nis: '12349' }
    ],
    'XI.RPL': [
        { nama: 'Budi Santoso', nis: '22345' },
        { nama: 'Siti Rahayu', nis: '22346' },
        { nama: 'Ahmad Fauzi', nis: '22347' }
    ],
    'XII.TKJ': [
        { nama: 'Rizky Ramadhan', nis: '32345' },
        { nama: 'Maya Sari', nis: '32346' }
    ],
    'X.RPL': [
        { nama: 'Hana Permata', nis: '42345' },
        { nama: 'Gilang Pratama', nis: '42346' }
    ]
};

// ============================================================
// FUNGSI LOAD/SAVE DATA GLOBAL
// ============================================================
function loadGlobalData() {
    const saved = localStorage.getItem(GLOBAL_DATA_KEY);
    if (saved) {
        try {
            return JSON.parse(saved);
        } catch(e) {
            console.error('Error parsing global data:', e);
        }
    }
    // Kalau belum ada, simpan default
    const defaultData = {
        siswaPerKelas: defaultSiswaPerKelas,
        lastUpdate: new Date().toISOString()
    };
    localStorage.setItem(GLOBAL_DATA_KEY, JSON.stringify(defaultData));
    return defaultData;
}

function saveGlobalData(data) {
    data.lastUpdate = new Date().toISOString();
    localStorage.setItem(GLOBAL_DATA_KEY, JSON.stringify(data));
}

// ============================================================
// STATE
// ============================================================
let identitasGuru = null;
let presensiList = [];
let nextId = 1;
let globalData = loadGlobalData();

// ============================================================
// FUNGSI PINDAH SECTION
// ============================================================
function showSection(nama, element) {
    console.log('🔄 Pindah ke:', nama);
    
    document.querySelectorAll('.section-content').forEach(el => {
        el.classList.remove('active');
        el.style.display = 'none';
    });
    
    const target = document.getElementById('section-' + nama);
    if (target) {
        target.classList.add('active');
        target.style.display = 'block';
    }
    
    document.querySelectorAll('.sidebar-link').forEach(el => el.classList.remove('active'));
    if (element) element.classList.add('active');
    
    const titles = {
        'dashboard': 'Dashboard',
        'progres': 'Progres Absensi',
        'kelola': 'Kelola Data Kelas',
        'laporan': 'Laporan Bulanan',
        'realtime': 'Real-Time Monitoring'
    };
    document.getElementById('breadcrumb').textContent = titles[nama] || 'Dashboard';
    document.getElementById('pageTitle').textContent = titles[nama] || 'Dashboard';

// ✅ Muat data real-time dari localStorage saat section dibuka
if (nama === 'realtime') {
    loadRealtimeFromStorage();  // ← HARUS loadRealtimeFromStorage, bukan renderRealtime
}
}

// ============================================================
// LOAD DATA GURU
// ============================================================
function cekIdentitasGuru() {
    const saved = localStorage.getItem('identitas_guru');
    if (saved) {
        try {
            identitasGuru = JSON.parse(saved);
            console.log('✅ Guru:', identitasGuru);

            document.getElementById('sidebarInitial').textContent = identitasGuru.nama.charAt(0).toUpperCase();
            document.getElementById('sidebarGuruNama').textContent = identitasGuru.nama;
            document.getElementById('sidebarGuruKelas').textContent = identitasGuru.kelasLengkap;
            document.getElementById('kelasProgres').textContent = identitasGuru.kelasLengkap;
            document.getElementById('kelasKelola').textContent = identitasGuru.kelasLengkap;

            loadDataKelas(identitasGuru.kelasLengkap);
            updateClock();

        } catch(e) {
            console.error('Error:', e);
            window.location.href = "{{ route('identitas.guru') }}";
        }
    } else {
        window.location.href = "{{ route('identitas.guru') }}";
    }
}

// ============================================================
// LOAD DATA KELAS (DARI GLOBAL DATA)
// ============================================================
function loadDataKelas(kelas) {
    presensiList = [];
    const students = globalData.siswaPerKelas[kelas] || globalData.siswaPerKelas['XII.RPL'] || [];
    const statuses = ['Hadir', 'Hadir', 'Hadir', 'Izin', 'Sakit'];
    const metodes = ['Scan QR', 'ID Unik', 'Izin/Sakit'];
    const now = new Date();

    for (let i = 0; i < students.length; i++) {
        const time = new Date(now);
        time.setMinutes(now.getMinutes() - (students.length - 1 - i) * 5);
        
        presensiList.push({
            id: nextId++,
            nama: students[i].nama,
            nis: students[i].nis,
            kelas: kelas,
            waktu: time,
            metode: metodes[i % metodes.length],
            status: statuses[i % statuses.length]
        });
    }

    // Render semua section KECUALI realtime
    renderDashboard();
    renderProgres();
    renderKelola(kelas);
    renderLaporan();
    initCharts();
}
/* ============================================================ */
/* REAL-TIME MONITORING - Sinkron dengan halaman absen siswa    */
/* ============================================================ */

const STORAGE_KEY_SISWA_ABSEN = 'siswa_absen';
const STORAGE_KEY_DAFTAR_ABSEN = 'daftar_absen';

// Ambil semua data absensi dari localStorage
function loadAllAbsensiSiswa() {
    let semua = [];

    // Sumber utama: daftar_absen
    try {
        const daftar = JSON.parse(localStorage.getItem(STORAGE_KEY_DAFTAR_ABSEN) || '[]');
        if (Array.isArray(daftar)) semua = daftar;
    } catch (e) {
        console.warn('Gagal parse daftar_absen:', e);
    }

    // Fallback: siswa_absen (kalau belum ada di daftar)
    try {
        const terakhir = JSON.parse(localStorage.getItem(STORAGE_KEY_SISWA_ABSEN) || 'null');
        if (terakhir && terakhir.timestamp) {
            const sudahAda = semua.some(d =>
                d.nama === terakhir.nama &&
                d.timestamp === terakhir.timestamp
            );
            if (!sudahAda) semua.push(terakhir);
        }
    } catch (e) {
        console.warn('Gagal parse siswa_absen:', e);
    }

    // Urutkan terbaru di atas
    semua.sort((a, b) => (b.timestamp || 0) - (a.timestamp || 0));
    return semua;
}

// Format waktu
function formatWaktuRealtime(item) {
    if (item.waktu && typeof item.waktu === 'string' && item.waktu.trim() !== '') {
        return item.waktu;
    }
    if (item.timestamp) {
        const d = new Date(item.timestamp);
        return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }
    return '-';
}

// Badge metode
function metodeBadgeRealtime(metode) {
    if (!metode) return `<span class="text-slate-300 italic">-</span>`;
    const m = String(metode);
    if (m.includes('Scan QR')) {
        return `<span class="badge-scan"><i class="fas fa-camera mr-1"></i> Scan QR</span>`;
    }
    if (m.includes('ID Unik')) {
        return `<span class="badge-id"><i class="fas fa-keyboard mr-1"></i> ID Unik</span>`;
    }
    if (m.includes('Izin') || m.includes('Sakit')) {
        return `<span class="badge-izin-metode"><i class="fas fa-file-medical-alt mr-1"></i> Izin/Sakit</span>`;
    }
    return `<span class="badge-alpha">${m}</span>`;
}

// Badge status
function statusBadgeRealtime(status) {
    if (status === 'Hadir') return `<span class="badge-hadir"><i class="fas fa-check-circle mr-1"></i> Hadir</span>`;
    if (status === 'Izin')  return `<span class="badge-izin"><i class="fas fa-pen mr-1"></i> Izin</span>`;
    if (status === 'Sakit') return `<span class="badge-sakit"><i class="fas fa-thermometer-half mr-1"></i> Sakit</span>`;
    return `<span class="badge-alpha"><i class="fas fa-times-circle mr-1"></i> Alpha</span>`;
}

// Render real-time monitoring dari localStorage — INI YANG DIPAKAI
function loadRealtimeFromStorage() {
    const tbody = document.getElementById('realtimeBody');
    const emptyEl = document.getElementById('realtimeEmpty');
    if (!tbody) return;

    // ✅ Ambil data asli dari halaman absen siswa
    const semua = loadAllAbsensiSiswa();

    // Hitung statistik
    let hadir = 0, izin = 0, sakit = 0, alpha = 0;
    semua.forEach(d => {
        if (d.status === 'Hadir') hadir++;
        else if (d.status === 'Izin') izin++;
        else if (d.status === 'Sakit') sakit++;
        else alpha++;
    });

    const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    setTxt('rtHadir', hadir);
    setTxt('rtIzin', izin);
    setTxt('rtSakit', sakit);
    setTxt('rtAlpha', alpha);

    // Update dashboard stat cards juga
    setTxt('statTotalSiswa', semua.length);
    setTxt('statHadir', hadir);
    setTxt('statIzin', izin + sakit);
    setTxt('statAlpha', alpha);

    // Kalau tidak ada data
    if (semua.length === 0) {
        tbody.innerHTML = '';
        if (emptyEl) emptyEl.style.display = 'block';
        return;
    }
    if (emptyEl) emptyEl.style.display = 'none';

    // Render baris tabel
    tbody.innerHTML = semua.map(d => {
        const waktu      = formatWaktuRealtime(d);
        const nama       = d.nama || '-';
        const nis        = d.nis || '-';
        const kelas      = d.kelas || '-';
        const metode     = d.metode || '-';
        const status     = d.status || 'Alpha';
        const keterangan = (d.keterangan && String(d.keterangan).trim() !== '')
            ? `<span class="text-slate-700">${d.keterangan}</span>`
            : `<span class="text-slate-300 italic">-</span>`;

        return `
            <tr class="border-b hover:bg-slate-50">
                <td class="px-4 py-3 font-mono text-slate-600 whitespace-nowrap">${waktu}</td>
                <td class="px-4 py-3 font-medium text-slate-800">
                    ${nama}
                    <div class="text-[10px] text-slate-400">NIS: ${nis}</div>
                </td>
                <td class="px-4 py-3 text-slate-600">${kelas}</td>
                <td class="px-4 py-3">${metodeBadgeRealtime(metode)}</td>
                <td class="px-4 py-3">${statusBadgeRealtime(status)}</td>
                <td class="px-4 py-3 text-sm max-w-xs">${keterangan}</td>
            </tr>
        `;
    }).join('');
}

// Real-time lintas tab
window.addEventListener('storage', function(e) {
    if (e.key === STORAGE_KEY_SISWA_ABSEN || e.key === STORAGE_KEY_DAFTAR_ABSEN) {
        console.log('🔄 Data absensi berubah dari tab lain, refresh realtime...');
        loadRealtimeFromStorage();
    }
});

// Auto-refresh tiap 3 detik
let realtimeIntervalId = null;
function startRealtimeAutoRefresh() {
    if (realtimeIntervalId) clearInterval(realtimeIntervalId);
    realtimeIntervalId = setInterval(() => {
        const sec = document.getElementById('section-realtime');
        if (sec && sec.classList.contains('active')) {
            loadRealtimeFromStorage();
        }
    }, 3000);
}
// ✅ Fungsi khusus tombol Refresh manual - dengan efek visual
function refreshRealtimeManual(buttonEl) {
    console.log('🔄 Refresh manual ditekan');
    
    // 1) Efek visual: putar ikon
    let iconEl = null;
    if (buttonEl) {
        iconEl = buttonEl.querySelector('i.fa-sync-alt');
        if (iconEl) {
            // Putar ikon 360 derajat
            iconEl.style.transition = 'transform 0.6s ease';
            iconEl.style.transform = 'rotate(360deg)';
            
            // Reset setelah animasi selesai
            setTimeout(() => {
                iconEl.style.transition = 'none';
                iconEl.style.transform = 'rotate(0deg)';
                setTimeout(() => {
                    iconEl.style.transition = 'transform 0.6s ease';
                }, 50);
            }, 650);
        }
        
        // Efek tombol: ganti warna sebentar
        buttonEl.classList.add('bg-blue-200');
        setTimeout(() => {
            buttonEl.classList.remove('bg-blue-200');
        }, 400);
    }
    
    // 2) Muat ulang data dari localStorage
    try {
        loadRealtimeFromStorage();
        console.log('✅ Data real-time berhasil di-refresh');
    } catch (e) {
        console.error('❌ Gagal refresh:', e);
    }
    
    // 3) Notifikasi kecil (toast)
    showRealtimeToast('Data berhasil di-refresh');
}

// Toast notifikasi khusus realtime
function showRealtimeToast(message) {
    const oldToast = document.querySelector('.realtime-toast');
    if (oldToast) oldToast.remove();
    
    const toast = document.createElement('div');
    toast.className = 'realtime-toast fixed top-20 right-6 bg-blue-600 text-white px-4 py-2.5 rounded-lg shadow-lg z-50 flex items-center gap-2 text-sm';
    toast.style.animation = 'slideInRight 0.3s ease-out';
    toast.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

// ============================================================
// RENDER DASHBOARD
// ============================================================
function renderDashboard() {
    let hadir = 0, izin = 0, sakit = 0, alpha = 0;
    presensiList.forEach(s => {
        if (s.status === 'Hadir') hadir++;
        else if (s.status === 'Izin') izin++;
        else if (s.status === 'Sakit') sakit++;
        else alpha++;
    });
    const total = presensiList.length;

    document.getElementById('statTotalSiswa').textContent = total;
    document.getElementById('statHadir').textContent = hadir;
    document.getElementById('statIzin').textContent = izin + sakit;
    document.getElementById('statAlpha').textContent = alpha;
}

// ============================================================
// RENDER PROGRES
// ============================================================
function renderProgres() {
    const list = document.getElementById('progresList');
    if (!list) return;
    
    if (presensiList.length === 0) {
        list.innerHTML = '<p class="text-sm text-slate-400 text-center py-4">Tidak ada data</p>';
        return;
    }

    const persens = presensiList.map(s => {
        return s.status === 'Hadir' ? 85 + Math.floor(Math.random() * 15) : 50 + Math.floor(Math.random() * 30);
    });
    
    document.getElementById('progresAvg').textContent = Math.round(persens.reduce((a,b) => a+b, 0) / persens.length) + '%';
    document.getElementById('progresMax').textContent = Math.max(...persens) + '%';
    document.getElementById('progresMin').textContent = Math.min(...persens) + '%';
    document.getElementById('progresTotal').textContent = presensiList.length;

    list.innerHTML = presensiList.map((s, i) => {
        const persen = persens[i];
        const color = persen >= 90 ? 'bg-emerald-500' : persen >= 70 ? 'bg-amber-500' : 'bg-red-500';
        return `
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium">${s.nama}</span>
                    <span class="text-slate-500">${persen}%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill ${color}" style="width:${persen}%"></div>
                </div>
            </div>
        `;
    }).join('');
}

// ============================================================
// RENDER KELOLA
// ============================================================
function renderKelola(kelas) {
    const tbody = document.getElementById('kelolaTableBody');
    if (!tbody) return;
    
    let html = '';
    presensiList.forEach((s, i) => {
        html += `
            <tr class="border-b hover:bg-slate-50">
                <td class="px-4 py-3">${i+1}</td>
                <td class="px-4 py-3 font-medium">${s.nama}</td>
                <td class="px-4 py-3">${s.nis}</td>
                <td class="px-4 py-3 text-center">
                    <button onclick="editSiswa(${i})" class="text-blue-600 mr-2 hover:text-blue-800">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="hapusSiswa(${i})" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

// ============================================================
// RENDER LAPORAN
// ============================================================
function renderLaporan() {
    const tbody = document.getElementById('laporanTableBody');
    if (!tbody) return;
    
    let html = '';
    presensiList.forEach((s, i) => {
        const hadir = s.status === 'Hadir' ? 25 : 20;
        const izin = s.status === 'Izin' ? 3 : 0;
        const sakit = s.status === 'Sakit' ? 2 : 0;
        const alpha = s.status === 'Alpha' ? 3 : 0;
        const total = hadir + izin + sakit + alpha;
        const persen = Math.round((hadir / total) * 100);
        html += `
            <tr class="border-b">
                <td class="px-3 py-2">${i+1}</td>
                <td class="px-3 py-2 font-medium">${s.nama}</td>
                <td class="px-3 py-2 text-center text-emerald-600">${hadir}</td>
                <td class="px-3 py-2 text-center text-amber-600">${izin}</td>
                <td class="px-3 py-2 text-center text-red-500">${sakit}</td>
                <td class="px-3 py-2 text-center text-gray-500">${alpha}</td>
                <td class="px-3 py-2 text-center font-semibold text-blue-600">${persen}%</td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}


// ============================================================
// CHARTS
// ============================================================
function initCharts() {
    const barEl = document.getElementById('barChart');
    if (barEl) {
        new Chart(barEl, {
            type: 'bar',
            data: {
                labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                datasets: [{
                    label: 'Hadir',
                    data: [220,215,225,230,220,195,210,225,230,220,215,220],
                    backgroundColor: '#10b981'
                }, {
                    label: 'Izin',
                    data: [15,20,12,18,22,15,20,18,15,20,18,15],
                    backgroundColor: '#f59e0b'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    const pieEl = document.getElementById('pieChart');
    if (pieEl) {
        let hadir = 0, izin = 0, sakit = 0, alpha = 0;
        presensiList.forEach(s => {
            if (s.status === 'Hadir') hadir++;
            else if (s.status === 'Izin') izin++;
            else if (s.status === 'Sakit') sakit++;
            else alpha++;
        });
        new Chart(pieEl, {
            type: 'doughnut',
            data: {
                labels: ['Hadir','Izin','Sakit','Alpha'],
                datasets: [{
                    data: [hadir, izin, sakit, alpha],
                    backgroundColor: ['#10b981','#f59e0b','#ef4444','#94a3b8']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '60%' }
        });
    }
}

// ============================================================
// FUNGSI TAMBAHAN
// ============================================================
function tambahSiswa() {
    if (!identitasGuru) return;
    
    const nama = prompt('Nama Siswa:');
    if (!nama) return;
    const nis = prompt('NIS:');
    if (!nis) return;
    
    // ✅ Simpan ke global data
    const kelas = identitasGuru.kelasLengkap;
    if (!globalData.siswaPerKelas[kelas]) {
        globalData.siswaPerKelas[kelas] = [];
    }
    globalData.siswaPerKelas[kelas].push({ nama, nis });
    saveGlobalData(globalData);
    
    // Reload
    loadDataKelas(kelas);
    alert(`✅ Siswa "${nama}" berhasil ditambahkan!`);
}

function editSiswa(index) {
    alert('Edit siswa index: ' + index + ' (fungsi bisa dikembangkan)');
}

function hapusSiswa(index) {
    if (!identitasGuru) return;
    if (!confirm('Yakin hapus siswa ini?')) return;
    
    const kelas = identitasGuru.kelasLengkap;
    if (globalData.siswaPerKelas[kelas]) {
        globalData.siswaPerKelas[kelas].splice(index, 1);
        saveGlobalData(globalData);
        loadDataKelas(kelas);
        alert('✅ Siswa berhasil dihapus!');
    }
}

function exportLaporanCSV() {
    if (presensiList.length === 0) {
        alert('⚠️ Tidak ada data!');
        return;
    }
    
    const headers = ['No', 'Nama', 'NIS', 'Hadir', 'Izin', 'Sakit', 'Alpha', 'Persentase'];
    const rows = presensiList.map((s, i) => {
        const hadir = s.status === 'Hadir' ? 25 : 20;
        const izin = s.status === 'Izin' ? 3 : 0;
        const sakit = s.status === 'Sakit' ? 2 : 0;
        const alpha = s.status === 'Alpha' ? 3 : 0;
        const total = hadir + izin + sakit + alpha;
        const persen = Math.round((hadir / total) * 100) + '%';
        return [i+1, s.nama, s.nis, hadir, izin, sakit, alpha, persen];
    });
    
    const csv = [
        headers.join(','),
        ...rows.map(r => r.map(c => `"${c}"`).join(','))
    ].join('\n');
    
    const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    const dateStr = new Date().toISOString().split('T')[0];
    link.setAttribute('href', url);
    link.setAttribute('download', `Laporan_${identitasGuru.kelasLengkap}_${dateStr}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function updateClock() {
    const now = new Date();
    document.getElementById('currentDate').textContent = now.toLocaleDateString('id-ID', {
        weekday: 'long', day: '2-digit', month: 'long', year: 'numeric'
    });
}

function logout() {
    if (confirm('Yakin ingin logout?')) {
        window.location.href = "{{ route('login') }}";
    }
}

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('📊 Dashboard Guru siap!');
    cekIdentitasGuru();

    // ✅ Muat data real-time
    setTimeout(() => {
        loadRealtimeFromStorage();
    }, 300);
});

</script>

</body>
</html>