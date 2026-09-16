<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hadirin.web · Identitas Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; }
        .form-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
        }
        .form-input:focus { border-color: #101bb9; box-shadow: 0 0 0 3px rgba(16, 61, 185, 0.35); }
        .form-label { display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 4px; }
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 16px;
        }
        .step-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: bold;
            transition: all 0.3s;
        }
        .step-dot.active { background: #104bb9; color: white; }
        .step-dot.inactive { background: #e2e8f0; color: #385d91; }
        .step-line { width: 40px; height: 2px; background: #e2e8f0; }
        .step-line.active { background: #1310b9; }
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
                    <a href="{{ route('identitas.siswa') }}" class="hover:text-indigo-600 transition">Absen Siswa</a>
                    <a href="{{ route('identitas.guru') }}" class="hover:text-indigo-600 transition text-blue-600 font-semibold">Dashboard Guru</a>
                    <a href="{{ route('rekap.laporan') }}" class="hover:text-indigo-600 transition">Rekap</a>
                </div>
                <div class="hidden md:block">
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-600"><i class="fas fa-user-circle text-blue-600 text-lg"></i> Guru</span>
                        <a href="{{ route('login') }}" class="text-sm text-red-500 hover:text-red-700 transition"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
                <div class="md:hidden flex items-center gap-3">
                    <span class="text-sm font-medium text-slate-600"><i class="fas fa-user-circle text-blue-600"></i> Guru</span>
                    <button class="text-slate-500 hover:text-indigo-600 transition"><i class="fas fa-bars text-xl"></i></button>
                </div>
            </nav>
        </div>
    </header>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        <!-- Header -->
        <div class="text-center mb-6 md:mb-8">
            <div class="inline-block bg-blue-100/80 text-blue-700 text-xs font-semibold px-4 py-1.5 rounded-full border border-blue-200/60 mb-3">
                <i class="fas fa-chalkboard-teacher mr-2"></i> Dashboard Guru
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-800">Identitas Guru</h1>
            <p class="text-sm text-slate-500 mt-2">Isi data diri Anda sebelum masuk ke dashboard kelas</p>
        </div>

        <!-- ====== STEP INDICATOR ====== -->
        <div class="step-indicator">
            <div class="step-dot active" id="step1">1</div>
            <div class="step-line" id="line1"></div>
            <div class="step-dot inactive" id="step2">2</div>
            <div class="step-line" id="line2"></div>
            <div class="step-dot inactive" id="step3">3</div>
        </div>
        <p class="text-center text-xs text-slate-500 mb-8 -mt-4">
            <span id="stepText">Langkah 1: Isi Identitas Guru</span>
        </p>

        <!-- ====== FORM IDENTITAS GURU ====== -->
        <div id="identitasForm" class="max-w-3xl mx-auto mb-8">
            <div class="bg-white rounded-2xl shadow-xl border-2 border-blue-200/60 p-6 md:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-tie text-2xl text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Identitas Guru</h3>
                        <p class="text-sm text-slate-500">Isi data diri Anda untuk mengakses dashboard kelas</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nama Guru -->
                    <div class="md:col-span-2">
                        <label class="form-label">
                            <i class="fas fa-user text-blue-500 mr-1"></i> Nama Lengkap Guru
                        </label>
                        <input type="text" id="inputNamaGuru" class="form-input" placeholder="Contoh: Budi Santoso, S.Kom" />
                    </div>

                    <!-- Wali Kelas -->
                    <div class="md:col-span-1">
                        <label class="form-label">
                            <i class="fas fa-door-open text-blue-500 mr-1"></i> Wali Kelas
                        </label>
                        <select id="inputWaliKelas" class="form-input">
                            <option value="">Pilih Kelas</option>
                            <option value="X">X</option>
                            <option value="XI">XI</option>
                            <option value="XII">XII</option>
                        </select>
                    </div>

                    <!-- Jurusan -->
                    <div class="md:col-span-1">
                        <label class="form-label">
                            <i class="fas fa-graduation-cap text-blue-500 mr-1"></i> Jurusan
                        </label>
                        <select id="inputJurusan" class="form-input">
                            <option value="">Pilih Jurusan</option>
                            <option value="RPL">RPL (Rekayasa Perangkat Lunak)</option>
                            <option value="TKJ">TKJ (Teknik Komputer & Jaringan)</option>
                            <option value="MM">MM (Multimedia)</option>
                            <option value="AKL">AKL (Akuntansi)</option>
                        </select>
                    </div>

                    <!-- Kepentingan Guru -->
                    <div class="md:col-span-2">
                        <label class="form-label">
                            <i class="fas fa-briefcase text-blue-500 mr-1"></i> Kepentingan / Tujuan
                        </label>
                        <select id="inputKepentingan" class="form-input">
                            <option value="">Pilih Kepentingan</option>
                            <option value="Melihat Progres Absensi">Melihat Progres Absensi</option>
                            <option value="Mengelola Data Kelas">Mengelola Data Kelas</option>
                            <option value="Membuat Laporan Bulanan">Membuat Laporan Bulanan</option>
                            <option value="Memantau Real-Time">Memantau Real-Time</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button onclick="simpanIdentitasGuru()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-xl shadow-md shadow-blue-200/60 transition flex items-center gap-2">
                        <i class="fas fa-arrow-right"></i> Masuk ke Dashboard Kelas
                    </button>
                </div>
            </div>
        </div>

        <!-- Tombol Kembali -->
        <div class="mt-8 text-center">
            <a href="{{ route('beranda') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
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
            <span><i class="fas fa-image mr-1"></i> Identitas Guru</span>
        </div>
    </footer>

    <!-- ========== JAVASCRIPT ========== -->
    <script>
        let identitasGuru = null;

        // ============================================================
        // SIMPAN IDENTITAS GURU
        // ============================================================
        function simpanIdentitasGuru() {
            const nama = document.getElementById('inputNamaGuru').value.trim();
            const waliKelas = document.getElementById('inputWaliKelas').value;
            const jurusan = document.getElementById('inputJurusan').value;
            const kepentingan = document.getElementById('inputKepentingan').value;

            // Validasi
            if (!nama) {
                showNotification('⚠️ Nama guru harus diisi!');
                document.getElementById('inputNamaGuru').focus();
                return;
            }
            if (!waliKelas) {
                showNotification('⚠️ Wali kelas harus dipilih!');
                document.getElementById('inputWaliKelas').focus();
                return;
            }
            if (!jurusan) {
                showNotification('⚠️ Jurusan harus dipilih!');
                document.getElementById('inputJurusan').focus();
                return;
            }
            if (!kepentingan) {
                showNotification('⚠️ Kepentingan harus dipilih!');
                document.getElementById('inputKepentingan').focus();
                return;
            }

            // Simpan identitas guru
            identitasGuru = {
                nama: nama,
                waliKelas: waliKelas,
                jurusan: jurusan,
                kepentingan: kepentingan,
                kelasLengkap: `${waliKelas}.${jurusan}`
            };

            localStorage.setItem('identitas_guru', JSON.stringify(identitasGuru));

            // Update step
            document.getElementById('step1').classList.remove('active');
            document.getElementById('step1').classList.add('inactive');
            document.getElementById('step2').classList.add('active');
            document.getElementById('line1').classList.add('active');
            document.getElementById('stepText').textContent = 'Langkah 2: Mengalihkan ke Dashboard...';

            showNotification(`✅ Selamat datang, ${nama}! Mengalihkan ke dashboard kelas ${waliKelas}.${jurusan}...`);

            // Redirect ke dashboard guru
            setTimeout(() => {
                window.location.href = "{{ route('dashboard.guru') }}";
            }, 1500);
        }

        // ============================================================
        // CEK IDENTITAS SAAT LOAD
        // ============================================================
        function cekIdentitasGuru() {
            const saved = localStorage.getItem('identitas_guru');
            if (saved) {
                try {
                    identitasGuru = JSON.parse(saved);
                    document.getElementById('inputNamaGuru').value = identitasGuru.nama;
                    document.getElementById('inputWaliKelas').value = identitasGuru.waliKelas;
                    document.getElementById('inputJurusan').value = identitasGuru.jurusan;
                    document.getElementById('inputKepentingan').value = identitasGuru.kepentingan;
                } catch(e) {
                    console.error('Error load identitas guru:', e);
                }
            }
        }

        // ============================================================
        // NOTIFICATION
        // ============================================================
        function showNotification(message) {
            const oldNotif = document.querySelector('.notification-toast');
            if (oldNotif) oldNotif.remove();
            const notification = document.createElement('div');
            notification.className = 'notification-toast fixed top-24 left-1/2 transform -translate-x-1/2 bg-emerald-600 text-white px-6 py-3 rounded-xl shadow-lg z-50 transition-all duration-500';
            notification.innerHTML = `<div class="flex items-center gap-3"><i class="fas fa-info-circle text-xl"></i><span class="text-sm font-medium">${message}</span></div>`;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translate(-50%, -20px)';
                setTimeout(() => notification.remove(), 500);
            }, 4000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            cekIdentitasGuru();
            console.log('👨‍🏫 Halaman Identitas Guru siap!');
        });
    </script>

</body>
</html>