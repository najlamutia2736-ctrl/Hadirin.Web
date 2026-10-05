@extends('layouts.absensi')

@section('title', 'Scan QR Code · Hadirin.web')

@php
    // Route `absensi.scan-qr` hanya mengembalikan view tanpa data tambahan,
    // jadi identitas untuk topbar diambil dari relasi user yang sedang login.
    $user = auth()->user();
    $siswa = $user?->siswa;
@endphp

@section('konten')
    <div class="min-h-screen">
        {{-- Topbar --}}
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6">
                <a href="{{ route('beranda') }}" class="text-xl font-bold tracking-tight text-indigo-700">
                    Hadirin.<span class="text-slate-700">web</span>
                </a>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-slate-800">{{ $user?->name }}</p>
                        <p class="text-xs text-slate-500">{{ $siswa?->kelas ?? 'Absensi Siswa' }}</p>
                    </div>
                    <span
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700">
                        {{ strtoupper(substr($user?->name ?? 'S', 0, 1)) }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6">
            {{-- Judul --}}
            <section>
                <a href="{{ route('absensi.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition-colors hover:text-indigo-600">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Kembali ke pilihan absen
                </a>
                <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-800 sm:text-3xl">
                    Scan QR Code
                </h1>
                <p class="mt-1 text-slate-600">
                    Arahkan kamera ke QR code yang terpasang di depan kelas.
                </p>
            </section>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {{-- Area kamera --}}
                <section class="lg:col-span-2">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-900 shadow-sm">
                        {{-- Rasio 4:3 supaya video tidak gepeng di HP --}}
                        <div class="relative aspect-[4/3] w-full bg-slate-900" id="wadahKamera">
                            <video id="kamera" playsinline autoplay muted
                                class="h-full w-full object-cover opacity-0 transition-opacity duration-300"></video>

                            {{-- Kanvas disembunyikan, hanya dipakai untuk membaca frame --}}
                            <canvas id="kanvas" class="hidden"></canvas>

                            {{-- Bingkai bidik: empat sudut dan garis scan --}}
                            <div id="bingkai" class="pointer-events-none absolute inset-0 hidden">
                                <div class="absolute inset-[12%]">
                                    <span
                                        class="absolute left-0 top-0 h-8 w-8 rounded-tl-lg border-l-4 border-t-4 border-white"></span>
                                    <span
                                        class="absolute right-0 top-0 h-8 w-8 rounded-tr-lg border-r-4 border-t-4 border-white"></span>
                                    <span
                                        class="absolute bottom-0 left-0 h-8 w-8 rounded-bl-lg border-b-4 border-l-4 border-white"></span>
                                    <span
                                        class="absolute bottom-0 right-0 h-8 w-8 rounded-br-lg border-b-4 border-r-4 border-white"></span>
                                    <span
                                        class="garis-scan absolute inset-x-2 top-2 h-0.5 rounded-full bg-indigo-400"></span>
                                </div>
                            </div>

                            {{-- Pesan status, menutupi layar selama kamera belum jalan --}}
                            <div id="statusKamera"
                                class="absolute inset-0 flex flex-col items-center justify-center gap-3 px-6 text-center">
                                <span id="ikonStatus"
                                    class="grid h-14 w-14 place-items-center rounded-full bg-white/10 text-2xl text-white">
                                    <i class="fas fa-camera"></i>
                                </span>
                                <p id="judulStatus" class="text-base font-semibold text-white">
                                    Meminta izin kamera
                                </p>
                                <p id="detailStatus" class="max-w-sm text-sm text-white/70">
                                    Browser akan meminta izin akses kamera. Pilih &ldquo;Izinkan&rdquo; agar pemindaian
                                    bisa berjalan.
                                </p>
                                <button type="button" id="tombolMulai"
                                    class="mt-1 inline-flex items-center gap-2 rounded-xl bg-white/15 px-5 py-2.5 text-sm font-semibold text-white ring-1 ring-white/25 transition-colors hover:bg-white/25">
                                    <i class="fas fa-play text-xs"></i>
                                    Aktifkan Kamera
                                </button>
                            </div>
                        </div>

                        {{-- Kontrol kamera --}}
                        <div class="flex flex-wrap items-center gap-2 border-t border-white/10 bg-slate-800 px-4 py-3">
                            <button type="button" id="tombolAktif"
                                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition-colors hover:bg-indigo-700">
                                <i class="fas fa-power-on text-xs"></i>
                                <span id="labelAktif">Matikan</span>
                            </button>

                            <button type="button" id="tombolGanti"
                                class="hidden items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-xs font-semibold text-white transition-colors hover:bg-white/20">
                                <i class="fas fa-repeat text-xs"></i>
                                Ganti Kamera
                            </button>

                            <button type="button" id="tombolSenter"
                                class="hidden items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-xs font-semibold text-white transition-colors hover:bg-white/20">
                                <i class="fas fa-flashlight-on text-xs"></i>
                                Senter
                            </button>

                            <span id="labelKamera" class="ml-auto text-[11px] text-white/50"></span>
                        </div>
                    </div>
                </section>

                {{-- Panel samping: hasil scan, cara lain, dan tips --}}
                <section class="space-y-4">
                    {{-- Hasil pemindaian --}}
                    <div id="panelHasil" class="kartu-hasil rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                                <i class="fas fa-qrcode"></i>
                            </span>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-800">Hasil Scan</h2>
                                <p class="text-xs text-slate-500" id="keteranganHasil">
                                    Belum ada QR code terbaca.
                                </p>
                            </div>
                        </div>

                        <div id="isiHasil"
                            class="mt-4 hidden rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 text-center">
                            <p class="text-xs uppercase tracking-wide text-slate-400">Kode yang terbaca</p>
                            <p id="kodeTerdeteksi" class="mt-1 font-mono text-lg font-bold break-all text-slate-800"></p>
                        </div>

                        <div id="aksiSetelahBenar" class="mt-4 hidden space-y-2">
                            <a href="{{ route('absensi.index') }}"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-indigo-700">
                                Lanjut isi absensi
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                            <button type="button" id="tombolScanLagi"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-200">
                                <i class="fas fa-rotate text-xs"></i>
                                Scan ulang
                            </button>
                        </div>
                    </div>

                    {{-- Cara lain kalau kamera tidak bisa dipakai --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-sm font-semibold text-slate-800">Kamera tidak tersedia?</h2>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500">
                            Izinkan akses kamera di pengaturan browser, atau pakai cara absen lewat ID unik.
                        </p>

                        <label for="kodeManual" class="mt-4 block text-xs font-medium text-slate-600">
                            Ketik kode QR secara manual
                        </label>
                        <input type="text" id="kodeManual" autocomplete="off" placeholder="Contoh: HADIRIN-PAGI-2026"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200">
                        <button type="button" id="tombolKodeManual"
                            class="mt-2.5 w-full rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-900">
                            Pakai kode ini
                        </button>

                        <a href="{{ route('absensi.id-unik') }}"
                            class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50">
                            <i class="fas fa-keyboard text-xs"></i>
                            Absen lewat ID Unik
                        </a>
                    </div>

                    {{-- Tips --}}
                    <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-5">
                        <h2 class="text-sm font-semibold text-indigo-900">Tips memindai</h2>
                        <ul class="mt-2 space-y-1.5 text-xs leading-relaxed text-indigo-800/80">
                            <li class="flex gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-[10px] text-indigo-500"></i>
                                Pegang HP/Kartu setinggi dada, jarak 15 sampai 25 cm.
                            </li>
                            <li class="flex gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-[10px] text-indigo-500"></i>
                                Pastikan QR code utuh dan tidak terkena pantulan.
                            </li>
                            <li class="flex gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-[10px] text-indigo-500"></i>
                                Nyalakan senter kalau ruangan gelap.
                            </li>
                        </ul>
                    </div>
                </section>
            </div>

            <noscript>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    Halaman ini butuh JavaScript untuk menyalakan kamera. Aktifkan JavaScript di browsermu, atau
                    langsung buka halaman
                    <a href="{{ route('absensi.id-unik') }}" class="font-semibold underline">Absen lewat ID Unik</a>.
                </div>
            </noscript>
        </main>
    </div>
@endsection

@push('scripts')
    {{-- jsQR membaca QR code langsung dari frame video, tanpa layanan pihak ketiga --}}
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

    <style>
        /* Garis scan yang bergerak naik-turun di dalam bingkai bidik. */
        @keyframes pindai-naik-turun {

            0%,
            100% {
                top: 0.5rem;
            }

            50% {
                top: calc(100% - 0.5rem);
            }
        }

        .garis-scan {
            animation: pindai-naik-turun 2.4s ease-in-out infinite;
            box-shadow: 0 0 12px 3px rgba(129, 140, 248, 0.65);
        }

        /* Pengguna yang minta pengurangan animasi: garisnya diam saja. */
        @media (prefers-reduced-motion: reduce) {
            .garis-scan {
                animation: none;
            }
        }

        /* Kartu hasil diberi penanda hijau begitu kode terbaca. */
        .kartu-hasil.terbaca {
            border-color: rgb(209, 250, 229);
            box-shadow: 0 0 0 2px rgb(209, 250, 229);
        }
    </style>

    <script>
        // ============================================================
        // SCAN QR CODE
        // ============================================================
        // Kamera dibuka lewat getUserMedia, lalu setiap frame-nya digambar
        // ke canvas dan dibaca jsQR. Pemindaian berhenti sekali kode
        // terdeteksi supaya satu QR tidak terbaca berulang, dan seluruh
        // track kamera dimatikan saat halaman ditinggalkan supaya lampu
        // kamera tidak menyala terus.

        const video = document.getElementById('kamera');
        const kanvas = document.getElementById('kanvas');
        const bingkai = document.getElementById('bingkai');
        const statusKamera = document.getElementById('statusKamera');
        const ikonStatus = document.getElementById('ikonStatus');
        const judulStatus = document.getElementById('judulStatus');
        const detailStatus = document.getElementById('detailStatus');
        const tombolMulai = document.getElementById('tombolMulai');
        const tombolAktif = document.getElementById('tombolAktif');
        const labelAktif = document.getElementById('labelAktif');
        const tombolGanti = document.getElementById('tombolGanti');
        const tombolSenter = document.getElementById('tombolSenter');
        const labelKamera = document.getElementById('labelKamera');
        const panelHasil = document.getElementById('panelHasil');
        const keteranganHasil = document.getElementById('keteranganHasil');
        const isiHasil = document.getElementById('isiHasil');
        const kodeTerdeteksi = document.getElementById('kodeTerdeteksi');
        const aksiSetelahBenar = document.getElementById('aksiSetelahBenar');
        const tombolScanLagi = document.getElementById('tombolScanLagi');
        const kodeManual = document.getElementById('kodeManual');
        const tombolKodeManual = document.getElementById('tombolKodeManual');

        // willReadFrequently dipakai karena setiap frame-reading memanggil
        // getImageData, yang tanpa flag ini akan memicu upload ulang texture.
        const konteks = kanvas.getContext('2d', {
            willReadFrequently: true
        });

        let stream = null;
        let idFrame = 0;
        let kameraTersedia = [];
        let indeksKamera = 0;
        let pakaiKameraBelakang = true;
        let kirimOtomatis = true;

        // ============================================================
        // PESAN ERROR
        // ============================================================

        // Setiap error dari getUserMedia punya nama sendiri, jadi dibedakan
        // supaya siswa tahu harus memperbaiki apa, bukan cuma "gagal".
        const PESAN_KAMERA = {
            NotAllowedError: {
                judul: 'Izin kamera ditolak',
                detail: 'Aktifkan izin kamera untuk situs ini di pengaturan browser, lalu tekan tombol di bawah.',
            },
            NotFoundError: {
                judul: 'Kamera tidak ditemukan',
                detail: 'Tidak ada kamera yang bisa dipakai di perangkat ini. Coba absen lewat ID Unik.',
            },
            NotReadableError: {
                judul: 'Kamera sedang dipakai aplikasi lain',
                detail: 'Tutup aplikasi lain yang sedang memakai kamera, lalu coba lagi.',
            },
            OverconstrainedError: {
                judul: 'Kamera tidak mendukung',
                detail: 'Kamera yang diminta tidak tersedia. Coba ganti kamera atau gunakan ID Unik.',
            },
            SecurityError: {
                judul: 'Kamera diblokir browser',
                detail: 'Browser hanya mengizinkan kamera pada alamat https:// atau localhost.',
            },
        };

        function tampilkanStatus(ikon, judul, detail, tampilkanTombol = false) {
            ikonStatus.innerHTML = '<i class="fas ' + ikon + '"></i>';
            judulStatus.textContent = judul;
            detailStatus.textContent = detail;
            statusKamera.classList.remove('hidden');
            tombolMulai.classList.toggle('hidden', !tampilkanTombol);
        }

        function sembunyikanStatus() {
            statusKamera.classList.add('hidden');
        }

        function tampilkanAtauSembunyikan(elemen, tampil) {
            elemen.classList.toggle('hidden', !tampil);
            elemen.classList.toggle('flex', tampil);
        }

        // ============================================================
        // MENYALAKAN DAN MATIKAN KAMERA
        // ============================================================

        async function nyalakanKamera() {
            if (stream !== null) {
                return;
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                tampilkanStatus(
                    'fa-triangle-exclamation',
                    'Browser tidak mendukung kamera',
                    'Gunakan Chrome, Edge, Firefox, atau Safari versi terbaru. Sementara itu kamu bisa absen lewat ID Unik.',
                    true
                );
                return;
            }

            // getUserMedia hanya boleh dipakai di konteks aman: https:// atau
            // localhost. Kalau tidak, API-nya memang tidak tersedia sama sekali.
            if (!window.isSecureContext) {
                tampilkanStatus(
                    'fa-lock',
                    'Alamat ini belum aman',
                    'Kamera hanya boleh aktif di alamat https:// atau http://localhost.',
                    true
                );
                return;
            }

            if (typeof window.jsQR === 'undefined') {
                tampilkanStatus(
                    'fa-triangle-exclamation',
                    'Pustaka pembaca QR gagal dimuat',
                    'Periksa koneksi internetmu lalu muat ulang halaman ini.',
                    true
                );
                return;
            }

            tampilkanStatus('fa-spinner', 'Menyiapkan kamera', 'Mohon tunggu sebentar.');
            tombolMulai.classList.add('hidden');

            const batasan = {
                audio: false,
                video: pakaiKameraBelakang ? {
                    facingMode: {
                        ideal: 'environment'
                    }
                } : {
                    facingMode: {
                        ideal: 'user'
                    }
                },
            };

            try {
                stream = await navigator.mediaDevices.getUserMedia(batasan);
            } catch (error) {
                stream = null;
                const pesan = PESAN_KAMERA[error.name] ?? {
                    judul: 'Kamera gagal dinyalakan',
                    detail: error.message || 'Terjadi kesalahan yang tidak diketahui.',
                };

                tampilkanStatus('fa-camera-slash', pesan.judul, pesan.detail, true);
                return;
            }

            video.srcObject = stream;

            try {
                await video.play();
            } catch (error) {
                // Sebagian browser menolak autoplay walau atributnya sudah
                // ada. Video tetap berjalan begitu pengguna menekan tombol.
            }

            const track = stream.getVideoTracks()[0] ?? null;
            const pengaturan = track?.getSettings?.() ?? {};

            labelKamera.textContent =
                pengaturan.label || (pakaiKameraBelakang ? 'Kamera belakang' : 'Kamera depan');

            sembunyikanStatus();
            video.classList.add('opacity-100');
            video.classList.remove('opacity-0');
            bingkai.classList.remove('hidden');
            tampilkanAtauSembunyikan(tombolGanti, true);
            perbaruiTombolSenter(track);
            perbaruiLabelAktif();

            // Daftar nama kamera baru boleh dibaca setelah izin diberikan.
            await muatDaftarKamera();

            kirimOtomatis = true;
            mulaiMembacaFrame();
        }

        function matikanKamera() {
            if (idFrame !== 0) {
                cancelAnimationFrame(idFrame);
                idFrame = 0;
            }

            if (stream !== null) {
                stream.getTracks().forEach((track) => track.stop());
                stream = null;
            }

            video.srcObject = null;
            video.classList.add('opacity-0');
            video.classList.remove('opacity-100');

            bingkai.classList.add('hidden');
            tampilkanAtauSembunyikan(tombolGanti, false);
            tampilkanAtauSembunyikan(tombolSenter, false);
            tombolSenter.classList.remove('opacity-40', 'cursor-not-allowed');
            labelKamera.textContent = '';

            perbaruiLabelAktif();
        }

        async function muatDaftarKamera() {
            try {
                const perangkat = await navigator.mediaDevices.enumerateDevices();
                kameraTersedia = perangkat.filter((perangkat) => perangkat.kind === 'videoinput');
            } catch (error) {
                kameraTersedia = [];
            }
        }

        // ============================================================
        // PEMINDAIAN
        // ============================================================

        function mulaiMembacaFrame() {
            const tick = () => {
                idFrame = requestAnimationFrame(tick);

                if (video.readyState !== video.HAVE_ENOUGH_DATA) {
                    return;
                }

                // Frame dibaca pada lebar maksimum 640 piksel supaya tidak
                // berat di HP kelas yang kentil. Kode QR tetap terbaca di sini.
                const lebarBaca = Math.min(video.videoWidth, 640);
                const tinggiBaca = Math.round(video.videoHeight * (lebarBaca / video.videoWidth));

                kanvas.width = lebarBaca;
                kanvas.height = tinggiBaca;

                konteks.drawImage(video, 0, 0, lebarBaca, tinggiBaca);

                const gambar = konteks.getImageData(0, 0, lebarBaca, tinggiBaca);
                const hasil = window.jsQR(gambar.data, lebarBaca, tinggiBaca, {
                    inversionAttempts: 'dontInvert',
                });

                if (hasil !== null && kirimOtomatis) {
                    kirimOtomatis = false;
                    terimaKode(hasil.data);
                }
            };

            idFrame = requestAnimationFrame(tick);
        }

        // ============================================================
        // HASIL SCAN
        // ============================================================

        function terimaKode(nilai) {
            const teks = String(nilai).trim();

            if (teks === '') {
                return;
            }

            // Getar dan bunyi pendek supaya siswa tahu tanpa harus menatap layar.
            if (navigator.vibrate) {
                navigator.vibrate(120);
            }
            bunyiBerhasil();

            kodeTerdeteksi.textContent = teks;
            keteranganHasil.textContent = 'QR code berhasil dibaca.';
            isiHasil.classList.remove('hidden');
            aksiSetelahBenar.classList.remove('hidden');
            panelHasil.classList.add('terbaca');

            // Kamera dimatikan supaya merekam wajah siswa tidak berlanjut.
            matikanKamera();
            tampilkanStatus('fa-circle-check', 'QR code terbaca', 'Kamera sudah dimatikan otomatis.');
        }

        function resetHasil() {
            kirimOtomatis = true;
            kodeTerdeteksi.textContent = '';
            keteranganHasil.textContent = 'Belum ada QR code terbaca.';
            isiHasil.classList.add('hidden');
            aksiSetelahBenar.classList.add('hidden');
            panelHasil.classList.remove('terbaca');
        }

        // Bunyinya dibuat dari Web Audio API supaya tidak perlu file audio.
        function bunyiBerhasil() {
            try {
                const AudioContextPeta = window.AudioContext || window.webkitAudioContext;

                if (!AudioContextPeta) {
                    return;
                }

                const audio = new AudioContextPeta();
                const oscillator = audio.createOscillator();
                const gain = audio.createGain();

                oscillator.connect(gain);
                gain.connect(audio.destination);
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(880, audio.currentTime);
                gain.gain.setValueAtTime(0.08, audio.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.0001, audio.currentTime + 0.25);
                oscillator.start();
                oscillator.stop(audio.currentTime + 0.25);
            } catch (error) {
                // Bunyi hanya bonus, jadi kegagalan di sini diabaikan diam-diam.
            }
        }

        // ============================================================
        // KONTROL
        // ============================================================

        function perbaruiLabelAktif() {
            const hidup = stream !== null;

            labelAktif.textContent = hidup ? 'Matikan' : 'Aktifkan';
            tombolAktif.querySelector('i').className = hidup ?
                'fas fa-power-off text-xs' :
                'fas fa-power-on text-xs';
        }

        // Senter tidak ada di semua perangkat, jadi tombolnya baru muncul
        // kalau track-nya memang melaporkan kemampuan itu.
        function perbaruiTombolSenter(track) {
            const punyaSenter = track?.getCapabilities?.().torch === true;

            tampilkanAtauSembunyikan(tombolSenter, punyaSenter);
        }

        async function gantiKamera() {
            if (stream === null) {
                return;
            }

            if (kameraTersedia.length > 1) {
                indeksKamera = (indeksKamera + 1) % kameraTersedia.length;
                pakaiKameraBelakang = indeksKamera % 2 === 0;
            } else {
                pakaiKameraBelakang = !pakaiKameraBelakang;
            }

            matikanKamera();
            await nyalakanKamera();
        }

        async function toggleSenter() {
            const track = stream?.getVideoTracks?.()[0];

            if (!track || track.getCapabilities().torch !== true) {
                return;
            }

            try {
                await track.applyConstraints({
                    advanced: [{
                        torch: track.getSettings().torch !== true
                    }],
                });
            } catch (error) {
                // Sebagian perangkat hanya bisa menyalakan senter dengan
                // facingMode tertentu. Kalau gagal, tombolnya dibuat nonaktif.
                tombolSenter.classList.add('opacity-40', 'cursor-not-allowed');
            }
        }

        // ============================================================
        // EVENT
        // ============================================================

        tombolMulai.addEventListener('click', nyalakanKamera);

        tombolAktif.addEventListener('click', () => {
            if (stream === null) {
                nyalakanKamera();
            } else {
                matikanKamera();
                tampilkanStatus(
                    'fa-video-slash',
                    'Kamera dimatikan',
                    'Tekan Aktifkan untuk memindai lagi.',
                    true
                );
            }
        });

        tombolGanti.addEventListener('click', gantiKamera);
        tombolSenter.addEventListener('click', toggleSenter);

        tombolScanLagi.addEventListener('click', async () => {
            resetHasil();
            await nyalakanKamera();
        });

        tombolKodeManual.addEventListener('click', () => {
            const nilai = kodeManual.value.trim();

            if (nilai === '') {
                kodeManual.focus();
                return;
            }

            terimaKode(nilai);
        });

        kodeManual.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                tombolKodeManual.click();
            }
        });

        // Sesi kamera di smartphone sering tetap menyala kalau hanya
        // beforeunload, jadi pagehide juga dipakai karena lebih andal.
        window.addEventListener('pagehide', matikanKamera);

        // Tidak ada gunanya kamera tetap aktif ketika tab tidak terlihat.
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && stream !== null) {
                matikanKamera();
            }
        });

        // Halaman ini sudah dibuka lewat tombol pengguna, jadi izin kamera
        // langsung diminta supaya tidak perlu klik sekali lagi.
        nyalakanKamera();
    </script>
@endpush
