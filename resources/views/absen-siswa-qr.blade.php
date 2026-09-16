<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hadirin.web · Absensi Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; }
        .qr-frame { border: 4px dashed #6366f1; background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%); }
        .scan-animation { animation: pulse 1.5s ease-in-out infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.6; transform: scale(0.95); } }
        .card-hover { transition: all 0.3s ease; cursor: pointer; }
        .card-hover:hover { transform: translateY(-4px); }
        .hidden { display: none !important; }
        #videoPreview { transform: scaleX(-1); }
        .form-input { width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; outline: none; transition: all 0.2s; }
        .form-input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,0.2); }
        .form-label { display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 4px; }
        .modal-overlay { background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
        .modal { animation: modalIn 0.3s ease-out; max-height: 90vh; overflow-y: auto; }
        @keyframes modalIn { 0% { transform: scale(0.9) translateY(20px); opacity: 0; } 100% { transform: scale(1) translateY(0); opacity: 1; } }
        .btn-submit { background: #7c3aed; color: white; padding: 10px 24px; border-radius: 10px; font-size: 14px; border: none; cursor: pointer; transition: 0.2s; }
        .btn-submit:hover { background: #6d28d9; }
        .btn-cancel { background: #e2e8f0; color: #475569; padding: 10px 24px; border-radius: 10px; font-size: 14px; border: none; cursor: pointer; transition: 0.2s; }
        .btn-cancel:hover { background: #cbd5e1; }
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
                    <a href="{{ route('dashboard.guru') }}" class="hover:text-indigo-600 transition">Dashboard Guru</a>
                    <a href="{{ route('rekap.laporan') }}" class="hover:text-indigo-600 transition">Rekap</a>
                </div>
                <div class="hidden md:block">
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-600">
                            <i class="fas fa-user-circle text-indigo-600 text-lg"></i>
                            <span id="userNavName">Siswa</span>
                        </span>
                        <a href="{{ route('login') }}" class="text-sm text-red-500 hover:text-red-700 transition">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
                <div class="md:hidden flex items-center gap-3">
                    <span class="text-sm font-medium text-slate-600">
                        <i class="fas fa-user-circle text-indigo-600"></i> 
                        <span id="userNavNameMobile">Siswa</span>
                    </span>
                    <button class="text-slate-500 hover:text-indigo-600 transition"><i class="fas fa-bars text-xl"></i></button>
                </div>
            </nav>
        </div>
    </header>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        <!-- Header -->
        <div class="text-center mb-8 md:mb-12">
            <div class="inline-block bg-indigo-100/80 text-indigo-700 text-xs font-semibold px-4 py-1.5 rounded-full border border-indigo-200/60 mb-3">
                <i class="fas fa-qrcode mr-2"></i> Absensi Mandiri
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-800">Absen hari ini</h1>
            <p class="text-sm text-slate-500 mt-2">Pilih metode absensi yang tersedia</p>
        </div>

        <!-- INFO SISWA -->
        <div id="studentInfoDisplay" class="hidden max-w-3xl mx-auto mb-6">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-4 rounded-2xl shadow-lg flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-xl font-bold" id="infoInitial">A</div>
                <div class="flex-1">
                    <p class="text-xs opacity-80 uppercase tracking-wider">Siswa Teridentifikasi</p>
                    <p class="text-lg font-bold" id="infoNama">-</p>
                    <p class="text-xs opacity-90" id="infoKelas">-</p>
                </div>
                <a href="{{ route('identitas.siswa') }}" class="bg-white/20 hover:bg-white/30 text-white text-xs px-4 py-2 rounded-lg transition flex items-center gap-1">
                    <i class="fas fa-redo"></i> Ubah
                </a>
            </div>
        </div>

        <!-- 3 Pilihan Metode -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 max-w-5xl mx-auto">
            
            <!-- Card Scan QR -->
            <div class="bg-white rounded-2xl shadow-lg border-2 border-indigo-200/60 p-6 md:p-8 text-center card-hover transition-all duration-300 relative">
                <div class="absolute -top-3 -right-3 bg-indigo-600 text-white text-[10px] font-bold px-3 py-1 rounded-full">
                    <i class="fas fa-star mr-1"></i> Utama
                </div>
                <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-camera text-3xl text-indigo-600"></i>
                </div>
                <h4 class="text-xl font-bold text-slate-800 mb-2">Scan QR Code</h4>
                <p class="text-sm text-slate-500 mb-4">Aktifkan kamera untuk memindai QR Code pada kartu presensi.</p>
                <button onclick="activateCamera()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3.5 rounded-xl shadow-md shadow-indigo-200/60 transition flex items-center justify-center gap-2 text-base">
                    <i class="fas fa-video"></i> Aktifkan Kamera
                </button>
            </div>

            <!-- Card ID Unik -->
            <div class="bg-white rounded-2xl shadow-lg border-2 border-emerald-200/60 p-6 md:p-8 text-center card-hover transition-all duration-300">
                <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-keyboard text-3xl text-emerald-600"></i>
                </div>
                <h4 class="text-xl font-bold text-slate-800 mb-2">ID Unik</h4>
                <p class="text-sm text-slate-500 mb-4">Masukkan kode ID unik yang tertera pada kartu presensi.</p>
                <div class="flex gap-2">
                    <input type="text" id="idUnikInput" placeholder="Masukkan ID Unik" 
                           class="flex-1 px-2 py-3 bg-slate-50/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 transition" />
                    <button id="submitIdUnik" class="px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition shadow-md shadow-emerald-200/60 flex items-center justify-center gap-2 text-base whitespace-nowrap">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>

            <!-- Card Izin / Sakit -->
            <div class="bg-white rounded-2xl shadow-lg border-2 border-amber-200/60 p-6 md:p-8 text-center card-hover transition-all duration-300">
                <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-file-medical-alt text-3xl text-amber-600"></i>
                </div>
                <h4 class="text-xl font-bold text-slate-800 mb-2">Izin / Sakit</h4>
                <p class="text-sm text-slate-500 mb-4">Untuk siswa yang tidak dapat hadir karena izin atau sakit.</p>
                <button onclick="openIzinSakitModal()" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3.5 rounded-xl shadow-md shadow-amber-200/60 transition flex items-center justify-center gap-2 text-base">
                    <i class="fas fa-pen"></i> Ajukan Izin/Sakit
                </button>
            </div>
        </div>

        <!-- QR SCANNER AREA -->
        <div id="scannerArea" class="hidden max-w-2xl mx-auto mt-6">
            <div class="bg-white rounded-2xl shadow-xl border border-indigo-200/60 p-6 md:p-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-800">
                        <i class="fas fa-camera text-indigo-600 mr-2"></i> Pemindaian QR
                    </h3>
                    <button onclick="deactivateCamera()" class="text-sm text-red-500 hover:text-red-700 transition">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                </div>
                <div class="qr-frame rounded-2xl p-4 relative">
                    <div class="aspect-square max-w-sm mx-auto relative">
                        <video id="videoPreview" class="w-full h-full bg-black rounded-xl border-2 border-indigo-300 object-cover hidden" autoplay playsinline></video>
                        <canvas id="qrCanvas" class="hidden"></canvas>
                        <div id="videoPlaceholder" class="w-full h-full bg-slate-100 rounded-xl border-2 border-indigo-300 flex flex-col items-center justify-center">
                            <i class="fas fa-camera text-6xl text-indigo-300/50 mb-3"></i>
                            <p class="text-sm text-slate-400">Kamera akan aktif saat tombol ditekan</p>
                        </div>
                        <div class="absolute top-2 left-2 w-8 h-8 border-t-4 border-l-4 border-indigo-600 rounded-tl-lg"></div>
                        <div class="absolute top-2 right-2 w-8 h-8 border-t-4 border-r-4 border-indigo-600 rounded-tr-lg"></div>
                        <div class="absolute bottom-2 left-2 w-8 h-8 border-b-4 border-l-4 border-indigo-600 rounded-bl-lg"></div>
                        <div class="absolute bottom-2 right-2 w-8 h-8 border-b-4 border-r-4 border-indigo-600 rounded-br-lg"></div>
                    </div>
                    <div class="mt-3 text-center">
                        <p id="cameraStatus" class="text-sm text-slate-500">
                            <i class="fas fa-info-circle mr-1"></i> Tekan "Mulai Kamera" untuk memulai
                        </p>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap justify-center gap-3">
                    <button id="startCameraBtn" onclick="startCamera()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-xl transition">
                        <i class="fas fa-play mr-1"></i> Mulai Kamera
                    </button>
                    <button id="stopCameraBtn" onclick="stopCamera()" class="bg-slate-200 hover:bg-slate-300 text-slate-600 font-medium px-6 py-2.5 rounded-xl transition" disabled>
                        <i class="fas fa-stop mr-1"></i> Stop
                    </button>
                    <button onclick="deactivateCamera()" class="text-sm text-slate-500 hover:text-slate-700 transition px-4 py-2.5">
                        <i class="fas fa-arrow-left mr-1"></i> Tutup
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

    <!-- MODAL IZIN / SAKIT -->
    <div id="izinSakitModal" class="fixed inset-0 z-50 hidden">
        <div class="modal-overlay absolute inset-0" onclick="closeIzinSakitModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="modal bg-white rounded-2xl shadow-2xl max-w-lg w-full">
                <div class="flex items-center justify-between p-6 border-b border-slate-200/60">
                    <h3 class="text-xl font-bold text-slate-800">
                        <i class="fas fa-file-medical-alt text-amber-500 mr-2"></i>
                        Form Izin / Sakit
                    </h3>
                    <button onclick="closeIzinSakitModal()" class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="bg-indigo-50/70 border border-indigo-100/60 rounded-xl p-4">
                        <p class="text-xs text-slate-500 mb-1">Data Siswa:</p>
                        <p class="text-sm font-bold text-slate-800" id="modalIzinNama">-</p>
                        <p class="text-xs text-slate-600" id="modalIzinKelas">-</p>
                    </div>
                    
                    <div>
                        <label class="form-label">Jenis Ketidakhadiran</label>
                        <select id="izinJenis" class="form-input">
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Keterangan / Alasan</label>
                        <textarea id="izinKeterangan" rows="4" class="form-input" placeholder="Jelaskan alasan ketidakhadiran Anda..."></textarea>
                    </div>
                    <div>
                        <label class="form-label">Dokumen Pendukung (Opsional)</label>
                        <input type="file" id="izinFile" class="form-input p-2" accept=".pdf,.jpg,.png,.docx" />
                        <p class="text-xs text-slate-400 mt-1">Upload surat izin/sakit (PDF, JPG, PNG, DOCX) - Maks 2MB</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 p-6 border-t border-slate-200/60">
                    <button onclick="closeIzinSakitModal()" class="btn-cancel">Batal</button>
                    <button onclick="submitIzinSakit()" class="btn-submit">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="w-full border-t border-slate-200/60 py-4 text-center text-[10px] text-slate-400 bg-white/40 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 flex flex-wrap items-center justify-center gap-3">
            <span>© 2026 Sistem Absensi</span>
            <span class="w-px h-3 bg-slate-300"></span>
            <span>Hadirin.web</span>
            <span class="w-px h-3 bg-slate-300"></span>
            <span><i class="fas fa-image mr-1"></i> Absensi Siswa QR1.png</span>
        </div>
    </footer>

    <!-- ========== JAVASCRIPT ========== -->
    <script>
        // ============================================================
        // 1. VARIABEL GLOBAL
        // ============================================================
        let videoStream = null;
        let scanInterval = null;
        let isScanning = false;
        let identitasSiswa = null;

        // ============================================================
        // 2. CEK IDENTITAS
        // ============================================================
        function cekIdentitas() {
            const savedIdentitas = localStorage.getItem('identitas_siswa');
            if (savedIdentitas) {
                try {
                    identitasSiswa = JSON.parse(savedIdentitas);
                    console.log('✅ Identitas ditemukan:', identitasSiswa);
                    tampilkanInfoSiswa();
                    updateNavbarName();
                } catch(e) {
                    console.error('❌ Error parsing identitas:', e);
                    redirectKeIdentitas();
                }
            } else {
                console.log('⚠️ Belum ada identitas, redirect...');
                redirectKeIdentitas();
            }
        }

        function redirectKeIdentitas() {
            setTimeout(() => {
                window.location.href = "{{ route('identitas.siswa') }}";
            }, 500);
        }

        function tampilkanInfoSiswa() {
            if (!identitasSiswa) return;
            const display = document.getElementById('studentInfoDisplay');
            if (display) {
                display.classList.remove('hidden');
                document.getElementById('infoInitial').textContent = identitasSiswa.nama.charAt(0).toUpperCase();
                document.getElementById('infoNama').textContent = identitasSiswa.nama;
                
                const jurusanFull = identitasSiswa.jurusan === 'RPL' ? 'Rekayasa Perangkat Lunak' :
                                   identitasSiswa.jurusan === 'TKJ' ? 'Teknik Komputer & Jaringan' :
                                   identitasSiswa.jurusan === 'MM' ? 'Multimedia' :
                                   identitasSiswa.jurusan === 'AKL' ? 'Akuntansi' : identitasSiswa.jurusan;
                
                document.getElementById('infoKelas').textContent = `Kelas ${identitasSiswa.kelas}.${identitasSiswa.jurusan} • ${jurusanFull}`;
            }
        }

        function updateNavbarName() {
            const savedIdentitas = localStorage.getItem('identitas_siswa');
            if (savedIdentitas) {
                try {
                    const data = JSON.parse(savedIdentitas);
                    const navName = document.getElementById('userNavName');
                    const navNameMobile = document.getElementById('userNavNameMobile');
                    if (navName) navName.textContent = data.nama;
                    if (navNameMobile) navNameMobile.textContent = data.nama.split(' ')[0];
                } catch(e) { console.error(e); }
            }
        }

        // ============================================================
        // 3. SIMPAN ABSENSI (INTI)
        // ============================================================
function simpanAbsensi(status, metode, keterangan = '') {
    if (!identitasSiswa) {
        showNotification('⚠️ Data identitas tidak ditemukan!');
        return false;
    }
    
    const now = new Date();
    const dataAbsen = {
        nama: identitasSiswa.nama,
        kelas: `${identitasSiswa.kelas}.${identitasSiswa.jurusan}`,
        jurusan: identitasSiswa.jurusan,
        nis: String(10000 + Math.floor(Math.random() * 9000)),
        status: status,
        metode: metode,
        keterangan: keterangan || '',
        waktu: now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
        tanggal: now.toLocaleDateString('id-ID'),
        timestamp: now.getTime()
    };
    
    // ✅ Simpan absensi terakhir
    localStorage.setItem('siswa_absen', JSON.stringify(dataAbsen));
    
    // ✅ Tambahkan ke daftar_absen (kumpulan semua absensi)
    let daftarAbsen = [];
    try {
        daftarAbsen = JSON.parse(localStorage.getItem('daftar_absen') || '[]');
        if (!Array.isArray(daftarAbsen)) daftarAbsen = [];
    } catch (e) {
        daftarAbsen = [];
    }
    
    daftarAbsen.push(dataAbsen);
    localStorage.setItem('daftar_absen', JSON.stringify(daftarAbsen));
    
    console.log('✅ Data absensi tersimpan:', dataAbsen);
    console.log('📚 Total di daftar_absen:', daftarAbsen.length);
    return true;
}

        // ============================================================
        // 4. FUNGSI KAMERA
        // ============================================================
        function activateCamera() {
            if (!identitasSiswa) {
                showNotification('⚠️ Silakan isi identitas terlebih dahulu!');
                return;
            }
            const scannerArea = document.getElementById('scannerArea');
            scannerArea.classList.remove('hidden');
            scannerArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const statusText = document.getElementById('cameraStatus');
            statusText.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Klik "Mulai Kamera" untuk mengaktifkan kamera';
            statusText.className = 'text-slate-500 text-sm';
        }

        function startCamera() {
            const video = document.getElementById('videoPreview');
            const placeholder = document.getElementById('videoPlaceholder');
            const statusText = document.getElementById('cameraStatus');
            const startBtn = document.getElementById('startCameraBtn');
            const stopBtn = document.getElementById('stopCameraBtn');

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                statusText.innerHTML = '❌ Browser tidak mendukung akses kamera';
                statusText.className = 'text-red-500 text-sm';
                return;
            }

            placeholder.classList.add('hidden');
            video.classList.remove('hidden');

            navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment', width: { ideal: 640 }, height: { ideal: 480 } } 
            })
            .then(function(stream) {
                videoStream = stream;
                video.srcObject = stream;
                video.onloadedmetadata = function() {
                    video.play();
                    startQRDetection();
                };
                statusText.innerHTML = '✅ Kamera aktif - Silakan scan QR Code';
                statusText.className = 'text-emerald-500 text-sm';
                startBtn.disabled = true;
                stopBtn.disabled = false;
            })
            .catch(function(err) {
                console.error('Error akses kamera:', err);
                statusText.innerHTML = '❌ Gagal akses kamera: ' + err.message;
                statusText.className = 'text-red-500 text-sm';
                showNotification('⚠️ Gagal akses kamera, gunakan mode simulasi');
                startSimulationMode();
            });
        }

        function startQRDetection() {
            const statusText = document.getElementById('cameraStatus');
            if (scanInterval) clearInterval(scanInterval);
            isScanning = true;
            let scanAttempts = 0;

            scanInterval = setInterval(() => {
                if (!isScanning) return;
                scanAttempts++;
                
                if (scanAttempts > 5) {
                    isScanning = false;
                    clearInterval(scanInterval);
                    scanInterval = null;
                    
                    statusText.innerHTML = '✅ QR Code terdeteksi!';
                    statusText.className = 'text-emerald-600 text-sm font-semibold';
                    
                    const berhasil = simpanAbsensi('Hadir', 'Scan QR');
                    
                    if (berhasil) {
                        setTimeout(() => {
                            window.location.href = "{{ route('absen.verifikasi') }}";
                        }, 1000);
                    }
                }
            }, 200);
        }

        function stopCamera() {
            if (scanInterval) { clearInterval(scanInterval); scanInterval = null; }
            isScanning = false;
            if (videoStream) { videoStream.getTracks().forEach(track => track.stop()); videoStream = null; }
            const video = document.getElementById('videoPreview');
            const placeholder = document.getElementById('videoPlaceholder');
            const statusText = document.getElementById('cameraStatus');
            const startBtn = document.getElementById('startCameraBtn');
            const stopBtn = document.getElementById('stopCameraBtn');
            if (video) { video.srcObject = null; video.pause(); video.classList.add('hidden'); }
            if (placeholder) { placeholder.classList.remove('hidden'); }
            if (startBtn) startBtn.disabled = false;
            if (stopBtn) stopBtn.disabled = true;
            if (statusText) {
                statusText.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Kamera dimatikan';
                statusText.className = 'text-slate-500 text-sm';
            }
        }

        function deactivateCamera() {
            stopCamera();
            document.getElementById('scannerArea').classList.add('hidden');
            document.getElementById('videoPlaceholder').innerHTML = `
                <i class="fas fa-camera text-6xl text-indigo-300/50 mb-3"></i>
                <p class="text-sm text-slate-400">Kamera akan aktif saat tombol ditekan</p>
            `;
        }

        function startSimulationMode() {
            const statusText = document.getElementById('cameraStatus');
            const placeholder = document.getElementById('videoPlaceholder');
            placeholder.classList.remove('hidden');
            placeholder.innerHTML = `
                <div class="flex flex-col items-center justify-center h-full">
                    <div class="relative">
                        <div class="w-32 h-32 border-4 border-indigo-500 rounded-lg animate-pulse flex items-center justify-center">
                            <i class="fas fa-qrcode text-5xl text-indigo-400"></i>
                        </div>
                        <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-24 h-1 bg-indigo-500 rounded-full scan-animation"></div>
                    </div>
                    <p class="text-sm text-indigo-600 font-medium mt-4">🔍 Mode Simulasi - Memindai...</p>
                </div>
            `;
            statusText.innerHTML = '📷 Mode simulasi - Scanning...';
            statusText.className = 'text-amber-500 text-sm';
            let attempts = 0;
            const simInterval = setInterval(() => {
                attempts++;
                if (attempts > 5) {
                    clearInterval(simInterval);
                    statusText.innerHTML = '✅ QR Code terdeteksi (Simulasi)!';
                    statusText.className = 'text-emerald-600 text-sm font-semibold';
                    
                    const berhasil = simpanAbsensi('Hadir', 'Scan QR (Simulasi)');
                    if (berhasil) {
                        setTimeout(() => { 
                            window.location.href = "{{ route('absen.verifikasi') }}"; 
                        }, 1000);
                    }
                }
            }, 600);
        }

        // ============================================================
        // 5. FUNGSI ID UNIK
        // ============================================================
        document.getElementById('submitIdUnik')?.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (!identitasSiswa) {
                showNotification('⚠️ Silakan isi identitas terlebih dahulu!');
                return;
            }
            
            const input = document.getElementById('idUnikInput');
            const id = input?.value.trim();
            
            if (!id) {
                showNotification('⚠️ Silakan masukkan ID Unik terlebih dahulu.');
                return;
            }
            
            showNotification(`✅ ID Unik "${id}" terdaftar!`);
            
            const berhasil = simpanAbsensi('Hadir', 'ID Unik');
            
            if (berhasil) {
                setTimeout(() => { 
                    window.location.href = "{{ route('absen.verifikasi') }}"; 
                }, 1000);
            }
        });

        document.getElementById('idUnikInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { document.getElementById('submitIdUnik').click(); }
        });

        // ============================================================
        // 6. FUNGSI IZIN / SAKIT
        // ============================================================
        function openIzinSakitModal() {
            if (!identitasSiswa) {
                showNotification('⚠️ Silakan isi identitas terlebih dahulu!');
                return;
            }
            
            document.getElementById('izinSakitModal').classList.remove('hidden');
            document.getElementById('modalIzinNama').textContent = identitasSiswa.nama;
            document.getElementById('modalIzinKelas').textContent = `Kelas ${identitasSiswa.kelas}.${identitasSiswa.jurusan}`;
            document.getElementById('izinJenis').value = 'Izin';
            document.getElementById('izinKeterangan').value = '';
            document.getElementById('izinFile').value = '';
        }

        function closeIzinSakitModal() {
            document.getElementById('izinSakitModal').classList.add('hidden');
        }

        function submitIzinSakit() {
            if (!identitasSiswa) {
                showNotification('⚠️ Data identitas tidak ditemukan!');
                return;
            }
            
            const jenis = document.getElementById('izinJenis').value;
            const keterangan = document.getElementById('izinKeterangan').value.trim();

            if (!keterangan) { 
                showNotification('⚠️ Keterangan alasan harus diisi!'); 
                return; 
            }

            showNotification(`📤 Mengirim data ${jenis} untuk ${identitasSiswa.nama}...`);

            setTimeout(() => {
                const berhasil = simpanAbsensi(jenis, 'Izin/Sakit', keterangan);
                
                if (berhasil) {
                    showNotification(`✅ ${jenis} ${identitasSiswa.nama} berhasil diajukan!`);
                    closeIzinSakitModal();
                    
                    setTimeout(() => {
                        window.location.href = "{{ route('absen.verifikasi') }}";
                    }, 1000);
                }
            }, 1500);
        }

        // ============================================================
        // 7. NOTIFICATION
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
        // 8. INIT
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📷 Halaman Absensi QR siap!');
            cekIdentitas();
        });
    </script>

</body>
</html>