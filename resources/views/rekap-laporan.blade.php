<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hadirin.web · Rekap & Laporan</title>
    <!-- Tailwind via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <!-- Font tambahan -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet" />
    <!-- SheetJS untuk Export Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- jsPDF untuk Export PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
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
        .btn-export {
            transition: all 0.3s ease;
        }
        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.25);
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
        }
        .print-only {
            display: none;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">

    <!-- ========== NAVBAR ========== -->
    <header class="w-full bg-white/80 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-10 no-print">
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
                    <a href="{{ route('dashboard.guru') }}" class="hover:text-indigo-600 transition">Dashboard Guru</a>
                    <a href="{{ route('rekap.laporan') }}" class="hover:text-indigo-600 transition text-indigo-600 font-semibold">Rekap</a>
                </div>

                <!-- Tombol User -->
                <div class="hidden md:block">
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-600">
                            <i class="fas fa-user-circle text-indigo-600 text-lg"></i>
                            Najla Mutia
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

    <!-- ========== HEADER ========== -->
    <section class="gradient-header text-white py-8 md:py-12 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="inline-block bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-medium mb-2">
                        <i class="fas fa-file-alt mr-1"></i> Laporan
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold">Rekap & Laporan</h1>
                    <p class="text-indigo-100/90 text-sm mt-1">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        <span id="currentDate">Memuat tanggal...</span>
                    </p>
                </div>
                <div class="mt-4 md:mt-0 flex flex-wrap gap-2">
                    <button onclick="exportPDF()" class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white text-sm font-medium px-4 py-2 rounded-lg transition backdrop-blur-sm">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                    <button onclick="exportExcel()" class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white text-sm font-medium px-4 py-2 rounded-lg transition backdrop-blur-sm">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8">

        <!-- ====== QUICK ACCESS ====== -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 no-print">
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

            <a href="{{ route('rekap.laporan') }}" class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 flex items-center gap-4 hover:shadow-md transition group">
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

        <!-- ====== FILTER ====== -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 mb-6 no-print">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-filter text-slate-400 text-sm"></i>
                    <span class="text-sm font-medium text-slate-600">Filter:</span>
                </div>
                <select id="filterBulan" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="2026-08">Agustus 2026</option>
                    <option value="2026-07">Juli 2026</option>
                    <option value="2026-06">Juni 2026</option>
                    <option value="2026-05">Mei 2026</option>
                </select>
                <select id="filterKelas" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="XII.RPL">XII.RPL</option>
                    <option value="XII.TKJ">XII.TKJ</option>
                    <option value="XI.RPL">XI.RPL</option>
                    <option value="X.TKJ">X.TKJ</option>
                </select>
                <button onclick="applyFilter()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-search mr-1"></i> Terapkan
                </button>
                <button onclick="resetFilter()" class="bg-slate-200 hover:bg-slate-300 text-slate-600 text-sm font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-undo mr-1"></i> Reset
                </button>
            </div>
        </div>

        <!-- ====== TABEL REKAP ====== -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200/60 overflow-hidden" id="tableContainer">
            <!-- Header Tabel -->
            <div class="px-6 py-4 border-b border-slate-200/60 bg-slate-50/50 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                    <i class="fas fa-table text-indigo-500"></i>
                    <h3 class="font-semibold text-slate-800">Rekap Absen Harian Persiswa</h3>
                    <span class="text-xs bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full">
                        <span id="totalSiswa">0</span> Siswa
                    </span>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <i class="fas fa-calendar-alt"></i>
                    <span id="periodeLabel">Agustus 2026</span>
                    <span class="mx-1">|</span>
                    <span id="kelasLabel">Jurusan RPL</span>
                </div>
            </div>

            <!-- Tabel -->
            <div class="overflow-x-auto" id="tableWrapper">
                <table class="w-full" id="rekapTable">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/60">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Hadir</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Izin</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Sakit</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Alpha</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Persentase</th>
                        </tr>
                    </thead>
                    <tbody id="rekapTableBody" class="divide-y divide-slate-200/60">
                        <!-- Data akan diisi oleh JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Footer Tabel: Summary -->
            <div class="px-6 py-4 border-t border-slate-200/60 bg-slate-50/50 flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        Rata-rata Hadir: <span class="font-semibold text-slate-700" id="avgHadir">0%</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                        Total Izin: <span class="font-semibold text-slate-700" id="totalIzin">0</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span>
                        Total Sakit: <span class="font-semibold text-slate-700" id="totalSakit">0</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-gray-400"></span>
                        Total Alpha: <span class="font-semibold text-slate-700" id="totalAlpha">0</span>
                    </span>
                </div>
                <div class="text-xs text-slate-400">
                    <i class="fas fa-print mr-1"></i> Total: <span id="totalAll">0</span> Siswa
                </div>
            </div>
        </div>

        <!-- Tombol Kembali -->
        <div class="mt-8 text-center no-print">
            <a href="{{ route('dashboard.guru') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard Guru
            </a>
        </div>

    </main>

    <!-- ========== FOOTER ========== -->
    <footer class="w-full border-t border-slate-200/60 py-4 text-center text-[10px] text-slate-400 bg-white/40 backdrop-blur-sm no-print">
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
        // ===== DATA REKAP =====
        let rekapData = [
            { name: 'Najla', kelas: 'XII.RPL', hadir: 30, izin: 0, sakit: 1, alpha: 0 },
            { name: 'Yasmin', kelas: 'XII.RPL', hadir: 31, izin: 0, sakit: 0, alpha: 0 },
            { name: 'Dina', kelas: 'XII.RPL', hadir: 30, izin: 0, sakit: 1, alpha: 0 },
            { name: 'Alex', kelas: 'XII.RPL', hadir: 29, izin: 1, sakit: 0, alpha: 1 },
            { name: 'Arjuna', kelas: 'XII.RPL', hadir: 31, izin: 0, sakit: 0, alpha: 0 }
        ];

        let filteredData = [...rekapData];

        // ===== RENDER TABEL =====
        function renderTable() {
            const tbody = document.getElementById('rekapTableBody');
            
            let html = '';
            let totalHadir = 0, totalIzin = 0, totalSakit = 0, totalAlpha = 0;
            
            filteredData.forEach((item, index) => {
                const total = item.hadir + item.izin + item.sakit + item.alpha;
                const persentase = total > 0 ? Math.round((item.hadir / total) * 100) : 0;
                
                totalHadir += item.hadir;
                totalIzin += item.izin;
                totalSakit += item.sakit;
                totalAlpha += item.alpha;
                
                const color = persentase >= 90 ? 'emerald' : persentase >= 75 ? 'amber' : 'rose';
                
                html += `
                    <tr class="table-row-hover">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-${getColor(item.name)}-100 flex items-center justify-center text-${getColor(item.name)}-600 text-sm font-bold">
                                    ${item.name.charAt(0)}
                                </div>
                                <span class="font-medium text-slate-800">${item.name}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">${item.kelas}</td>
                        <td class="px-6 py-4 text-center text-sm font-semibold text-emerald-600">${item.hadir}</td>
                        <td class="px-6 py-4 text-center text-sm text-amber-600">${item.izin}</td>
                        <td class="px-6 py-4 text-center text-sm text-red-500">${item.sakit}</td>
                        <td class="px-6 py-4 text-center text-sm text-gray-500">${item.alpha}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-${color}-100 text-${color}-700">
                                ${persentase}%
                            </span>
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
            
            // Update statistik
            const totalSiswa = filteredData.length;
            document.getElementById('totalSiswa').textContent = totalSiswa;
            document.getElementById('totalAll').textContent = totalSiswa;
            document.getElementById('totalIzin').textContent = totalIzin;
            document.getElementById('totalSakit').textContent = totalSakit;
            document.getElementById('totalAlpha').textContent = totalAlpha;
            
            const avgHadir = totalSiswa > 0 ? Math.round((totalHadir / (totalHadir + totalIzin + totalSakit + totalAlpha)) * 100) : 0;
            document.getElementById('avgHadir').textContent = avgHadir + '%';
        }

        // ===== GET COLOR =====
        function getColor(name) {
            const colors = ['indigo', 'emerald', 'amber', 'red', 'purple', 'blue', 'pink', 'orange', 'teal', 'cyan'];
            return colors[name.length % colors.length];
        }

        // ===== FILTER =====
        function applyFilter() {
            const bulan = document.getElementById('filterBulan').value;
            const kelas = document.getElementById('filterKelas').value;
            
            // Simulasi filter berdasarkan kelas
            filteredData = rekapData.filter(item => item.kelas === kelas);
            
            document.getElementById('periodeLabel').textContent = 
                document.getElementById('filterBulan').options[document.getElementById('filterBulan').selectedIndex].text;
            document.getElementById('kelasLabel').textContent = 'Jurusan ' + kelas.split('.')[1] || kelas;
            
            renderTable();
            showNotification(`✅ Filter diterapkan: ${document.getElementById('periodeLabel').textContent} - ${document.getElementById('kelasLabel').textContent}`);
        }

        function resetFilter() {
            document.getElementById('filterBulan').value = '2026-08';
            document.getElementById('filterKelas').value = 'XII.RPL';
            filteredData = [...rekapData];
            document.getElementById('periodeLabel').textContent = 'Agustus 2026';
            document.getElementById('kelasLabel').textContent = 'Jurusan RPL';
            renderTable();
            showNotification('🔄 Filter direset');
        }

        // ===== EXPORT PDF =====
        function exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('landscape', 'mm', 'a4');
            
            // Title
            doc.setFontSize(18);
            doc.setTextColor(79, 70, 229);
            doc.text('Rekap Absensi Hadirin.web', 14, 22);
            
            doc.setFontSize(10);
            doc.setTextColor(100, 116, 139);
            doc.text(`Periode: ${document.getElementById('periodeLabel').textContent} | ${document.getElementById('kelasLabel').textContent}`, 14, 30);
            doc.text(`Tanggal: ${new Date().toLocaleDateString('id-ID')}`, 14, 36);
            
            // Table
            const tableData = filteredData.map(item => {
                const total = item.hadir + item.izin + item.sakit + item.alpha;
                const persentase = total > 0 ? Math.round((item.hadir / total) * 100) : 0;
                return [item.name, item.kelas, item.hadir, item.izin, item.sakit, item.alpha, persentase + '%'];
            });
            
            doc.autoTable({
                startY: 45,
                head: [['Siswa', 'Kelas', 'Hadir', 'Izin', 'Sakit', 'Alpha', 'Persentase']],
                body: tableData,
                theme: 'striped',
                headStyles: { fillColor: [79, 70, 229], textColor: [255, 255, 255] },
                styles: { fontSize: 9 },
                columnStyles: {
                    0: { cellWidth: 35 },
                    1: { cellWidth: 30 },
                    2: { cellWidth: 20, halign: 'center' },
                    3: { cellWidth: 20, halign: 'center' },
                    4: { cellWidth: 20, halign: 'center' },
                    5: { cellWidth: 20, halign: 'center' },
                    6: { cellWidth: 25, halign: 'center' }
                }
            });
            
            // Footer
            const pageCount = doc.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(150, 150, 150);
                doc.text(`© 2026 Hadirin.web - Halaman ${i} dari ${pageCount}`, 14, doc.internal.pageSize.height - 10);
            }
            
            doc.save(`Rekap_Absensi_${document.getElementById('periodeLabel').textContent}.pdf`);
            showNotification('✅ PDF berhasil diunduh!');
        }

        // ===== EXPORT EXCEL =====
        function exportExcel() {
            const excelData = filteredData.map(item => {
                const total = item.hadir + item.izin + item.sakit + item.alpha;
                const persentase = total > 0 ? Math.round((item.hadir / total) * 100) : 0;
                return {
                    'Siswa': item.name,
                    'Kelas': item.kelas,
                    'Hadir': item.hadir,
                    'Izin': item.izin,
                    'Sakit': item.sakit,
                    'Alpha': item.alpha,
                    'Persentase': persentase + '%'
                };
            });
            
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);
            
            // Set column widths
            ws['!cols'] = [
                { wch: 15 }, // Siswa
                { wch: 12 }, // Kelas
                { wch: 10 }, // Hadir
                { wch: 10 }, // Izin
                { wch: 10 }, // Sakit
                { wch: 10 }, // Alpha
                { wch: 14 }  // Persentase
            ];
            
            XLSX.utils.book_append_sheet(wb, ws, 'Rekap');
            XLSX.writeFile(wb, `Rekap_Absensi_${document.getElementById('periodeLabel').textContent}.xlsx`);
            showNotification('✅ Excel berhasil diunduh!');
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
            }, 3000);
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
        }

        // ===== INIT =====
        document.addEventListener('DOMContentLoaded', function() {
            renderTable();
            updateClock();
            setInterval(updateClock, 60000);
            
            // Set default filter
            document.getElementById('filterBulan').value = '2026-08';
            document.getElementById('filterKelas').value = 'XII.RPL';
        });
    </script>

</body>
</html>