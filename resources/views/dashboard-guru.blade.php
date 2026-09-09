<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hadirin.web · Dashboard Guru</title>
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
        .gradient-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(79, 70, 229, 0.15);
        }
        .table-row-hover:hover {
            background-color: #f1f5f9;
            transition: background-color 0.2s ease;
        }
        .badge-hadir {
            background: #dcfce7;
            color: #166534;
        }
        .badge-izin {
            background: #fef9c3;
            color: #854d0e;
        }
        .badge-sakit {
            background: #fce4ec;
            color: #b91c1c;
        }
        .badge-alpha {
            background: #fee2e2;
            color: #991b1b;
        }
        .new-row {
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn {
            0% { opacity: 0; transform: translateX(-20px); }
            100% { opacity: 1; transform: translateX(0); }
        }
        .pulse-dot {
            animation: pulse 1.5s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">

    <!-- ========== NAVBAR ========== -->
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
                    <a href="{{ route('beranda') }}" class="hover:text-indigo-600 transition">Beranda</a>
                    <a href="{{ route('absen.siswa.qr') }}" class="hover:text-indigo-600 transition">Absen Siswa</a>
                    <a href="{{ route('dashboard.guru') }}" class="hover:text-indigo-600 transition text-indigo-600 font-semibold">Dashboard Guru</a>
                    <a href="#" class="hover:text-indigo-600 transition">Rekap</a>
                </div>

                <!-- Tombol User -->
                <div class="hidden md:block">
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-600">
                            <i class="fas fa-user-circle text-indigo-600 text-lg"></i>
                            Guru
                        </span>
                        <button onclick="logout()" class="text-sm text-red-500 hover:text-red-700 transition">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div class="md:hidden flex items-center gap-3">
                    <span class="text-sm font-medium text-slate-600">
                        <i class="fas fa-user-circle text-indigo-600"></i> Najla
                    </span>
                    <button class="text-slate-500 hover:text-indigo-600 transition">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </nav>
        </div>
    </header>

    <!-- ========== HEADER DASHBOARD ========== -->
    <section class="gradient-header text-white py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="inline-block bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-medium mb-2">
                        <i class="fas fa-chalkboard-teacher mr-1"></i> Guru
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold">DASHBOARD GURU</h1>
                    <p class="text-indigo-100/90 text-sm mt-1">
                        <i class="fas fa-clock mr-1"></i> Mentoring Kehadiran Real-Time
                    </p>
                </div>
                <div class="mt-4 md:mt-0 text-sm text-indigo-100/90">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    <span id="currentDate">Memuat tanggal...</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8">

        <!-- ====== QUICK ACCESS ====== -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <a href="#" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 flex items-center gap-4 hover:shadow-md transition group">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 group-hover:scale-110 transition">
                    <i class="fas fa-chart-pie text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-800">Ringkasan</p>
                    <p class="text-xs text-slate-500">Lihat statistik harian</p>
                </div>
                <i class="fas fa-arrow-right text-slate-400 ml-auto group-hover:translate-x-1 transition"></i>
            </a>

            <a href="#" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 flex items-center gap-4 hover:shadow-md transition group">
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 group-hover:scale-110 transition">
                    <i class="fas fa-file-alt text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-800">Rekap & Laporan</p>
                    <p class="text-xs text-slate-500">Unduh data bulanan</p>
                </div>
                <i class="fas fa-arrow-right text-slate-400 ml-auto group-hover:translate-x-1 transition"></i>
            </a>

            <a href="{{ route('absen.siswa.qr') }}" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 flex items-center gap-4 hover:shadow-md transition group">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 group-hover:scale-110 transition">
                    <i class="fas fa-user-graduate text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-800">Halaman Absen Siswa</p>
                    <p class="text-xs text-slate-500">Kelola absensi siswa</p>
                </div>
                <i class="fas fa-arrow-right text-slate-400 ml-auto group-hover:translate-x-1 transition"></i>
            </a>
        </div>

        <!-- ====== PRESENSI HARI INI ====== -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200/60 overflow-hidden">
            <!-- Header Tabel -->
            <div class="px-6 py-4 border-b border-slate-200/60 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-clipboard-list text-indigo-500"></i>
                    <h3 class="font-semibold text-slate-800">Presensi Hari Ini</h3>
                    <span class="text-xs bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full">
                        <span id="totalSiswa">0</span> Siswa
                    </span>
                    <span class="text-xs bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full">
                        <i class="fas fa-arrow-up text-[8px] mr-1"></i> Real-Time
                    </span>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <i class="fas fa-calendar-alt"></i>
                    <span id="currentDateSmall">Memuat...</span>
                </div>
            </div>

            <!-- Tabel -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/60">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Metode</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody id="presensiTableBody" class="divide-y divide-slate-200/60">
                        <!-- Data akan diisi oleh JavaScript secara real-time -->
                    </tbody>
                </table>
            </div>

            <!-- Footer Tabel -->
            <div class="px-6 py-4 border-t border-slate-200/60 bg-slate-50/50 flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        Hadir: <span class="font-semibold text-slate-700" id="totalHadir">0</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                        Izin: <span class="font-semibold text-slate-700" id="totalIzin">0</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span>
                        Sakit: <span class="font-semibold text-slate-700" id="totalSakit">0</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-gray-400"></span>
                        Alpha: <span class="font-semibold text-slate-700" id="totalAlpha">0</span>
                    </span>
                </div>
                <button onclick="window.location.href='{{ route('rekap.laporan') }}'" class="inline-flex items-center gap-2 text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded-lg transition">
    <i class="fas fa-file-alt"></i> Lihat Rekap Bulanan
</button>
            </div>
        </div>

        <!-- ====== STATISTIK CEPAT ====== -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 text-center stat-card">
                <p class="text-2xl font-bold text-indigo-600" id="statTotal">0</p>
                <p class="text-xs text-slate-500">Total Siswa</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 text-center stat-card">
                <p class="text-2xl font-bold text-emerald-600" id="statHadir">0</p>
                <p class="text-xs text-slate-500">Hadir</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 text-center stat-card">
                <p class="text-2xl font-bold text-amber-600" id="statIzinSakit">0</p>
                <p class="text-xs text-slate-500">Izin + Sakit</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 text-center stat-card">
                <p class="text-2xl font-bold text-rose-600" id="statAlpha">0</p>
                <p class="text-xs text-slate-500">Alpha</p>
            </div>
        </div>

        <!-- Tombol Simulasi -->
        <div class="mt-6 text-center">
            <button onclick="simulateNewPresensi()" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-3 rounded-xl transition shadow-md shadow-indigo-200/60">
                <i class="fas fa-user-plus"></i> Simulasi Absensi Baru
            </button>
            <p class="text-xs text-slate-400 mt-2">
                <i class="fas fa-info-circle"></i> Klik untuk mensimulasikan siswa baru melakukan absensi
            </p>
        </div>

        <!-- Tombol Kembali -->
        <div class="mt-8 text-center">
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
            <span><i class="fas fa-image mr-1"></i> Dashboard Guru2.png</span>
        </div>
    </footer>

    <!-- ========== JAVASCRIPT REAL-TIME ========== -->
    <script>
        // ===== DATA PRESENSI =====
        let presensiList = [];
        let nextId = 1;

        // Data awal
        const initialData = [
            { name: 'Najla', kelas: 'XII.PRL', metode: 'Scan', status: 'Hadir' },
            { name: 'Yasmin', kelas: 'XII.PRL', metode: 'Ketik', status: 'Hadir' },
            { name: 'Dina', kelas: 'XII.PRL', metode: 'Google Form', status: 'Izin' },
            { name: 'Alex', kelas: 'XII.PRL', metode: 'Scan', status: 'Hadir' },
            { name: 'Arjuna', kelas: 'XII.PRL', metode: 'Ketik', status: 'Sakit' }
        ];

        const metodeList = ['Scan', 'Ketik', 'Google Form', 'QR Code'];
        const statusList = ['Hadir', 'Hadir', 'Hadir', 'Izin', 'Sakit'];
        const namesList = ['Budi', 'Siti', 'Ahmad', 'Dewi', 'Rizky', 'Sarah', 'Doni', 'Maya', 'Faisal', 'Hana', 'Gilang', 'Putri'];

        // ===== INISIALISASI =====
        function initPresensi() {
            presensiList = [];
            const baseTime = new Date();
            baseTime.setHours(8, 0, 0);

            initialData.forEach((item, index) => {
                const time = new Date(baseTime);
                time.setMinutes(baseTime.getMinutes() + (index * 5) + Math.floor(Math.random() * 3));
                
                presensiList.push({
                    id: nextId++,
                    name: item.name,
                    kelas: item.kelas,
                    waktu: time,
                    metode: item.metode,
                    status: item.status,
                    isNew: false
                });
            });

            // Urutkan dari terbaru ke terlama
            presensiList.sort((a, b) => b.waktu - a.waktu);
            renderTable();
            updateStats();
        }

        // ===== RENDER TABEL =====
        function renderTable() {
            const tbody = document.getElementById('presensiTableBody');
            
            let html = '';
            presensiList.forEach((item, index) => {
                const statusBadge = getStatusBadge(item.status);
                const timeStr = formatTime(item.waktu);
                const isNew = item.isNew ? 'new-row' : '';
                const highlight = item.isNew ? 'bg-emerald-50/70' : '';
                const newLabel = item.isNew ? `<span class="text-[8px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full font-medium ml-1"><i class="fas fa-star text-[6px]"></i> Baru</span>` : '';
                
                html += `
                    <tr class="table-row-hover ${highlight} ${isNew}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-${getColor(item.name)}-100 flex items-center justify-center text-${getColor(item.name)}-600 text-sm font-bold">
                                    ${item.name.charAt(0)}
                                </div>
                                <span class="font-medium text-slate-800">${item.name}</span>
                                ${newLabel}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">${item.kelas}</td>
                        <td class="px-6 py-4 text-sm ${item.isNew ? 'font-medium text-emerald-600' : 'text-slate-500'}">
                            ${timeStr}
                            ${item.isNew ? '<span class="text-[10px] text-emerald-400 ml-1">(baru)</span>' : ''}
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">
                                <i class="fas fa-${getMetodeIcon(item.metode)} text-[10px]"></i>
                                ${item.metode}
                            </span>
                        </td>
                        <td class="px-6 py-4">${statusBadge}</td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
            
            // Reset new flag
            presensiList.forEach(item => item.isNew = false);
            
            // Update total
            document.getElementById('totalSiswa').textContent = presensiList.length;
        }

        // ===== GET STATUS BADGE =====
        function getStatusBadge(status) {
            const badges = {
                'Hadir': `<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium badge-hadir"><i class="fas fa-check-circle text-emerald-500 text-[10px]"></i> Hadir</span>`,
                'Izin': `<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium badge-izin"><i class="fas fa-pen text-amber-500 text-[10px]"></i> Izin</span>`,
                'Sakit': `<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium badge-sakit"><i class="fas fa-thermometer-half text-red-500 text-[10px]"></i> Sakit</span>`,
                'Alpha': `<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium badge-alpha"><i class="fas fa-times-circle text-red-500 text-[10px]"></i> Alpha</span>`
            };
            return badges[status] || badges['Alpha'];
        }

        // ===== GET METODE ICON =====
        function getMetodeIcon(metode) {
            const icons = {
                'Scan': 'camera',
                'Ketik': 'keyboard',
                'Google Form': 'google',
                'QR Code': 'qrcode'
            };
            return icons[metode] || 'check';
        }

        // ===== GET COLOR =====
        function getColor(name) {
            const colors = ['indigo', 'emerald', 'amber', 'red', 'purple', 'blue', 'pink', 'orange', 'teal', 'cyan'];
            return colors[name.length % colors.length];
        }

        // ===== FORMAT TIME =====
        function formatTime(date) {
            if (!date) return '-';
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${hours}.${minutes}`;
        }

        // ===== UPDATE STATISTICS =====
        function updateStats() {
            const hadir = presensiList.filter(s => s.status === 'Hadir').length;
            const izin = presensiList.filter(s => s.status === 'Izin').length;
            const sakit = presensiList.filter(s => s.status === 'Sakit').length;
            const alpha = presensiList.filter(s => s.status === 'Alpha').length;
            
            document.getElementById('totalHadir').textContent = hadir;
            document.getElementById('totalIzin').textContent = izin;
            document.getElementById('totalSakit').textContent = sakit;
            document.getElementById('totalAlpha').textContent = alpha;
            
            // Statistik card
            document.getElementById('statTotal').textContent = presensiList.length;
            document.getElementById('statHadir').textContent = hadir;
            document.getElementById('statIzinSakit').textContent = izin + sakit;
            document.getElementById('statAlpha').textContent = alpha;
        }

        // ===== SIMULASI ABSENSI BARU =====
        function simulateNewPresensi() {
            const availableNames = namesList.filter(n => !presensiList.some(p => p.name === n));
            let name;
            
            if (availableNames.length === 0) {
                name = `Siswa ${presensiList.length + 1}`;
            } else {
                name = availableNames[Math.floor(Math.random() * availableNames.length)];
            }
            
            const status = statusList[Math.floor(Math.random() * statusList.length)];
            const metode = metodeList[Math.floor(Math.random() * metodeList.length)];
            
            // Tambahkan ke list dengan waktu sekarang
            presensiList.push({
                id: nextId++,
                name: name,
                kelas: 'XII.PRL',
                waktu: new Date(),
                metode: metode,
                status: status,
                isNew: true
            });
            
            // Urutkan dari terbaru ke terlama
            presensiList.sort((a, b) => b.waktu - a.waktu);
            
            renderTable();
            updateStats();
            
            // Highlight row baru
            setTimeout(() => {
                const rows = document.querySelectorAll('#presensiTableBody tr');
                if (rows.length > 0) {
                    rows[0].style.transition = 'all 0.3s ease';
                    rows[0].style.backgroundColor = '#d1fae5';
                    setTimeout(() => {
                        rows[0].style.backgroundColor = '';
                    }, 2000);
                }
            }, 100);
            
            showNotification(`✅ ${name} berhasil absen dengan status ${status}!`);
        }

        // ===== LIHAT REKAP =====
        function lihatRekap() {
            showNotification('📊 Membuka rekap bulanan...');
            setTimeout(() => {
                window.location.href = '#';
            }, 1000);
        }

        // ===== LOGOUT =====
        function logout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = "{{ route('login') }}";
            }
        }

        // ===== NOTIFICATION =====
        function showNotification(message) {
            const oldNotif = document.querySelector('.notification-toast');
            if (oldNotif) oldNotif.remove();

            const notification = document.createElement('div');
            notification.className = 'notification-toast fixed top-24 left-1/2 transform -translate-x-1/2 bg-indigo-600 text-white px-6 py-3 rounded-xl shadow-lg z-50 transition-all duration-500';
            notification.innerHTML = `
                <div class="flex items-center gap-3">
                    <i class="fas fa-info-circle text-xl"></i>
                    <span class="text-sm font-medium">${message}</span>
                </div>
            `;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translate(-50%, -20px)';
                setTimeout(() => notification.remove(), 500);
            }, 4000);
        }

        // ===== UPDATE CLOCK =====
        function updateClock() {
            const now = new Date();
            const dateStr = now.toLocaleDateString('id-ID', { 
                weekday: 'long', 
                day: '2-digit', 
                month: '2-digit', 
                year: 'numeric' 
            });
            document.getElementById('currentDate').textContent = dateStr;
            document.getElementById('currentDateSmall').textContent = dateStr;
        }

        // ===== INIT =====
        document.addEventListener('DOMContentLoaded', function() {
            initPresensi();
            updateClock();
            setInterval(updateClock, 60000);
        });

        // ===== KEYBOARD SHORTCUT =====
        document.addEventListener('keydown', function(e) {
            if ((e.key === 'a' || e.key === 'A') && !e.ctrlKey && !e.metaKey) {
                simulateNewPresensi();
            }
        });

        console.log('📋 Shortcut: Tekan "A" untuk simulasi absensi baru');
    </script>

</body>
</html>