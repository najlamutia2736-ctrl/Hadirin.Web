<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hadirin.web · Verifikasi Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
        .success-card { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .check-animation { animation: checkPop 0.6s ease-out; }
        @keyframes checkPop {
            0% { transform: scale(0) rotate(-20deg); opacity: 0; }
            50% { transform: scale(1.3) rotate(5deg); }
            70% { transform: scale(0.9) rotate(-5deg); }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }
        .table-row-hover:hover { background-color: #f1f5f9; }
        .badge-hadir { background: #dcfce7; color: #166534; }
        .badge-izin { background: #fef9c3; color: #854d0e; }
        .badge-sakit { background: #fce4ec; color: #b91c1c; }
        .badge-alpha { background: #fee2e2; color: #991b1b; }
        .badge-metode-scan { background: #e0e7ff; color: #4338ca; }
        .badge-metode-id { background: #d1fae5; color: #065f46; }
        .badge-metode-izin { background: #fef3c7; color: #92400e; }
        .new-row { animation: slideIn 0.5s ease-out; }
        @keyframes slideIn {
            0% { opacity: 0; transform: translateY(-20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .pulse-dot { animation: pulse 1.5s ease-in-out infinite; }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }
        .btn-export {
            background: #7c3aed; color: white; padding: 8px 16px;
            border-radius: 8px; font-size: 13px; transition: 0.2s;
            border: none; cursor: pointer;
        }
        .btn-export:hover { background: #6d28d9; }
        .toast-notification { animation: slideDown 0.3s ease-out; }
        @keyframes slideDown {
            0% { transform: translate(-50%, -20px); opacity: 0; }
            100% { transform: translate(-50%, 0); opacity: 1; }
        }
        .empty-state { padding: 40px 20px; text-align: center; color: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">

    <!-- ========== NAVBAR ========== -->
    <header class="w-full bg-white/80 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center justify-between h-16 md:h-20">
                <div class="flex items-center gap-2">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-indigo-700 tracking-tight">
                        Hadirin.<span class="text-slate-700">web</span>
                    </a>
                    <span class="hidden sm:inline-block text-[10px] font-medium bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full">beta</span>
                </div>
                <div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600">
                    <a href="{{ route('beranda') }}" class="hover:text-indigo-600 transition">Beranda</a>
                    <a href="{{ route('identitas.siswa') }}" class="hover:text-indigo-600 transition text-indigo-600 font-semibold">Absen Siswa</a>
                    <a href="{{ route('guru.dashboard') }}" class="hover:text-indigo-600 transition">Dashboard Guru</a>
                    <a href="{{ route('cms.rekap') }}" class="hover:text-indigo-600 transition">Rekap</a>
                </div>
                <!-- ✅ TOMBOL USER DINAMIS -->
                <div class="hidden md:block">
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-600 flex items-center gap-2">
                            <i class="fas fa-user-circle text-indigo-600 text-lg"></i>
                            <span id="userNavName">Siswa</span>
                        </span>
                        <a href="{{ route('login') }}" class="text-sm text-red-500 hover:text-red-700 transition">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
                <div class="md:hidden flex items-center gap-3">
                    <span class="text-sm font-medium text-slate-600 flex items-center gap-1">
                        <i class="fas fa-user-circle text-indigo-600"></i>
                        <span id="userNavNameMobile">Siswa</span>
                    </span>
                    <button class="text-slate-500 hover:text-indigo-600 transition"><i class="fas fa-bars text-xl"></i></button>
                </div>
            </nav>
        </div>
    </header>

    <!-- ========== MAIN ========== -->
    <main class="flex-1 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-block bg-emerald-100/80 text-emerald-700 text-xs font-semibold px-4 py-1.5 rounded-full border border-emerald-200/60 mb-3">
                <i class="fas fa-check-circle mr-2"></i> Absensi Mandiri
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-800">Absen hari ini</h1>
            <p class="text-sm text-slate-500 mt-2 flex items-center justify-center gap-2">
                <i class="fas fa-clock text-emerald-500 mr-1"></i>
                <span id="currentTime">Memuat waktu...</span>
                <span class="text-emerald-500 flex items-center gap-1 text-xs">
                    <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full pulse-dot"></span> Live
                </span>
            </p>
        </div>

        <!-- Tabs -->
        <div class="flex flex-wrap justify-center gap-3 mb-8">
            <a href="{{ route('absen.siswa.qr') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-50 text-indigo-600 rounded-full text-sm font-medium border border-indigo-200/60 hover:bg-indigo-100 transition">
                <i class="fas fa-camera"></i> Scan QR Code
            </a>
            <a href="{{ route('absen.siswa.qr') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-100 text-slate-600 rounded-full text-sm font-medium border border-slate-200/60 hover:bg-slate-200 transition">
                <i class="fas fa-keyboard"></i> ID Unik
            </a>
            <a href="{{ route('absen.siswa.qr') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-50 text-amber-600 rounded-full text-sm font-medium border border-amber-200/60 hover:bg-amber-100 transition">
                <i class="fas fa-file-medical-alt"></i> Izin / Sakit
            </a>
        </div>

        <!-- KARTU SUKSES -->
        <div id="successCard" class="success-card rounded-3xl shadow-xl shadow-emerald-200/60 p-8 md:p-10 text-center mb-8 hidden">
            <div class="check-animation inline-block bg-white/20 backdrop-blur-sm rounded-full p-4 mb-4">
                <i class="fas fa-check-circle text-6xl text-white"></i>
            </div>
            <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">
                Absensi Berhasil!
            </h2>
            <p class="text-emerald-100/90 text-sm mb-1" id="successNama">-</p>
            <p class="text-emerald-100/90 text-xs mb-6">
                <i class="fas fa-clock mr-1"></i>
                <span id="successTime">-</span>
            </p>
            <a href="{{ route('absen.siswa.qr') }}" class="inline-block bg-white text-emerald-700 hover:bg-emerald-50 font-semibold px-8 py-3 rounded-xl shadow-lg transition hover:scale-105">
                <i class="fas fa-redo mr-2"></i> Absensi Lagi
            </a>
        </div>

        <!-- KARTU KOSONG -->
        <div id="emptyCard" class="bg-white rounded-3xl shadow-sm border border-slate-200/60 p-8 text-center mb-8">
            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-inbox text-3xl text-slate-400"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mb-2">Belum Ada Data Absensi</h2>
            <p class="text-sm text-slate-500 mb-6">Silakan lakukan absensi terlebih dahulu di halaman absensi</p>
            <a href="{{ route('absen.siswa.qr') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-3 rounded-xl shadow-md shadow-indigo-200/60 transition">
                <i class="fas fa-arrow-right mr-2"></i> Ke Halaman Absensi
            </a>
        </div>

        <!-- DAFTAR ABSENSI SISWA -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/60 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-users text-indigo-500"></i>
                    <h3 class="font-semibold text-slate-800">Riwayat Absensi</h3>
                    <span class="text-xs bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full">
                        <span id="totalSiswa">0</span> Data
                    </span>
                    <span class="text-xs bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full">
                        <i class="fas fa-arrow-up text-[8px] mr-1"></i> Terbaru di atas
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <i class="fas fa-calendar-alt"></i>
                        <span id="currentDate">Memuat tanggal...</span>
                    </div>
                    <button onclick="exportData()" class="btn-export">
                        <i class="fas fa-download mr-1"></i> Export
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/60">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Metode</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody" class="divide-y divide-slate-200/60"></tbody>
                </table>
            </div>

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
                    <i class="fas fa-print mr-1"></i> Total: <span id="totalAll">0</span> Data
                </div>
            </div>
        </div>

        <!-- Tombol Kembali -->
        <div class="mt-8 text-center">
            <a href="{{ route('absen.siswa.qr') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left"></i> Kembali ke Halaman Absensi
            </a>
        </div>

    </main>

    <!-- FOOTER -->
    <footer class="w-full border-t border-slate-200/60 py-4 text-center text-[10px] text-slate-400 bg-white/40 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 flex flex-wrap items-center justify-center gap-3">
            <span>© 2026 Sistem Absensi</span>
            <span class="w-px h-3 bg-slate-300"></span>
            <span>Hadirin.web</span>
            <span class="w-px h-3 bg-slate-300"></span>
            <span><i class="fas fa-image mr-1"></i> Absensi Siswa Verifikasi</span>
        </div>
    </footer>

    <!-- ========== JAVASCRIPT ========== -->
    <script>
        // ================================================================
        // 1. STATE
        // ================================================================
        let absensiList = [];

        // ================================================================
        // 2. AMBIL DATA DARI LOCALSTORAGE
        // ================================================================
        function loadDataAbsensi() {
            const siswaAbsen = localStorage.getItem('siswa_absen');
            
            if (siswaAbsen) {
                try {
                    const data = JSON.parse(siswaAbsen);
                    console.log('✅ Data absensi ditemukan:', data);
                    
                    absensiList.push({
                        id: Date.now(),
                        nama: data.nama,
                        kelas: data.kelas,
                        jurusan: data.jurusan,
                        nis: data.nis,
                        status: data.status,
                        metode: data.metode,
                        keterangan: data.keterangan || '',
                        waktu: data.waktu,
                        tanggal: data.tanggal,
                        isNew: true
                    });
                    
                    tampilkanKartuSukses(data);
                    
                } catch(e) {
                    console.error('❌ Error parsing data absensi:', e);
                    tampilkanKartuKosong();
                }
            } else {
                console.log('⚠️ Tidak ada data absensi');
                tampilkanKartuKosong();
            }
            
            renderTable();
            updateStats();
        }

        // ================================================================
        // 3. TAMPILKAN KARTU SUKSES
        // ================================================================
        function tampilkanKartuSukses(data) {
            document.getElementById('successCard').classList.remove('hidden');
            document.getElementById('emptyCard').classList.add('hidden');
            document.getElementById('successNama').textContent = `${data.nama} • ${data.kelas}`;
            document.getElementById('successTime').textContent = `${data.waktu} • ${data.tanggal}`;
        }

        function tampilkanKartuKosong() {
            document.getElementById('successCard').classList.add('hidden');
            document.getElementById('emptyCard').classList.remove('hidden');
        }

        // ================================================================
        // 4. RENDER TABEL
        // ================================================================
        function renderTable() {
            const tbody = document.getElementById('studentTableBody');
            
            if (absensiList.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fas fa-inbox text-4xl mb-2 block"></i>
                            <p>Belum ada data absensi</p>
                        </td>
                    </tr>
                `;
                document.getElementById('totalSiswa').textContent = 0;
                document.getElementById('totalAll').textContent = 0;
                return;
            }
            
            absensiList.sort((a, b) => new Date(b.tanggal + ' ' + b.waktu) - new Date(a.tanggal + ' ' + a.waktu));
            
            let html = '';
            absensiList.forEach((item, index) => {
                const statusBadge = getStatusBadge(item.status);
                const metodeBadge = getMetodeBadge(item.metode);
                const isNew = item.isNew ? 'new-row' : '';
                const highlight = item.isNew ? 'bg-emerald-50/70 border-l-4 border-emerald-500' : '';
                const newLabel = item.isNew ? `<span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium ml-2"><i class="fas fa-star text-[8px]"></i> Baru</span>` : '';
                
                html += `
                    <tr class="table-row-hover ${highlight} ${isNew}">
                        <td class="px-6 py-4 text-sm text-slate-500">${index + 1}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-${getColor(item.nama)}-100 flex items-center justify-center text-${getColor(item.nama)}-600 text-sm font-bold">
                                    ${item.nama.charAt(0).toUpperCase()}
                                </div>
                                <span class="font-medium text-slate-800">${item.nama}</span>
                                ${newLabel}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">${item.kelas}</td>
                        <td class="px-6 py-4">${statusBadge}</td>
                        <td class="px-6 py-4 text-sm ${item.isNew ? 'font-medium text-emerald-600' : 'text-slate-500'}">
                            ${item.waktu}
                            ${item.isNew ? '<span class="text-[10px] text-emerald-400 ml-1">(baru saja)</span>' : ''}
                        </td>
                        <td class="px-6 py-4">${metodeBadge}</td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
            absensiList.forEach(item => item.isNew = false);
            document.getElementById('totalSiswa').textContent = absensiList.length;
            document.getElementById('totalAll').textContent = absensiList.length;
        }

        // ================================================================
        // 5. GET STATUS BADGE
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

        function getMetodeBadge(metode) {
            const badges = {
                'Scan QR': `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs badge-metode-scan"><i class="fas fa-camera text-[10px]"></i> Scan QR</span>`,
                'Scan QR (Simulasi)': `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs badge-metode-scan"><i class="fas fa-camera text-[10px]"></i> Scan QR</span>`,
                'ID Unik': `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs badge-metode-id"><i class="fas fa-keyboard text-[10px]"></i> ID Unik</span>`,
                'Izin/Sakit': `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs badge-metode-izin"><i class="fas fa-file-medical-alt text-[10px]"></i> Izin/Sakit</span>`
            };
            return badges[metode] || badges['Scan QR'];
        }

        function getColor(name) {
            const colors = ['indigo', 'emerald', 'amber', 'red', 'purple', 'blue', 'pink', 'orange'];
            return colors[name.length % colors.length];
        }

        // ================================================================
        // 6. UPDATE STATISTIK
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
        // 7. ✅ UPDATE NAVBAR NAME (DARI EMAIL YANG DIDAFTARKAN)
        // ================================================================
        function updateNavbarName() {
            const nama = localStorage.getItem('user_nama');
            const email = localStorage.getItem('user_email');
            
            console.log('📧 Email:', email);
            console.log('👤 Nama:', nama);
            
            if (nama) {
                const navName = document.getElementById('userNavName');
                const navNameMobile = document.getElementById('userNavNameMobile');
                
                if (navName) navName.textContent = nama;
                if (navNameMobile) navNameMobile.textContent = nama.split(' ')[0];
                
                console.log('✅ Navbar diupdate:', nama);
            } else {
                console.warn('⚠️ Nama tidak ditemukan, pakai default');
            }
        }

        // ================================================================
        // 8. EXPORT DATA
        // ================================================================
        function exportData() {
            if (absensiList.length === 0) {
                showNotification('⚠️ Tidak ada data untuk di-export!');
                return;
            }
            showNotification('📥 Mengexport data absensi...');
            setTimeout(() => {
                showNotification('✅ Data berhasil di-export!');
            }, 1500);
        }

        // ================================================================
        // 9. NOTIFICATION
        // ================================================================
        function showNotification(message) {
            const oldNotif = document.querySelector('.notification-toast');
            if (oldNotif) oldNotif.remove();
            const notification = document.createElement('div');
            notification.className = 'notification-toast fixed top-24 left-1/2 transform -translate-x-1/2 bg-indigo-600 text-white px-6 py-3 rounded-xl shadow-lg z-50 transition-all duration-500';
            notification.innerHTML = `<div class="flex items-center gap-3"><i class="fas fa-info-circle text-xl"></i><span class="text-sm font-medium">${message}</span></div>`;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translate(-50%, -20px)';
                setTimeout(() => notification.remove(), 500);
            }, 4000);
        }

        // ================================================================
        // 10. UPDATE CLOCK
        // ================================================================
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { 
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false 
            });
            document.getElementById('currentTime').textContent = timeStr + ' WIB';
            
            const dateStr = now.toLocaleDateString('id-ID', { 
                weekday: 'long', day: '2-digit', month: '2-digit', year: 'numeric' 
            });
            document.getElementById('currentDate').textContent = dateStr;
        }

        // ================================================================
        // 11. INIT
        // ================================================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📷 Halaman Verifikasi siap!');
            
            // Load data absensi
            loadDataAbsensi();
            
            // ✅ Update nama di navbar
            updateNavbarName();
            
            // Update clock
            updateClock();
            setInterval(updateClock, 1000);
        });

    </script>

</body>
</html>