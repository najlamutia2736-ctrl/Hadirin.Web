<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hadirin.web · Verifikasi Absensi</title>
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
        .success-card {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .check-animation {
            animation: checkPop 0.6s ease-out;
        }
        @keyframes checkPop {
            0% { transform: scale(0) rotate(-20deg); opacity: 0; }
            50% { transform: scale(1.3) rotate(5deg); }
            70% { transform: scale(0.9) rotate(-5deg); }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
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
            0% { opacity: 0; transform: translateY(-20px); }
            100% { opacity: 1; transform: translateY(0); }
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
                    <a href="{{ route('absen.siswa.qr') }}" class="hover:text-indigo-600 transition text-indigo-600 font-semibold">Absen Siswa</a>
                    <a href="{{ route('dashboard.guru') }}" class="hover:text-indigo-600 transition">Dashboard Guru</a>
                    <a href="{{ route('rekap.laporan') }}" class="hover:text-indigo-600 transition">Rekap</a>
                </div>

                <!-- Tombol User -->
                <div class="hidden md:block">
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-600">
                            <i class="fas fa-user-circle text-indigo-600 text-lg"></i>
                            Najla Mutia
                        </span>
                        <a href="{{ route('login') }}" class="text-sm text-red-500 hover:text-red-700 transition">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
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

    <!-- ========== MAIN: VERIFIKASI ABSENSI ========== -->
    <main class="flex-1 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-block bg-emerald-100/80 text-emerald-700 text-xs font-semibold px-4 py-1.5 rounded-full border border-emerald-200/60 mb-3">
                <i class="fas fa-check-circle mr-2"></i> Absensi Mandiri
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-800">
                Absen hari ini
            </h1>
            <p class="text-sm text-slate-500 mt-2">
                <i class="fas fa-clock text-emerald-500 mr-1"></i>
                <span id="currentTime">Memuat waktu...</span>
            </p>
        </div>

        <!-- Tabs: Scan QR Code / ID Unik -->
        <div class="flex justify-center gap-4 mb-8">
            <a href="{{ route('absen.siswa.qr') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-50 text-indigo-600 rounded-full text-sm font-medium border border-indigo-200/60 hover:bg-indigo-100 transition">
                <i class="fas fa-camera"></i> Scan QR Code
            </a>
            <a href="{{ route('absen.siswa.qr') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-slate-100 text-slate-600 rounded-full text-sm font-medium border border-slate-200/60 hover:bg-slate-200 transition">
                <i class="fas fa-keyboard"></i> ID Unik
            </a>
        </div>

        <!-- ====== KARTU SUKSES ====== -->
        <div class="success-card rounded-3xl shadow-xl shadow-emerald-200/60 p-8 md:p-10 text-center mb-8">
            <div class="check-animation inline-block bg-white/20 backdrop-blur-sm rounded-full p-4 mb-4">
                <i class="fas fa-check-circle text-6xl text-white"></i>
            </div>
            <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">
                Anda Telah Berhasil Melakukan absensi
            </h2>
            <p class="text-emerald-100/90 text-sm mb-6">
                <i class="fas fa-clock mr-1"></i> 
                <span id="successTime">Memuat waktu...</span>
            </p>
            <button onclick="absenUlang()" class="inline-block bg-white text-emerald-700 hover:bg-emerald-50 font-semibold px-8 py-3 rounded-xl shadow-lg transition hover:scale-105">
                <i class="fas fa-redo mr-2"></i> Absensi Ulang
            </button>
        </div>

        <!-- ====== DAFTAR NAMA SISWA ====== -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200/60 overflow-hidden">
            <!-- Header Tabel -->
            <div class="px-6 py-4 border-b border-slate-200/60 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-users text-indigo-500"></i>
                    <h3 class="font-semibold text-slate-800">Daftar Nama Siswa</h3>
                    <span class="text-xs bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full">
                        <span id="totalSiswa">0</span> Siswa
                    </span>
                    <span class="text-xs bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full">
                        <i class="fas fa-arrow-up text-[8px] mr-1"></i> Terbaru di atas
                    </span>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <i class="fas fa-calendar-alt"></i>
                    <span id="currentDate">Memuat tanggal...</span>
                </div>
            </div>

            <!-- Tabel -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/60">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">NIS</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody" class="divide-y divide-slate-200/60">
                        <!-- Data akan diisi oleh JavaScript secara real-time -->
                    </tbody>
                </table>
            </div>

            <!-- Footer Tabel: Summary -->
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
                <div class="text-xs text-slate-400">
                    <i class="fas fa-print mr-1"></i> Total: <span id="totalAll">0</span> Siswa
                </div>
            </div>
        </div>

        <!-- Tombol Simulasi Absensi Baru -->
        <div class="mt-6 text-center">
            <button onclick="simulateNewAbsensi()" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-3 rounded-xl transition shadow-md shadow-indigo-200/60">
                <i class="fas fa-user-plus"></i> Simulasi Absensi Baru
            </button>
            <p class="text-xs text-slate-400 mt-2">
                <i class="fas fa-info-circle"></i> Klik untuk mensimulasikan siswa baru melakukan absensi (Waktu REAL-TIME)
            </p>
        </div>

        <!-- Tombol Kembali -->
        <div class="mt-8 text-center">
            <a href="{{ route('absen.siswa.qr') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left"></i> Kembali ke Halaman Absensi
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
            <span><i class="fas fa-image mr-1"></i> Absensi Siswa QR3.png</span>
        </div>
    </footer>

    <!-- ============================================================ -->
    <!-- ========== JAVASCRIPT REAL-TIME (LENGKAP) ========== -->
    <!-- ============================================================ -->
    <script>
        // ================================================================
        // 1. DATA SISWA (HANYA NAMA & NIS, TANPA WAKTU)
        // ================================================================
        const studentData = [
            { name: 'Alex', nis: '12345' },
            { name: 'Yasmin', nis: '12346' },
            { name: 'Dina', nis: '12347' },
            { name: 'Arjuna', nis: '12348' },
            { name: 'Najla', nis: '12349' }
        ];

        // ================================================================
        // 2. STATUS YANG TERSEDIA
        // ================================================================
        const statusList = ['Hadir', 'Hadir', 'Hadir', 'Izin', 'Sakit'];

        // ================================================================
        // 3. STATE / VARIABEL GLOBAL
        // ================================================================
        let absensiList = [];
        let nextId = 1;

        // ================================================================
        // 4. INISIALISASI DATA (SEMUA WAKTU REAL-TIME)
        // ================================================================
        function initAbsensi() {
            absensiList = [];
            
            // === SEMUA SISWA DIABSEN DENGAN WAKTU REAL ===
            const now = new Date();
            
            // Status untuk setiap siswa (urutan tetap)
            const statusOptions = ['Hadir', 'Hadir', 'Izin', 'Sakit', 'Hadir'];
            
            studentData.forEach((student, index) => {
                // Waktu: sekarang - (jumlah siswa - 1 - index) * 5 menit
                // Jadi siswa pertama (Alex) absen paling lama, siswa terakhir (Najla) absen paling baru
                const time = new Date(now);
                time.setMinutes(now.getMinutes() - ((studentData.length - 1 - index) * 5));
                
                absensiList.push({
                    id: nextId++,
                    name: student.name,
                    nis: student.nis,
                    status: statusOptions[index] || 'Hadir',
                    time: time, // ✅ WAKTU REAL-TIME
                    isNew: false
                });
            });
            
            // Urutkan dari terbaru ke terlama (descending)
            absensiList.sort((a, b) => b.time - a.time);
            
            renderTable();
            updateStats();
        }

        // ================================================================
        // 5. FUNGSI ABSENSI BARU (REAL-TIME)
        // ================================================================
        function absenBaru(nama, status) {
            // ✅ Gunakan WAKTU SEKARANG (REAL-TIME)
            const now = new Date();
            
            const newAbsen = {
                id: nextId++,
                name: nama,
                nis: String(10000 + absensiList.length + 1),
                status: status || 'Hadir',
                time: now, // ✅ WAKTU REAL-TIME SEKARANG
                isNew: true
            };
            
            absensiList.push(newAbsen);
            
            // Urutkan dari terbaru ke terlama
            absensiList.sort((a, b) => b.time - a.time);
            
            renderTable();
            updateStats();
            
            // Update waktu sukses di kartu
            document.getElementById('successTime').textContent = formatTime(now) + ' WIB';
            
            // Tampilkan notifikasi
            showNotification(`✅ ${nama} berhasil absen dengan status ${status}!`);
        }

        // ================================================================
        // 6. RENDER TABEL
        // ================================================================
        function renderTable() {
            const tbody = document.getElementById('studentTableBody');
            
            let html = '';
            absensiList.forEach((item, index) => {
                const statusBadge = getStatusBadge(item.status);
                const timeStr = formatTime(item.time);
                const isNew = item.isNew ? 'new-row' : '';
                const highlight = item.isNew ? 'bg-emerald-50/70 border-l-4 border-emerald-500' : '';
                const newLabel = item.isNew ? `<span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium ml-2"><i class="fas fa-star text-[8px]"></i> Baru</span>` : '';
                
                html += `
                    <tr class="table-row-hover ${highlight} ${isNew}">
                        <td class="px-6 py-4 text-sm text-slate-500">${index + 1}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-${getColor(item.name)}-100 flex items-center justify-center text-${getColor(item.name)}-600 text-sm font-bold">
                                    ${item.name.charAt(0)}
                                </div>
                                <span class="font-medium text-slate-800">${item.name}</span>
                                ${newLabel}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">${item.nis}</td>
                        <td class="px-6 py-4">${statusBadge}</td>
                        <td class="px-6 py-4 text-sm ${item.isNew ? 'font-medium text-emerald-600' : 'text-slate-500'}">
                            ${timeStr}
                            ${item.isNew ? '<span class="text-[10px] text-emerald-400 ml-1">(baru saja)</span>' : ''}
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
            
            // Reset new flag setelah render
            absensiList.forEach(item => item.isNew = false);
            
            // Update total siswa
            document.getElementById('totalSiswa').textContent = absensiList.length;
            document.getElementById('totalAll').textContent = absensiList.length;
        }

        // ================================================================
        // 7. GET STATUS BADGE
        // ================================================================
        function getStatusBadge(status) {
            const badges = {
                'Hadir': `<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium badge-hadir"><i class="fas fa-check-circle text-emerald-500 text-[10px]"></i> Hadir</span>`,
                'Izin': `<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium badge-izin"><i class="fas fa-pen text-amber-500 text-[10px]"></i> Izin</span>`,
                'Sakit': `<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium badge-sakit"><i class="fas fa-thermometer-half text-red-500 text-[10px]"></i> Sakit</span>`,
                'Alpha': `<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium badge-alpha"><i class="fas fa-times-circle text-red-500 text-[10px]"></i> Alpha</span>`
            };
            return badges[status] || badges['Alpha'];
        }

        // ================================================================
        // 8. GET COLOR FOR AVATAR
        // ================================================================
        function getColor(name) {
            const colors = ['indigo', 'emerald', 'amber', 'red', 'purple', 'blue', 'pink', 'orange'];
            return colors[name.length % colors.length];
        }

        // ================================================================
        // 9. FORMAT TIME (REAL-TIME)
        // ================================================================
        function formatTime(date) {
            if (!date) return '-';
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${hours}:${minutes} WIB`;
        }

        // ================================================================
        // 10. UPDATE STATISTICS
        // ================================================================
        function updateStats() {
            const hadir = absensiList.filter(s => s.status === 'Hadir').length;
            const izin = absensiList.filter(s => s.status === 'Izin').length;
            const sakit = absensiList.filter(s => s.status === 'Sakit').length;
            const alpha = absensiList.filter(s => s.status === 'Alpha').length;
            
            document.getElementById('totalHadir').textContent = hadir;
            document.getElementById('totalIzin').textContent = izin;
            document.getElementById('totalSakit').textContent = sakit;
            document.getElementById('totalAlpha').textContent = alpha;
        }

        // ================================================================
        // 11. SIMULASI ABSENSI BARU (TANPA SETTIMEOUT)
        // ================================================================
        function simulateNewAbsensi() {
            const names = ['Budi', 'Siti', 'Ahmad', 'Dewi', 'Rizky', 'Sarah', 'Doni', 'Maya', 'Faisal', 'Hana'];
            
            // Cari nama yang belum dipakai
            let availableNames = names.filter(n => !absensiList.some(a => a.name === n));
            if (availableNames.length === 0) {
                const newName = `Siswa ${absensiList.length + 1}`;
                availableNames = [newName];
            }
            
            const name = availableNames[Math.floor(Math.random() * availableNames.length)];
            const status = statusList[Math.floor(Math.random() * statusList.length)];
            
            // ✅ LANGSUNG TAMBAHKAN DENGAN WAKTU SEKARANG (REAL-TIME)
            absenBaru(name, status);
        }

        // ================================================================
        // 12. ABSENSI ULANG (RESET)
        // ================================================================
        function absenUlang() {
            // Reset ke data awal dengan waktu REAL-TIME
            initAbsensi();
            document.getElementById('successTime').textContent = formatTime(new Date()) + ' WIB';
            showNotification('🔄 Data absensi direset dengan waktu real-time!');
        }

        // ================================================================
        // 13. NOTIFICATION
        // ================================================================
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

        // ================================================================
        // 14. UPDATE CLOCK REAL-TIME
        // ================================================================
        function updateClock() {
            const now = new Date();
            
            // Update waktu saat ini
            const timeStr = now.toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: false 
            });
            document.getElementById('currentTime').textContent = timeStr + ' WIB';
            
            // Update tanggal
            const dateStr = now.toLocaleDateString('id-ID', { 
                weekday: 'long', 
                day: '2-digit', 
                month: '2-digit', 
                year: 'numeric' 
            });
            document.getElementById('currentDate').textContent = dateStr;
            
            // Update waktu sukses (jika belum di-set)
            if (!document.getElementById('successTime').textContent.includes(':')) {
                document.getElementById('successTime').textContent = formatTime(now) + ' WIB';
            }
        }

        // ================================================================
        // 15. INIT
        // ================================================================
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi data dengan waktu REAL-TIME
            initAbsensi();
            
            // Update clock setiap detik
            updateClock();
            setInterval(updateClock, 1000);
            
            // Update waktu sukses
            document.getElementById('successTime').textContent = formatTime(new Date()) + ' WIB';
            
            // Animasi fade-in kartu sukses
            const successCard = document.querySelector('.success-card');
            if (successCard) {
                successCard.style.opacity = '0';
                successCard.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    successCard.style.transition = 'all 0.6s ease-out';
                    successCard.style.opacity = '1';
                    successCard.style.transform = 'translateY(0)';
                }, 300);
            }
        });

        // ================================================================
        // 16. KEYBOARD SHORTCUT
        // ================================================================
        document.addEventListener('keydown', function(e) {
            // Tekan 'A' untuk simulasi absensi baru
            if ((e.key === 'a' || e.key === 'A') && !e.ctrlKey && !e.metaKey) {
                simulateNewAbsensi();
            }
            // Tekan 'R' untuk reset
            if ((e.key === 'r' || e.key === 'R') && !e.ctrlKey && !e.metaKey) {
                absenUlang();
            }
        });

        // ================================================================
        // 17. CONSOLE INFO
        // ================================================================
        console.log('📋 Shortcut: Tekan "A" untuk absensi baru, "R" untuk reset data');
        console.log('✅ SEMUA WAKTU ABSENSI MENGGUNAKAN REAL-TIME!');
        console.log(`📊 Total siswa: ${absensiList.length}`);
    </script>

</body>
</html>