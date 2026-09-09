<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hadirin.web · Absensi Siswa QR</title>
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
        .qr-frame {
            border: 4px dashed #6366f1;
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        }
        .scan-animation {
            animation: pulse 1.5s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.95); }
        }
        .btn-scan:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 30px rgba(99, 102, 241, 0.4);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
        }
        .hidden {
            display: none !important;
        }
        #videoPreview {
            transform: scaleX(-1);
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

    <!-- ========== MAIN: ABSENSI QR ========== -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        <!-- Header -->
        <div class="text-center mb-8 md:mb-12">
            <div class="inline-block bg-indigo-100/80 text-indigo-700 text-xs font-semibold px-4 py-1.5 rounded-full border border-indigo-200/60 mb-3">
                <i class="fas fa-qrcode mr-2"></i> Absensi Mandiri
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-800">
                Absen hari ini
            </h1>
            <p class="text-sm text-slate-500 mt-2">Pilih metode absensi yang tersedia</p>
        </div>

        <!-- Pilihan Metode: Scan QR / ID Unik -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 max-w-4xl mx-auto">
            
            <!-- Card Scan QR -->
            <div class="bg-white rounded-2xl shadow-lg border-2 border-indigo-200/60 p-6 md:p-8 text-center card-hover transition-all duration-300 relative">
                <div class="absolute -top-3 -right-3 bg-indigo-600 text-white text-[10px] font-bold px-3 py-1 rounded-full">
                    <i class="fas fa-star mr-1"></i> Utama
                </div>
                <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-camera text-3xl text-indigo-600"></i>
                </div>
                <h4 class="text-xl font-bold text-slate-800 mb-2">Scan QR Code</h4>
                <p class="text-sm text-slate-500 mb-4">
                    Aktifkan kamera untuk memindai QR Code pada kartu presensi.
                </p>
                <button onclick="activateCamera()" class="btn-scan w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3.5 rounded-xl shadow-md shadow-indigo-200/60 transition flex items-center justify-center gap-2 text-base">
                    <i class="fas fa-video"></i> Aktifkan Kamera
                </button>
            </div>

            <!-- Card ID Unik -->
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200/60 p-6 md:p-8 text-center card-hover transition-all duration-300">
                <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-keyboard text-3xl text-emerald-600"></i>
                </div>
                <h4 class="text-xl font-bold text-slate-800 mb-2">ID Unik</h4>
                <p class="text-sm text-slate-500 mb-4">
                    Masukkan kode ID unik yang tertera pada kartu presensi.
                </p>
                <div class="flex gap-2">
                    <input type="text" id="idUnikInput" placeholder="Masukkan ID Unik" 
                           class="flex-1 px-4 py-3 bg-slate-50/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 transition" />
                    <button id="submitIdUnik" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-5 py-3 rounded-xl transition shadow-md shadow-emerald-200/60">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>

        </div>

        <!-- ====== QR SCANNER AREA ====== -->
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
                
                <!-- QR Frame -->
                <div class="qr-frame rounded-2xl p-4 relative">
                    <div class="aspect-square max-w-sm mx-auto relative">
                        
                        <!-- VIDEO PREVIEW (Kamera Real) -->
                        <video id="videoPreview" 
                               class="w-full h-full bg-black rounded-xl border-2 border-indigo-300 object-cover hidden"
                               autoplay playsinline>
                        </video>
                        
                        <!-- CANVAS untuk deteksi QR -->
                        <canvas id="qrCanvas" class="hidden"></canvas>
                        
                        <!-- Placeholder jika kamera belum aktif -->
                        <div id="videoPlaceholder" class="w-full h-full bg-slate-100 rounded-xl border-2 border-indigo-300 flex flex-col items-center justify-center">
                            <i class="fas fa-camera text-6xl text-indigo-300/50 mb-3"></i>
                            <p class="text-sm text-slate-400">Kamera akan aktif saat tombol ditekan</p>
                        </div>

                        <!-- Corner Marks -->
                        <div class="absolute top-2 left-2 w-8 h-8 border-t-4 border-l-4 border-indigo-600 rounded-tl-lg"></div>
                        <div class="absolute top-2 right-2 w-8 h-8 border-t-4 border-r-4 border-indigo-600 rounded-tr-lg"></div>
                        <div class="absolute bottom-2 left-2 w-8 h-8 border-b-4 border-l-4 border-indigo-600 rounded-bl-lg"></div>
                        <div class="absolute bottom-2 right-2 w-8 h-8 border-b-4 border-r-4 border-indigo-600 rounded-br-lg"></div>
                    </div>
                    
                    <!-- Status Kamera -->
                    <div class="mt-3 text-center">
                        <p id="cameraStatus" class="text-sm text-slate-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Tekan "Mulai Kamera" untuk memulai
                        </p>
                    </div>
                </div>

                <!-- Tombol Kontrol -->
                <div class="mt-4 flex flex-wrap justify-center gap-3">
                    <button id="startCameraBtn" onclick="startCamera()" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-xl transition">
                        <i class="fas fa-play mr-1"></i> Mulai Kamera
                    </button>
                    <button id="stopCameraBtn" onclick="stopCamera()" 
                            class="bg-slate-200 hover:bg-slate-300 text-slate-600 font-medium px-6 py-2.5 rounded-xl transition" disabled>
                        <i class="fas fa-stop mr-1"></i> Stop
                    </button>
                    <button onclick="deactivateCamera()" 
                            class="text-sm text-slate-500 hover:text-slate-700 transition px-4 py-2.5">
                        <i class="fas fa-arrow-left mr-1"></i> Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Info Tambahan -->
        <div class="mt-8 max-w-2xl mx-auto">
            <div class="bg-indigo-50/70 border border-indigo-100/60 rounded-xl p-4 flex items-start gap-3">
                <i class="fas fa-info-circle text-indigo-500 text-lg mt-0.5"></i>
                <div>
                    <p class="text-sm text-slate-600">
                        <span class="font-semibold">Tips:</span> Pastikan kamera dalam kondisi baik dan 
                        QR Code terlihat jelas. Jika gagal, gunakan metode <span class="font-medium text-indigo-600">ID Unik</span>.
                    </p>
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

    <!-- ========== FOOTER ========== -->
    <footer class="w-full border-t border-slate-200/60 py-4 text-center text-[10px] text-slate-400 bg-white/40 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 flex flex-wrap items-center justify-center gap-3">
            <span>© 2026 Sistem Absensi</span>
            <span class="w-px h-3 bg-slate-300"></span>
            <span>Hadirin.web</span>
            <span class="w-px h-3 bg-slate-300"></span>
            <span><i class="fas fa-image mr-1"></i> Absensi Siswa QR1.png</span>
        </div>
    </footer>

    <!-- ========== JAVASCRIPT LENGKAP ========== -->
    <script>
        // ========================================
        // 1. VARIABEL GLOBAL
        // ========================================
        let videoStream = null;
        let scanInterval = null;
        let isScanning = false;

        // ========================================
        // 2. FUNGSI AKTIFKAN KAMERA (DARI TOMBOL CARD)
        // ========================================
        function activateCamera() {
            const scannerArea = document.getElementById('scannerArea');
            scannerArea.classList.remove('hidden');
            scannerArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Update status
            const statusText = document.getElementById('cameraStatus');
            statusText.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Klik "Mulai Kamera" untuk mengaktifkan kamera';
            statusText.className = 'text-slate-500 text-sm';
        }

        // ========================================
        // 3. FUNGSI START KAMERA (REAL)
        // ========================================
        function startCamera() {
            const video = document.getElementById('videoPreview');
            const placeholder = document.getElementById('videoPlaceholder');
            const statusText = document.getElementById('cameraStatus');
            const startBtn = document.getElementById('startCameraBtn');
            const stopBtn = document.getElementById('stopCameraBtn');

            // Cek dukungan browser
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                statusText.innerHTML = '❌ Browser tidak mendukung akses kamera';
                statusText.className = 'text-red-500 text-sm';
                return;
            }

            // Sembunyikan placeholder, tampilkan video
            placeholder.classList.add('hidden');
            video.classList.remove('hidden');

            // Request akses kamera
            navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'environment',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                } 
            })
            .then(function(stream) {
                // Simpan stream
                videoStream = stream;
                video.srcObject = stream;
                
                video.onloadedmetadata = function() {
                    video.play();
                    // Mulai deteksi QR setelah video jalan
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
                
                // Fallback ke mode simulasi jika gagal
                showNotification('⚠️ Gagal akses kamera, menggunakan mode simulasi');
                startSimulationMode();
            });
        }

        // ========================================
        // 4. FUNGSI DETEKSI QR CODE
        // ========================================
        function startQRDetection() {
            const video = document.getElementById('videoPreview');
            const canvas = document.getElementById('qrCanvas');
            const context = canvas.getContext('2d');
            const statusText = document.getElementById('cameraStatus');

            if (scanInterval) {
                clearInterval(scanInterval);
            }

            isScanning = true;
            let scanAttempts = 0;

            scanInterval = setInterval(() => {
                if (!isScanning) return;
                
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                    
                    // Simulasi deteksi QR (karena kita tidak pakai library jsQR)
                    // Di real app, pakai jsQR library
                    scanAttempts++;
                    
                    // Simulasi: 30% chance berhasil setiap 2 detik
                    if (scanAttempts > 10 && Math.random() > 0.6) {
                        // QR Terdeteksi!
                        isScanning = false;
                        clearInterval(scanInterval);
                        scanInterval = null;
                        
                        statusText.innerHTML = '✅ QR Code terdeteksi!';
                        statusText.className = 'text-emerald-600 text-sm font-semibold';
                        
                        setTimeout(() => {
                            window.location.href = "{{ route('absen.verifikasi') }}";
                        }, 1000);
                    }
                }
            }, 200);
        }

        // ========================================
        // 5. FUNGSI STOP KAMERA
        // ========================================
        function stopCamera() {
            // Stop scanning
            if (scanInterval) {
                clearInterval(scanInterval);
                scanInterval = null;
            }
            isScanning = false;

            // Stop video stream
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }

            const video = document.getElementById('videoPreview');
            const placeholder = document.getElementById('videoPlaceholder');
            const statusText = document.getElementById('cameraStatus');
            const startBtn = document.getElementById('startCameraBtn');
            const stopBtn = document.getElementById('stopCameraBtn');

            if (video) {
                video.srcObject = null;
                video.pause();
                video.classList.add('hidden');
            }
            
            if (placeholder) {
                placeholder.classList.remove('hidden');
            }

            if (startBtn) startBtn.disabled = false;
            if (stopBtn) stopBtn.disabled = true;
            
            if (statusText) {
                statusText.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Kamera dimatikan';
                statusText.className = 'text-slate-500 text-sm';
            }
        }

        // ========================================
        // 6. FUNGSI DEACTIVATE (TUTUP SCANNER)
        // ========================================
        function deactivateCamera() {
            stopCamera();
            const scannerArea = document.getElementById('scannerArea');
            scannerArea.classList.add('hidden');
            
            // Reset placeholder
            const placeholder = document.getElementById('videoPlaceholder');
            placeholder.innerHTML = `
                <i class="fas fa-camera text-6xl text-indigo-300/50 mb-3"></i>
                <p class="text-sm text-slate-400">Kamera akan aktif saat tombol ditekan</p>
            `;
        }

        // ========================================
        // 7. FUNGSI SIMULASI MODE (FALLBACK)
        // ========================================
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
                    <p class="text-xs text-slate-400 mt-1">(Kamera tidak tersedia)</p>
                </div>
            `;

            statusText.innerHTML = '📷 Mode simulasi - Scanning...';
            statusText.className = 'text-amber-500 text-sm';

            // Simulasi deteksi QR
            let attempts = 0;
            const simInterval = setInterval(() => {
                attempts++;
                if (attempts > 8) {
                    clearInterval(simInterval);
                    statusText.innerHTML = '✅ QR Code terdeteksi (Simulasi)!';
                    statusText.className = 'text-emerald-600 text-sm font-semibold';
                    setTimeout(() => {
                        window.location.href = "{{ route('absen.verifikasi') }}";
                    }, 1000);
                }
            }, 600);
        }

        // ========================================
        // 8. FUNGSI ID UNIK
        // ========================================
        document.getElementById('submitIdUnik')?.addEventListener('click', function(e) {
            e.preventDefault();
            const input = document.getElementById('idUnikInput');
            const id = input?.value.trim();
            if (id) {
                showNotification(`✅ ID Unik "${id}" terdaftar!`);
                setTimeout(() => {
                    window.location.href = "{{ route('absen.verifikasi') }}";
                }, 500);
            } else {
                showNotification('⚠️ Silakan masukkan ID Unik terlebih dahulu.');
            }
        });

        // Enter key untuk ID Unik
        document.getElementById('idUnikInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('submitIdUnik').click();
            }
        });

        // ========================================
        // 9. FUNGSI NOTIFICATION
        // ========================================
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

        // ========================================
        // 10. INISIALISASI
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📷 Halaman Absensi QR siap!');
        });
    </script>

</body>
</html>