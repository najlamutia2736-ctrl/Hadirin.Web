@extends('layouts.app')

@section('title', 'Pindai Barcode · Hadirin.Web')

@section('konten')
    {{-- header halaman --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Pindai Barcode Absensi</h2>
        <p class="mt-1 text-gray-600">
            @if ($tab === 'qr')
                Tampilkan QR sesi supaya mahasiswa bisa memindai sendiri lewat halaman Scan QR di akun mereka.
            @else
                Pindai barcode mahasiswa untuk mencatat kehadirannya pada sesi mata kuliah yang dipilih.
            @endif
        </p>
    </div>

    @if ($daftarSesi->isEmpty())
        {{-- Tanpa jadwal hari ini memang tidak ada sesi yang bisa dipindai. --}}
        <div class="rounded-xl border border-gray-100 bg-white p-8 text-center shadow-sm">
            <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-slate-500">
                <i class="fas fa-calendar-xmark"></i>
            </div>
            <h3 class="mt-4 font-semibold text-gray-800">Tidak ada sesi hari ini</h3>
            <p class="mx-auto mt-1 max-w-md text-sm text-gray-500">
                @if (auth()->user()->role === 'Dosen')
                    Belum ada jadwal mengajar untukmu hari ini, jadi belum ada sesi yang bisa dipindai.
                @else
                    Tidak ada jadwal kuliah aktif hari ini. Jadwal diisi lewat menu Jadwal, lalu
                    sesinya dibuat otomatis mengikuti jam yang admin tentukan.
                @endif
            </p>
        </div>
    @else
        {{-- Tab: kamera pemindai & QR sesi jadi dua halaman dalam satu bagian Pindai Barcode. --}}
        <div class="mb-6 inline-flex rounded-xl border border-gray-100 bg-white p-1 shadow-sm">
            @foreach ($pilihanTab as $pilihan)
                @php $aktif = $tab === $pilihan['nilai']; @endphp
                <a data-tab-link
                    href="{{ route($ruteHalaman, array_filter([
                        'tab' => $pilihan['nilai'] === 'pindai' ? null : $pilihan['nilai'],
                        'sesi' => $sesiTerpilih?->getKey(),
                    ])) }}"
                    @class([
                        'inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-colors',
                        'bg-indigo-600 text-white shadow-sm' => $aktif,
                        'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' => ! $aktif,
                    ])>
                    <i class="{{ $pilihan['icon'] }} text-xs"></i>
                    {{ $pilihan['label'] }}
                </a>
            @endforeach
        </div>

        {{-- Pemilih sesi: satu scan selalu ditujukan ke satu mata kuliah. --}}
        <div class="mb-6 rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <label for="pilihanSesi" class="block text-sm font-semibold text-gray-700">
                Sesi mata kuliah yang sedang dipilih
            </label>
            <select name="sesi" id="pilihanSesi"
                class="mt-2 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                @foreach ($daftarSesi as $sesi)
                    @php
                        $kondisi = $sesi->kondisiSekarang();
                        $labelKondisi = ['belum' => 'belum mulai', 'berjalan' => 'berjalan', 'selesai' => 'selesai'][$kondisi];
                    @endphp
                    <option value="{{ $sesi->id }}" data-jam="{{ $sesi->waktu_mulai->format('H:i') }}-{{ $sesi->waktu_selesai->format('H:i') }}"
                        @selected($sesiTerpilih !== null && $sesi->id === $sesiTerpilih->id)>
                        {{ $sesi->judul() }}
                        · {{ $sesi->jadwal?->kelas?->nama_kelas ?? 'Tanpa kelas' }}
                        · {{ $sesi->waktu_mulai->format('H:i') }}-{{ $sesi->waktu_selesai->format('H:i') }}
                        · {{ $labelKondisi }}
                        · {{ $sesi->absensis_count ?? 0 }} tercatat
                    </option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-gray-500">
                Absensi ditutup otomatis pada jam selesai sesi di atas, sesuai jadwal yang diatur admin.
                Scan setelah jam itu ditolak sebagai kedaluwarsa.
            </p>
        </div>

        @if ($tab === 'qr')
            {{-- ============================================================
                 TAB QR SESI
                 ============================================================
                 Halaman ini hanya menampilkan QR milik sesi terpilih supaya
                 bisa dipindai mahasiswa lewat halaman Scan QR mereka. Kamera
                 pemindai dan input manual pindah ke tab Pindai Barcode.
                 --}}
            <div class="mx-auto max-w-lg rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                        <i class="fas fa-qrcode text-lg"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800">QR Sesi untuk Mahasiswa</h2>
                        <p class="text-xs text-gray-500">Tampilkan QR ini di depan kelas</p>
                    </div>
                </div>

                <div id="qrSesi" data-barcode-mahasiswa data-barcode-teks="{{ $sesiTerpilih?->kode_sesi ?? '' }}"
                    data-barcode-ukuran="240"
                    class="mx-auto mt-5 flex min-h-[240px] items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50 p-4">
                </div>

                @if ($sesiTerpilih !== null)
                    <p class="mt-4 text-center text-sm font-semibold text-gray-800">
                        {{ $sesiTerpilih->judul() }}
                        · {{ $sesiTerpilih->jadwal?->kelas?->nama_kelas ?? 'Tanpa kelas' }}
                        · {{ $sesiTerpilih->waktu_mulai->format('H:i') }}-{{ $sesiTerpilih->waktu_selesai->format('H:i') }}
                    </p>
                @endif

                <p class="mt-3 text-center text-xs leading-relaxed text-gray-500">
                    Mahasiswa memindai QR ini lewat halaman <span class="font-semibold">Scan QR</span> di akun mereka,
                    lalu kehadirannya langsung tercatat pada sesi terpilih. Ganti sesi di atas untuk menampilkan QR
                    mata kuliah lain.
                </p>
            </div>

            <div class="mx-auto mt-4 max-w-lg rounded-xl border border-indigo-100 bg-indigo-50/60 p-5">
                <h2 class="text-sm font-semibold text-indigo-900">Catatan QR Sesi</h2>
                <ul class="mt-2 space-y-1.5 text-xs leading-relaxed text-indigo-800/80">
                    <li class="flex gap-2">
                        <i class="fas fa-circle-check mt-0.5 text-[10px] text-indigo-500"></i>
                        QR berisi kode sesi terpilih, jadi kehadiran mahasiswa tercatat pada sesi itu.
                    </li>
                    <li class="flex gap-2">
                        <i class="fas fa-circle-check mt-0.5 text-[10px] text-indigo-500"></i>
                        QR hanya berlaku sampai jam selesai sesi; scan setelah jam itu ditolak sebagai kedaluwarsa.
                    </li>
                    <li class="flex gap-2">
                        <i class="fas fa-circle-check mt-0.5 text-[10px] text-indigo-500"></i>
                        Untuk mencatat lewat kamera atau NIM manual, buka tab Pindai Barcode.
                    </li>
                </ul>
            </div>
        @else
            {{-- ============================================================
                 TAB PINDAI BARCODE
                 ============================================================
                 Kamera pemindai ada di kolom besar, panel kanan berisi hasil
                 scan dan input manual. QR sesi pindah ke tab QR Sesi.
                 --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {{-- Area kamera --}}
                <section class="lg:col-span-2">
                    <div class="overflow-hidden rounded-xl border border-gray-100 bg-slate-900 shadow-sm">
                        {{-- Rasio 4:3 supaya video tidak gepeng di HP --}}
                        <div class="relative aspect-[4/3] w-full bg-slate-900" id="wadahKamera">
                            <video id="kamera" playsinline autoplay muted
                                class="h-full w-full object-cover opacity-0 transition-opacity duration-300"></video>

                            {{-- Kanvas disembunyikan, hanya dipakai untuk membaca frame --}}
                            <canvas id="kanvas" class="hidden"></canvas>

                            {{-- Bingkai bidik: empat sudut dan garis scan --}}
                            <div id="bingkai" class="pointer-events-none absolute inset-0 hidden">
                                <div class="absolute inset-[12%]">
                                    <span class="absolute left-0 top-0 h-8 w-8 rounded-tl-lg border-l-4 border-t-4 border-white"></span>
                                    <span class="absolute right-0 top-0 h-8 w-8 rounded-tr-lg border-r-4 border-t-4 border-white"></span>
                                    <span class="absolute bottom-0 left-0 h-8 w-8 rounded-bl-lg border-b-4 border-l-4 border-white"></span>
                                    <span class="absolute bottom-0 right-0 h-8 w-8 rounded-br-lg border-b-4 border-r-4 border-white"></span>
                                    <span class="garis-scan absolute inset-x-2 top-2 h-0.5 rounded-full bg-indigo-400"></span>
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

                {{-- Panel samping: hasil scan dan input manual --}}
                <section class="space-y-4">
                    {{-- Hasil pemindaian --}}
                    <div id="panelHasil" class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span id="ikonHasil"
                                class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                                <i class="fas fa-barcode"></i>
                            </span>
                            <div>
                                <h2 class="text-sm font-semibold text-gray-800">Hasil Pemindaian</h2>
                                <p class="text-xs text-gray-500" id="keteranganHasil">
                                    Belum ada barcode dipindai.
                                </p>
                            </div>
                        </div>

                        <div id="isiHasil" class="mt-4 hidden rounded-xl border border-dashed border-gray-200 bg-gray-50 p-4 text-center">
                            <p class="text-xs uppercase tracking-wide text-gray-400">Mahasiswa</p>
                            <p id="hasilNama" class="mt-1 text-sm font-semibold text-gray-800"></p>
                            <p id="hasilNim" class="font-mono text-xs text-gray-500"></p>
                        </div>
                    </div>

                    {{-- Input manual: untuk kamera bermasalah atau barcode scanner fisik --}}
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                        <h2 class="text-sm font-semibold text-gray-800">Input manual</h2>
                        <p class="mt-1 text-xs leading-relaxed text-gray-500">
                            Ketik NIM mahasiswa, atau pakai barcode scanner fisik yang mengetik hasil scan
                            langsung ke kolom ini.
                        </p>

                        <label for="kodeManual" class="mt-4 block text-xs font-medium text-gray-600">NIM mahasiswa</label>
                        <input type="text" id="kodeManual" autocomplete="off" placeholder="Contoh: 20260001"
                            class="mt-1.5 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">

                        <button type="button" id="tombolKodeManual"
                            class="mt-2.5 w-full rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-900">
                            <i class="fas fa-user-check mr-1.5 text-xs"></i>
                            Catat kehadiran
                        </button>
                    </div>

                    {{-- Tips --}}
                    <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 p-5">
                        <h2 class="text-sm font-semibold text-indigo-900">Aturan pemindaian</h2>
                        <ul class="mt-2 space-y-1.5 text-xs leading-relaxed text-indigo-800/80">
                            <li class="flex gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-[10px] text-indigo-500"></i>
                                Satu kali pindai = satu mahasiswa tercatat hadir di sesi terpilih.
                            </li>
                            <li class="flex gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-[10px] text-indigo-500"></i>
                                Scan ulang pada sesi yang sama ditolak, jadi kehadiran tidak tercatat ganda.
                            </li>
                            <li class="flex gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-[10px] text-indigo-500"></i>
                                Barcode dari kelas lain ditolak, sesi sudah lewat jam selesai juga ditolak.
                            </li>
                            <li class="flex gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-[10px] text-indigo-500"></i>
                                QR untuk dipindai mahasiswa ada di tab QR Sesi.
                            </li>
                        </ul>
                    </div>
                </section>
            </div>

            <noscript>
                <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    Halaman ini butuh JavaScript untuk menyalakan kamera. Aktifkan JavaScript, atau pakai
                    input manual di samping untuk memindai lewat barcode scanner fisik.
                </div>
            </noscript>
        @endif
    @endif
@endsection

{{-- Pustaka QR hanya dibutuhkan di tab QR Sesi, tempat QR digambar. --}}
@if ($daftarSesi->isNotEmpty() && $tab === 'qr')
    @include('components.qr-script')
@endif

{{-- Skrip ini hanya dibutuhkan kalau ada sesi yang bisa dipilih. --}}
@if ($daftarSesi->isNotEmpty())
    @push('scripts')
    <script>
        // ============================================================
        // PEMILIH SESI (dipakai kedua tab Pindai Barcode)
        // ============================================================
        // Sesi dipilih lewat query `?sesi=` di alamat halaman, jadi pilihan yang
        // sama ikut terbawa ketika petugas berpindah antara tab Pindai dan tab
        // QR Sesi — termasuk saat tautan tab ditekan.
        const pilihanSesi = document.getElementById('pilihanSesi');

        function sinkronPilihanSesi() {
            const sesiId = pilihanSesi.value;

            document.querySelectorAll('[data-tab-link]').forEach(function (tautan) {
                const alamat = new URL(tautan.href);
                alamat.searchParams.set('sesi', sesiId);
                tautan.href = alamat.toString();
            });

            const halaman = new URL(window.location.href);
            halaman.searchParams.set('sesi', sesiId);
            window.history.replaceState({}, '', halaman.toString());
        }
    </script>

    @if ($tab === 'pindai')
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
        </style>

        <script>
            // ============================================================
            // PINDAI BARCODE ABSENSI
            // ============================================================
            // Kamera dibuka lewat getUserMedia, lalu tiap frame digambar ke canvas
            // dan dibaca jsQR. Beda dengan halaman scan-qr mahasiswa, hasil scan di
            // sini langsung dikirim ke server untuk dicatat sebagai kehadiran dan
            // kamera tetap menyala supaya petugas bisa memindai mahasiswa
            // berikutnya tanpa membuka halaman lagi.

            const RUTE_SIMPAN = @json($ruteSimpan);
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
            const ikonHasil = document.getElementById('ikonHasil');
            const keteranganHasil = document.getElementById('keteranganHasil');
            const isiHasil = document.getElementById('isiHasil');
            const hasilNama = document.getElementById('hasilNama');
            const hasilNim = document.getElementById('hasilNim');
            const kodeManual = document.getElementById('kodeManual');
            const tombolKodeManual = document.getElementById('tombolKodeManual');

            // willReadFrequently dipakai karena tiap pembacaan frame memanggil
            // getImageData, yang tanpa flag ini memicu upload ulang texture.
            const konteks = kanvas.getContext('2d', {
                willReadFrequently: true
            });

            let stream = null;
            let idFrame = 0;
            let kameraTersedia = [];
            let indeksKamera = 0;
            let pakaiKameraBelakang = true;
            let kirimOtomatis = true;
            let sedangMengirim = false;

            // ============================================================
            // PESAN ERROR KAMERA
            // ============================================================

            const PESAN_KAMERA = {
                NotAllowedError: {
                    judul: 'Izin kamera ditolak',
                    detail: 'Aktifkan izin kamera untuk situs ini di pengaturan browser, lalu tekan tombol di bawah.',
                },
                NotFoundError: {
                    judul: 'Kamera tidak ditemukan',
                    detail: 'Tidak ada kamera yang bisa dipakai di perangkat ini. Pakai input manual di samping.',
                },
                NotReadableError: {
                    judul: 'Kamera sedang dipakai aplikasi lain',
                    detail: 'Tutup aplikasi lain yang sedang memakai kamera, lalu coba lagi.',
                },
                OverconstrainedError: {
                    judul: 'Kamera tidak mendukung',
                    detail: 'Kamera yang diminta tidak tersedia. Coba ganti kamera atau pakai input manual.',
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
                        'Gunakan Chrome, Edge, Firefox, atau Safari versi terbaru. Sementara itu pakai input manual di samping.',
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
                    // Sebagian browser menolak autoplay walau atributnya sudah ada.
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

                    if (hasil !== null && kirimOtomatis && !sedangMengirim) {
                        kirimOtomatis = false;
                        terimaKode(hasil.data);
                    }
                };

                idFrame = requestAnimationFrame(tick);
            }

            // ============================================================
            // PENGIRIMAN KE SERVER
            // ============================================================

            function terimaKode(nilai) {
                const teks = String(nilai).trim();

                if (teks === '') {
                    kirimOtomatis = true;
                    return;
                }

                kirimKode(teks);
            }

            /**
             * Kirim satu kode hasil scan ke server lalu tampilkan hasilnya.
             *
             * Kamera sengaja tidak dimatikan di sini: petugas pasti memindai
             * mahasiswa berikutnya, jadi pemindaian cukup dijeda beberapa detik
             * supaya hasil sempat terbaca dan barcode yang sama tidak terkirim dua
             * kali.
             */
            async function kirimKode(kode) {
                if (sedangMengirim || pilihanSesi.value === '') {
                    return;
                }

                sedangMengirim = true;

                try {
                    const respons = await fetch(RUTE_SIMPAN, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            kode: kode,
                            sesi: pilihanSesi.value,
                        }),
                    });

                    const hasil = await respons.json();

                    if (respons.ok && hasil.sukses === true) {
                        tampilkanBerhasil(hasil);
                    } else {
                        tampilkanGagal(hasil.pesan || hasil.message || 'Barcode ditolak oleh server.');
                    }
                } catch (error) {
                    tampilkanGagal('Gagal menghubungi server. Periksa koneksi lalu coba lagi.');
                } finally {
                    sedangMengirim = false;

                    // Jeda sebentar supaya pesan hasil sempat terbaca, lalu
                    // pemindaian berjalan lagi untuk mahasiswa berikutnya.
                    setTimeout(() => {
                        kirimOtomatis = true;
                    }, 2000);
                }
            }

            function tampilkanBerhasil(hasil) {
                panelHasil.classList.remove('border-gray-100', 'border-red-200');
                panelHasil.classList.add('border-green-200', 'bg-green-50/60');
                ikonHasil.className = 'grid h-9 w-9 place-items-center rounded-lg bg-green-100 text-green-700';
                ikonHasil.innerHTML = '<i class="fas fa-circle-check"></i>';

                keteranganHasil.textContent = hasil.pesan || 'Kehadiran tercatat.';
                hasilNama.textContent = hasil.mahasiswa || '-';
                hasilNim.textContent = (hasil.nim || '') + (hasil.kelas ? ' · ' + hasil.kelas : '');
                isiHasil.classList.remove('hidden');

                if (navigator.vibrate) {
                    navigator.vibrate(120);
                }

                bunyiNada(true);
            }

            function tampilkanGagal(pesan) {
                panelHasil.classList.remove('border-gray-100', 'border-green-200', 'bg-green-50/60');
                panelHasil.classList.add('border-red-200', 'bg-red-50/60');
                ikonHasil.className = 'grid h-9 w-9 place-items-center rounded-lg bg-red-100 text-red-700';
                ikonHasil.innerHTML = '<i class="fas fa-triangle-exclamation"></i>';

                keteranganHasil.textContent = pesan;
                hasilNama.textContent = 'Tidak dicatat';
                hasilNim.textContent = '';
                isiHasil.classList.remove('hidden');

                if (navigator.vibrate) {
                    navigator.vibrate([80, 60, 80]);
                }

                bunyiNada(false);
            }

            // Bunyinya dibuat dari Web Audio API supaya tidak perlu file audio.
            function bunyiNada(berhasil) {
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
                    oscillator.frequency.setValueAtTime(berhasil ? 880 : 320, audio.currentTime);
                    gain.gain.setValueAtTime(0.08, audio.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.0001, audio.currentTime + 0.25);
                    oscillator.start();
                    oscillator.stop(audio.currentTime + 0.25);
                } catch (error) {
                    // Bunyi hanya bonus, jadi kegagalan di sini diabaikan diam-diam.
                }
            }

            // ============================================================
            // KONTROL KAMERA
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

            tombolKodeManual.addEventListener('click', () => {
                const nilai = kodeManual.value.trim();

                if (nilai === '') {
                    kodeManual.focus();
                    return;
                }

                kodeManual.value = '';
                kirimKode(nilai);
            });

            kodeManual.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    tombolKodeManual.click();
                }
            });

            // Ganti sesi berarti scan berikutnya ditujukan ke mata kuliah lain,
            // jadi hasil lama tidak boleh masih terbaca seolah berlaku. Pilihan
            // sesinya juga disinkronkan ke alamat & tautan tab supaya ikut
            // terbawa saat berpindah ke tab QR Sesi.
            pilihanSesi.addEventListener('change', () => {
                panelHasil.classList.remove('border-green-200', 'border-red-200', 'bg-green-50/60', 'bg-red-50/60');
                panelHasil.classList.add('border-gray-100');
                ikonHasil.className = 'grid h-9 w-9 place-items-center rounded-lg bg-indigo-50 text-indigo-600';
                ikonHasil.innerHTML = '<i class="fas fa-barcode"></i>';
                keteranganHasil.textContent = 'Belum ada barcode dipindai.';
                isiHasil.classList.add('hidden');

                sinkronPilihanSesi();
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

            // Halaman ini memang dibuka untuk memindai, jadi izin kamera langsung
            // diminta supaya petugas tidak perlu klik sekali lagi.
            nyalakanKamera();
        </script>
    @else
        <script>
            // ============================================================
            // QR SESI
            // ============================================================
            // Kode sesi tiap pilihan, dipakai saat menampilkan QR Sesi di wadah
            // `#qrSesi`. QR digambar memakai helper dari `components.qr-script`,
            // dan versi awalnya sudah otomatis digambar saat halaman siap lewat
            // atribut `data-barcode-teks`.
            const KODE_SESI = @json($daftarSesi->mapWithKeys(fn ($sesi) => [$sesi->id => $sesi->kode_sesi])->all());

            // Ganti sesi berarti QR yang ditampilkan harus ikut berganti supaya
            // selalu sama dengan sesi terpilih, dan pilihannya disinkronkan ke
            // alamat & tautan tab supaya ikut terbawa ke tab Pindai Barcode.
            pilihanSesi.addEventListener('change', () => {
                const kodeSesiBaru = KODE_SESI[pilihanSesi.value] || '';

                if (typeof window.gambarBarcodeMahasiswa === 'function') {
                    window.gambarBarcodeMahasiswa(document.getElementById('qrSesi'), kodeSesiBaru, 240);
                }

                sinkronPilihanSesi();
            });
        </script>
    @endif
    @endpush
@endif
