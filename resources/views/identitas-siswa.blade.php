<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hadirin.web · Identitas Siswa</title>
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
        .form-input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,0.2); }
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
        .step-dot.active { background: #7c3aed; color: white; }
        .step-dot.inactive { background: #e2e8f0; color: #94a3b8; }
        .step-line { width: 40px; height: 2px; background: #e2e8f0; }
        .step-line.active { background: #7c3aed; }
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
                    <a href="{{ route('identitas.guru') }}" class="hover:text-indigo-600 transition">Dashboard Guru</a>
                    <a href="{{ route('rekap.laporan') }}" class="hover:text-indigo-600 transition">Rekap</a>
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

                <!-- Mobile Menu -->
                <div class="md:hidden flex items-center gap-3">
                    <span class="text-sm font-medium text-slate-600 flex items-center gap-1">
                        <i class="fas fa-user-circle text-indigo-600"></i>
                        <span id="userNavNameMobile">Siswa</span>
                    </span>
                    <button class="text-slate-500 hover:text-indigo-600 transition">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </nav>
        </div>
    </header>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        <!-- Header -->
        <div class="text-center mb-6 md:mb-8">
            <div class="inline-block bg-indigo-100/80 text-indigo-700 text-xs font-semibold px-4 py-1.5 rounded-full border border-indigo-200/60 mb-3">
                <i class="fas fa-qrcode mr-2"></i> Absensi Mandiri
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-800">Absen hari ini</h1>
            <p class="text-sm text-slate-500 mt-2">Ikuti langkah-langkah absensi di bawah ini</p>
        </div>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step-dot active" id="step1">1</div>
            <div class="step-line" id="line1"></div>
            <div class="step-dot inactive" id="step2">2</div>
            <div class="step-line" id="line2"></div>
            <div class="step-dot inactive" id="step3">3</div>
        </div>
        <p class="text-center text-xs text-slate-500 mb-8 -mt-4">
            <span id="stepText">Langkah 1: Isi Identitas Siswa</span>
        </p>

        <!-- FORM IDENTITAS SISWA -->
        <div id="identitasForm" class="max-w-3xl mx-auto mb-8">
            <div class="bg-white rounded-2xl shadow-xl border-2 border-indigo-200/60 p-6 md:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-edit text-2xl text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Identitas Siswa</h3>
                        <p class="text-sm text-slate-500">Isi data diri Anda sebelum melakukan absensi</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-1">
                        <label class="form-label">
                            <i class="fas fa-user text-indigo-500 mr-1"></i> Nama Lengkap
                        </label>
                        <input type="text" id="inputNama" class="form-input" placeholder="Contoh: Najla Mutia" />
                    </div>

                    <div class="md:col-span-1">
                        <label class="form-label">
                            <i class="fas fa-door-open text-indigo-500 mr-1"></i> Kelas
                        </label>
                        <select id="inputKelas" class="form-input">
                            <option value="">Pilih Kelas</option>
                            <option value="X">X</option>
                            <option value="XI">XI</option>
                            <option value="XII">XII</option>
                        </select>
                    </div>

                    <div class="md:col-span-1">
                        <label class="form-label">
                            <i class="fas fa-graduation-cap text-indigo-500 mr-1"></i> Jurusan
                        </label>
                        <select id="inputJurusan" class="form-input">
                            <option value="">Pilih Jurusan</option>
                            <option value="RPL">RPL (Rekayasa Perangkat Lunak)</option>
                            <option value="TKJ">TKJ (Teknik Komputer & Jaringan)</option>
                            <option value="MM">MM (Multimedia)</option>
                            <option value="AKL">AKL (Akuntansi)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button onclick="simpanIdentitas()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-3 rounded-xl shadow-md shadow-indigo-200/60 transition flex items-center gap-2">
                        <i class="fas fa-arrow-right"></i> Lanjutkan ke Absensi
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
            <span><i class="fas fa-image mr-1"></i> Identitas Siswa</span>
        </div>
    </footer>

    <!-- ========== JAVASCRIPT ========== -->
    <script>
        // ============================================================
        // STATE
        // ============================================================
        let identitasSiswa = null;

        // ============================================================
        // SIMPAN IDENTITAS (REDIRECT KE HALAMAN ABSENSI)
        // ============================================================
        function simpanIdentitas() {
            const nama = document.getElementById('inputNama').value.trim();
            const kelas = document.getElementById('inputKelas').value;
            const jurusan = document.getElementById('inputJurusan').value;

            // Validasi
            if (!nama) {
                showNotification('⚠️ Nama lengkap harus diisi!');
                document.getElementById('inputNama').focus();
                return;
            }
            if (!kelas) {
                showNotification('⚠️ Kelas harus dipilih!');
                document.getElementById('inputKelas').focus();
                return;
            }
            if (!jurusan) {
                showNotification('⚠️ Jurusan harus dipilih!');
                document.getElementById('inputJurusan').focus();
                return;
            }

            // Simpan identitas
            identitasSiswa = {
                nama: nama,
                kelas: kelas,
                jurusan: jurusan,
                kelasLengkap: `${kelas}.${jurusan}`
            };

            localStorage.setItem('identitas_siswa', JSON.stringify(identitasSiswa));

            // Update step indicator
            document.getElementById('step1').classList.remove('active');
            document.getElementById('step1').classList.add('inactive');
            document.getElementById('step2').classList.add('active');
            document.getElementById('line1').classList.add('active');
            document.getElementById('stepText').textContent = 'Langkah 2: Pilih Metode Absensi';

            showNotification(`✅ Identitas tersimpan! Mengalihkan ke halaman absensi...`);

            // Redirect ke halaman absensi QR
            setTimeout(() => {
                window.location.href = "{{ route('absen.siswa.qr') }}";
            }, 1500);
        }

        // ============================================================
        // CEK IDENTITAS SAAT LOAD
        // ============================================================
        function cekIdentitas() {
            const saved = localStorage.getItem('identitas_siswa');
            if (saved) {
                try {
                    identitasSiswa = JSON.parse(saved);
                    document.getElementById('inputNama').value = identitasSiswa.nama;
                    document.getElementById('inputKelas').value = identitasSiswa.kelas;
                    document.getElementById('inputJurusan').value = identitasSiswa.jurusan;
                } catch(e) {
                    console.error('Error load identitas:', e);
                }
            }
        }

        // ============================================================
        // UPDATE NAMA DI NAVBAR DARI EMAIL YANG DIDAFTARKAN
        // ============================================================
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

        // ============================================================
        // NOTIFICATION
        // ============================================================
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

        // ============================================================
        // INIT
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            cekIdentitas();
            updateNavbarName(); // ✅ Update nama di navbar
            console.log('📝 Halaman Identitas Siswa siap!');
        });
    </script>

</body>
</html>