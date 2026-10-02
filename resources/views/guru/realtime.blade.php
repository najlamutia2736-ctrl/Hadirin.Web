@extends('layouts.app')

@section('title', 'Real-Time Monitoring · Hadirin.Web')

@php
    $pageTitle = 'Real-Time Monitoring';
    $breadcrumbItems = [
        ['label' => 'Beranda'],
        ['label' => 'Dashboard Guru'],
        ['label' => 'Real-Time Monitoring', 'current' => true],
    ];

    /*
    | Palet warna yang sama dengan dashboard guru supaya angka kehadiran
    | di kedua halaman langsung terbaca dengan warna yang sama.
    */
    $tones = [
        'blue' => [
            'chip' => 'bg-gradient-to-br from-blue-500 to-sky-500 text-white shadow-md shadow-blue-500/30',
            'value' => 'text-blue-600',
        ],
        'green' => [
            'chip' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/30',
            'value' => 'text-emerald-600',
        ],
        'amber' => [
            'chip' => 'bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-md shadow-amber-500/30',
            'value' => 'text-amber-600',
        ],
        'rose' => [
            'chip' => 'bg-gradient-to-br from-rose-500 to-red-500 text-white shadow-md shadow-rose-500/30',
            'value' => 'text-rose-600',
        ],
        'slate' => [
            'chip' => 'bg-gradient-to-br from-slate-500 to-slate-600 text-white shadow-md shadow-slate-500/30',
            'value' => 'text-slate-600',
        ],
    ];

    /*
    | Kartu statistik. Nilai numeriknya diisi JS dari `presensiList` tiap kali
    | polling selesai, jadi label/ikon/warna tetap di Blade.
    */
    $statCards = [
        [
            'key' => 'total',
            'label' => 'Total Siswa',
            'icon' => 'fas fa-user-graduate',
            'tone' => 'blue',
        ],
        [
            'key' => 'hadir',
            'label' => 'Sudah Absen',
            'icon' => 'fas fa-circle-check',
            'tone' => 'green',
        ],
        [
            'key' => 'izinSakit',
            'label' => 'Izin / Sakit',
            'icon' => 'fas fa-file-circle-exclamation',
            'tone' => 'amber',
        ],
        [
            'key' => 'belum',
            'label' => 'Belum Absen',
            'icon' => 'fas fa-hourglass-half',
            'tone' => 'slate',
        ],
    ];
@endphp

@section('konten')
    {{-- pemilih kelas + kendali polling --}}
    <div
        class="mb-6 flex flex-col gap-4 rounded-xl border border-gray-100 bg-white px-5 py-4 shadow-sm lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                <i class="fas fa-chalkboard"></i>
            </span>
            <div>
                <h3 class="text-sm font-semibold text-gray-800">Kelas yang Dipantau</h3>
                <p class="text-xs text-gray-500" data-kelasAktif>-</p>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div id="realtimeKelasChips" class="-mx-1 flex flex-wrap gap-2 px-1"></div>

            <div class="flex items-center gap-2 border-t border-gray-100 pt-3 sm:border-l sm:border-t-0 sm:pl-4 sm:pt-0">
                <button type="button" id="tombolRefresh"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition-colors hover:bg-gray-50 hover:text-indigo-600"
                    title="Segarkan sekarang">
                    <i class="fas fa-rotate text-sm"></i>
                </button>

                <button type="button" id="tombolAutoRefresh"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
                    <i class="fas fa-pause"></i>
                    <span data-labelAutoRefresh>Jeda</span>
                </button>
            </div>
        </div>
    </div>

    {{-- muncul kalau guru ini belum punya kelas yang diampu --}}
    <div id="realtimeKosongKelas"
        class="mb-6 hidden rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
        <div class="flex items-start gap-3">
            <i class="fas fa-circle-info mt-0.5 shrink-0"></i>
            <div>
                <p class="font-semibold">Belum ada kelas yang diampu.</p>
                <p class="mt-0.5">
                    Hubungkan kelas dengan akun Anda di dashboard admin, menu
                    <a href="{{ route('cms.classes') }}" class="font-semibold underline">Classes</a>,
                    kolom <span class="font-semibold">Guru Pengampu</span>. Siswa
                    kelas yang terhubung akan otomatis dipantau di halaman ini.
                </p>
            </div>
        </div>
    </div>

    {{-- kartu statistik --}}
    <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($statCards as $card)
            @php $tone = $tones[$card['tone']]; @endphp
            <div
                class="group relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-lg transition-transform duration-300 group-hover:scale-110 {{ $tone['chip'] }}">
                        <i class="{{ $card['icon'] }}"></i>
                    </div>
                    <span data-persenStat="{{ $card['key'] }}"
                        class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-500">
                        0%
                    </span>
                </div>

                <p class="mt-4 text-sm font-medium text-gray-500">{{ $card['label'] }}</p>
                <p class="mt-0.5 text-2xl font-bold tracking-tight {{ $tone['value'] }}"
                    data-nilaiStat="{{ $card['key'] }}">0</p>
                <p class="mt-1 text-xs text-gray-400" data-keteranganStat="{{ $card['key'] }}">-</p>
            </div>
        @endforeach
    </div>

    {{-- progres sesi + feed absensi masuk --}}
    <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div>
            <div class="flex h-full flex-col rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-emerald-50 text-emerald-600">
                            <i class="fas fa-gauge-high"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">Progres Sesi</h3>
                            <p class="text-xs text-gray-500" data-subtitleProgres>-</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-1 flex-col justify-center px-6 py-6">
                    <div class="relative mx-auto h-40 w-40">
                        <div id="donutRealtime"
                            class="h-40 w-40 rounded-full transition-all duration-500"
                            style="background: conic-gradient(#e5e7eb 0 100%);"></div>
                        <div class="absolute inset-[16px] flex flex-col items-center justify-center rounded-full bg-white">
                            <span class="text-3xl font-bold tracking-tight text-gray-800" data-donutPersen>0%</span>
                            <span class="text-[11px] font-medium text-gray-500">Hadir</span>
                        </div>
                    </div>

                    <div class="mt-6 space-y-2.5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2 text-gray-600">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                Hadir
                            </span>
                            <span class="font-semibold text-gray-800" data-rekap="hadir">0</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2 text-gray-600">
                                <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                                Izin
                            </span>
                            <span class="font-semibold text-gray-800" data-rekap="izin">0</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2 text-gray-600">
                                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                                Sakit
                            </span>
                            <span class="font-semibold text-gray-800" data-rekap="sakit">0</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2 text-gray-600">
                                <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                                Alpa
                            </span>
                            <span class="font-semibold text-gray-800" data-rekap="alpha">0</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2 text-gray-600">
                                <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                                Belum Absen
                            </span>
                            <span class="font-semibold text-gray-800" data-rekap="belum">0</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 px-6 py-3 text-center text-xs text-gray-500">
                    <span data-terakhirSinkron>-</span>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="flex h-full flex-col rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                            <i class="fas fa-bolt"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">Absensi Masuk</h3>
                            <p class="text-xs text-gray-500">Siswa terbaru yang tercatat hari ini</p>
                        </div>
                    </div>
                    <span data-badgeSinkron
                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-600">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                        <span data-teksSinkron>Live</span>
                    </span>
                </div>

                <div id="feedRealtime" class="flex-1 divide-y divide-gray-100 overflow-y-auto"></div>
            </div>
        </div>
    </div>

    {{-- daftar siswa --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-violet-50 text-violet-600">
                    <i class="fas fa-list-check"></i>
                </span>
                <div>
                    <h3 class="font-semibold text-gray-800">Daftar Siswa</h3>
                    <p class="text-xs text-gray-500" data-subtitleTabel>-</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <label for="filterStatusRealtime" class="sr-only">Saring status</label>
                <select id="filterStatusRealtime"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="semua">Semua status</option>
                    <option value="Hadir">Hadir</option>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Alpha">Alpa</option>
                    <option value="Belum">Belum Absen</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Siswa</th>
                        <th class="px-4 py-3 font-semibold">Kelas</th>
                        <th class="px-4 py-3 font-semibold">Waktu</th>
                        <th class="px-4 py-3 font-semibold">Metode</th>
                        <th class="px-4 py-3 text-right font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody id="tabelRealtime" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-6 py-3">
            <span class="text-xs text-gray-500">
                <span data-jumlahTampil>0</span> dari <span data-jumlahTotal>0</span> siswa ditampilkan
            </span>
            <a href="{{ route('guru.dashboard') }}"
                class="text-xs font-semibold text-indigo-600 transition-colors hover:text-indigo-700">
                Ringkasan lengkap <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>

    {{-- wadah toast untuk absensi yang baru masuk --}}
    <div id="toastRealtime" class="pointer-events-none fixed right-5 top-5 z-50 flex w-80 flex-col gap-2"></div>
@endsection

@push('styles')
    @include('guru.partials.styles')

    <style>
        /* baris tabel real-time */
        .realtime-row {
            transition: background-color 0.2s ease;
        }

        .realtime-row:hover {
            background-color: #f9fafb;
        }

        /* chip pemilih kelas */
        .kelas-chip {
            transition: all 0.2s ease;
        }

        /* baris feed yang baru masuk */
        @keyframes feedMasuk {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .feed-item {
            animation: feedMasuk 0.3s ease;
        }

        /* toast absensi baru, memakai keyframe yang sudah ada di styles bersama */
        .toast-item {
            animation: slideInRight 0.3s ease;
        }
    </style>
@endpush

@push('scripts')
    {{-- Data kelas & siswa dari database, dibaca data-guru.blade.php --}}
    <script>
        window.HADIRIN_GURU = @json($guruData ?? null);
    </script>

    @include('guru.partials.data-guru')

    <script>
        // ============================================================
        // REAL-TIME MONITORING
        // ============================================================
        // Dua sumber data, keduanya sudah tersedia tanpa endpoint baru:
        //
        // 1. **Server (database)** — `guru.dashboard` sebagai JSON, yang
        //    isinya sama dengan `window.HADIRIN_GURU`. Dipakai sebagai
        //    sumber kebenaran dan dimuat ulang tiap polling.
        // 2. **localStorage** — `daftar_absen`/`siswa_absen` yang diisi
        //    halaman absensi siswa. Langsung dipakai supaya catatan baru
        //    terlihat seketika, sebelum round-trip ke database selesai.

        const ENDPOINT_REFRESH = @json(route('guru.dashboard'));
        const INTERVAL_POLLING = 15000;
        const BATAS_FEED = 12;

        const PETA_BADGE = {
            'Hadir': 'badge-hadir',
            'Izin': 'badge-izin',
            'Sakit': 'badge-sakit',
            'Alpha': 'badge-alpha'
        };

        const PETA_BADGE_METODE = {
            'Scan QR': 'badge-scan',
            'ID Unik': 'badge-id',
            'Izin/Sakit': 'badge-izin-metode'
        };

        const WARNA_DONUT = {
            hadir: '#22c55e',
            izin: '#3b82f6',
            sakit: '#f59e0b',
            alpha: '#ef4444',
            belum: '#cbd5e1'
        };

        let kelasAktif = null;
        let daftarKelas = [];
        let filterStatus = 'semua';
        let autoRefresh = true;
        let timerPolling = null;

        // Kunci absensi yang sudah pernah ditampilkan, dipakai untuk
        // membedakan "baris baru" dari sekadar hasil render ulang.
        const absensiTerlihat = new Set();

        // Menandai bahwa daftar awal sudah didaftarkan, sehingga render
        // berikutnya benar-benar bisa membedakan absensi baru dari data
        // lama yang baru pertama kali terlihat.
        let siudahDaftarkanAwal = false;

        // ============================================================
        // SUMBER DATA
        // ============================================================
        function daftarKelasDiajarkan() {
            const dariServer = daftarKelasServer();

            if (dariServer.length > 0) return dariServer;

            return Object.keys(globalData.siswaPerKelas || {});
        }

        /**
         * Catatan absensi dari localStorage, dibatasi ke kelas aktif.
         *
         * `loadAllAbsensiSiswa()` sudah menangani kedua kunci storage dan
         * mengurutkan dari yang terbaru, jadi di sini hanya perlu disaring.
         * Catatan tanpa nama dilewati karena tidak bisa ditampilkan.
         */
        function loadRealtimeFromStorage() {
            return loadAllAbsensiSiswa().filter(function (catatan) {
                if (!catatan || !catatan.nama) return false;
                if (kelasAktif === null) return true;

                // Catatan dari storage belum selalu membawa kelas, jadi
                // yang tidak menyebut kelas tetap dianggap milik kelas aktif.
                return !catatan.kelas || catatan.kelas === kelasAktif;
            });
        }

        /**
         * Gabungkan catatan server dengan catatan localStorage.
         *
         * Server menang kalau catatan yang sama sudah ada di database, karena
         * database yang jadi sumber kebenaran. LocalStorage hanya menambah
         * catatan yang belum sempat masuk ke server.
         */
        function gabungkanAbsensi() {
            const server = presensiList
                .filter(function (baris) {
                    return baris.waktu !== null;
                })
                .map(function (baris) {
                    return {
                        nama: baris.nama,
                        nis: baris.nis,
                        kelas: baris.kelas,
                        waktu: baris.waktu,
                        metode: baris.metode,
                        status: baris.status,
                        dariServer: true
                    };
                });

            const sudahAda = new Set(server.map(function (baris) {
                return kunciAbsensi(baris);
            }));

            const tambahan = loadRealtimeFromStorage()
                .filter(function (catatan) {
                    return !sudahAda.has(kunciAbsensi(catatan));
                })
                .map(function (catatan) {
                    return {
                        nama: catatan.nama,
                        nis: catatan.nis || '-',
                        kelas: catatan.kelas || kelasAktif,
                        waktu: new Date(catatan.timestamp || Date.now()),
                        metode: catatan.metode || 'Scan QR',
                        status: toLabelStatus(catatan.status) || 'Hadir',
                        dariServer: false
                    };
                });

            return server.concat(tambahan).sort(function (a, b) {
                return new Date(b.waktu) - new Date(a.waktu);
            });
        }

        /**
         * Kunci unik satu catatan absensi.
         *
         *_absensi pada database selalu punya `waktu` presisi, sedangkan
         * catatan dari storage bisa lebih kasar, jadi nama + kelas + waktu
         * dipakai bersama agar keduanya bisa dibandingkan.
         */
        function kunciAbsensi(catatan) {
            const waktu = catatan.waktu instanceof Date
                ? catatan.waktu.getTime()
                : new Date(catatan.waktu || catatan.timestamp || 0).getTime();

            return (catatan.nama || '') + '|' + (catatan.kelas || '') + '|' + waktu;
        }

        // ============================================================
        // POLLING DARI SERVER
        // ============================================================
        async function muatDataServer() {
            try {
                const response = await fetch(ENDPOINT_REFRESH, {
                    headers: { Accept: 'application/json' }
                });

                if (!response.ok) return;

                const hasil = await response.json();

                if (Array.isArray(hasil.kelas)) {
                    // `SERVER_DATA` dipakai ulang supaya `data-guru` ikut
                    // membaca kelas terbaru pada render berikutnya.
                    SERVER_DATA.kelas = hasil.kelas;
                }

                if (SERVER_DATA && hasil.guru) {
                    SERVER_DATA.guru = hasil.guru;
                }

                daftarKelas = daftarKelasDiajarkan();

                // Kelas yang dipantau mungkin sudah tidak diampu guru.
                if (daftarKelas.indexOf(kelasAktif) === -1) {
                    kelasAktif = daftarKelas[0] || null;
                }

                if (kelasAktif !== null) {
                    loadDataKelas(kelasAktif);
                }

                renderSeluruhHalaman();
            } catch (error) {
                console.warn('Gagal menyegarkan data real-time:', error);
                setTeks('[data-terakhirSinkron]', 'Sinkronisasi gagal, mencoba lagi...');
            }
        }

        /**
         * Nyalakan penyegaran berkala.
         *
         * Timer disimpan di `timerPolling` supaya bisa dihentikan lagi dari
         * tombol Jeda tanpa meninggalkan interval ganda.
         */
        function startRealtimeAutoRefresh() {
            stopRealtimeAutoRefresh();

            if (!autoRefresh) return;

            timerPolling = window.setInterval(muatDataServer, INTERVAL_POLLING);
        }

        function stopRealtimeAutoRefresh() {
            if (timerPolling !== null) {
                window.clearInterval(timerPolling);
                timerPolling = null;
            }
        }

        function toggleAutoRefresh() {
            autoRefresh = !autoRefresh;

            const tombol = document.getElementById('tombolAutoRefresh');
            const label = tombol ? tombol.querySelector('[data-labelAutoRefresh]') : null;
            const ikon = tombol ? tombol.querySelector('i') : null;

            if (tombol) {
                tombol.classList.toggle('bg-indigo-600', autoRefresh);
                tombol.classList.toggle('hover:bg-indigo-700', autoRefresh);
                tombol.classList.toggle('bg-gray-100', !autoRefresh);
                tombol.classList.toggle('text-gray-600', !autoRefresh);
                tombol.classList.toggle('hover:bg-gray-200', !autoRefresh);
            }

            if (label) label.textContent = autoRefresh ? 'Jeda' : 'Lanjut';
            if (ikon) ikon.className = autoRefresh ? 'fas fa-pause' : 'fas fa-play';

            setTeks('[data-teksSinkron]', autoRefresh ? 'Live' : 'Dijeda');

            if (autoRefresh) {
                startRealtimeAutoRefresh();
                muatDataServer();
            } else {
                stopRealtimeAutoRefresh();
            }
        }

        // ============================================================
        // RENDER
        // ============================================================
        function renderPemilihKelas() {
            const container = document.getElementById('realtimeKelasChips');
            const kosong = document.getElementById('realtimeKosongKelas');

            if (kosong) kosong.classList.toggle('hidden', daftarKelas.length > 0);
            if (!container) return;

            container.innerHTML = '';

            if (daftarKelas.length === 0) return;

            daftarKelas.forEach(function (nama) {
                const kelas = dataKelasServer(nama);
                const jumlah = kelas ? (kelas.siswa || []).length : 0;
                const aktif = nama === kelasAktif;

                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'kelas-chip inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-xs font-semibold ring-1 ' +
                    (aktif
                        ? 'bg-indigo-600 text-white ring-indigo-600 shadow-md shadow-indigo-500/30'
                        : 'bg-white text-gray-600 ring-gray-200 hover:bg-indigo-50 hover:text-indigo-600 hover:ring-indigo-200');
                chip.innerHTML = '<span>' + nama + '</span>' +
                    '<span class="rounded-full px-1.5 py-0.5 text-[10px] ' +
                    (aktif ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500') + '">' +
                    jumlah + '</span>';

                chip.addEventListener('click', function () {
                    kelasAktif = nama;
                    loadDataKelas(nama);

                    // Absensi kelas yang baru dipilih bukan yang baru masuk,
                    // jadi hanya didaftarkan tanpa diumumkan sebagai toast.
                    absensiTerlihat.clear();
                    daftarkanAwal(gabungkanAbsensi());

                    renderSeluruhHalaman();
                });

                container.appendChild(chip);
            });
        }

        function renderStatistik(list) {
            const stat = hitungStatAbsensi(list);
            const total = list.length;
            const izinSakit = stat.izin + stat.sakit;

            const nilai = {
                total: total,
                hadir: stat.hadir,
                izinSakit: izinSakit,
                belum: stat.belum
            };

            const keterangan = {
                total: kelasAktif === null ? '-' : 'siswa di ' + kelasAktif,
                hadir: 'dari ' + total + ' siswa',
                izinSakit: izinSakit === 0 ? 'tidak ada' : izinSakit + ' siswa tercatat',
                belum: total > 0 ? Math.round((stat.belum / total) * 100) + '% belum absen' : '-'
            };

            Object.keys(nilai).forEach(function (key) {
                setTeks('[data-nilaiStat="' + key + '"]', nilai[key]);
                setTeks('[data-persenStat="' + key + '"]', persen(nilai[key], total) + '%');
                setTeks('[data-keteranganStat="' + key + '"]', keterangan[key]);
            });
        }

        function renderDonut(list) {
            const stat = hitungStatAbsensi(list);
            const total = list.length;
            const donut = document.getElementById('donutRealtime');

            Object.keys(WARNA_DONUT).forEach(function (key) {
                setTeks('[data-rekap="' + key + '"]', stat[key]);
            });

            setTeks('[data-donutPersen]', persen(stat.hadir, total) + '%');
            setTeks('[data-subtitleProgres]', kelasAktif === null
                ? 'Pilih kelas untuk mulai memantau'
                : kelasAktif + ' · ' + stat.hadir + ' dari ' + total + ' sudah absen');

            if (!donut) return;

            if (total === 0) {
                donut.style.background = 'conic-gradient(#e5e7eb 0 100%)';
                return;
            }

            let kumulatif = 0;
            const segmen = [];

            Object.keys(WARNA_DONUT).forEach(function (key) {
                const nilai = stat[key];
                if (nilai === 0) return;

                const mulai = kumulatif;
                kumulatif += (nilai / total) * 100;
                segmen.push(WARNA_DONUT[key] + ' ' + mulai.toFixed(2) + '% ' + kumulatif.toFixed(2) + '%');
            });

            donut.style.background = 'conic-gradient(' + segmen.join(', ') + ')';
        }

        function renderFeed(gabungan) {
            const container = document.getElementById('feedRealtime');

            if (!container) return;

            container.innerHTML = '';

            if (gabungan.length === 0) {
                container.innerHTML =
                    '<div class="flex h-full min-h-[16rem] flex-col items-center justify-center px-6 py-10 text-center">' +
                    '<span class="mb-3 grid h-14 w-14 place-items-center rounded-full bg-gray-100 text-gray-400">' +
                    '<i class="fas fa-inbox text-xl"></i></span>' +
                    '<p class="text-sm font-semibold text-gray-700">Belum ada absensi masuk</p>' +
                    '<p class="mt-1 max-w-xs text-xs leading-relaxed text-gray-500">' +
                    'Siswa yang memindai QR di kelas akan langsung muncul di sini tanpa perlu reload halaman.' +
                    '</p></div>';
                return;
            }

            gabungan.slice(0, BATAS_FEED).forEach(function (baris) {
                const inisial = (baris.nama || '?').charAt(0).toUpperCase();
                const badge = baris.status ? (PETA_BADGE[baris.status] || 'badge-belum') : 'badge-belum';

                const item = document.createElement('div');
                item.className = 'feed-item flex items-center gap-3 px-6 py-3';
                item.innerHTML =
                    '<span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-600">' +
                        inisial + '</span>' +
                    '<span class="min-w-0 flex-1">' +
                        '<span class="block truncate text-sm font-semibold text-gray-800">' +
                            (baris.nama || '-') + '</span>' +
                        '<span class="block text-xs text-gray-400">' +
                            (baris.kelas || '-') + ' · ' + formatWaktu(baris.waktu) +
                            (baris.dariServer ? '' : ' · dari perangkat siswa') + '</span>' +
                    '</span>' +
                    '<span class="shrink-0 ' + badge + '">' + (baris.status || 'Belum Absen') + '</span>';
            });
        }

        function renderTabel(list) {
            const tbody = document.getElementById('tabelRealtime');
            const visible = list.filter(function (baris) {
                if (filterStatus === 'semua') return true;
                if (filterStatus === 'Belum') return !baris.status;

                return baris.status === filterStatus;
            });

            setTeks('[data-subtitleTabel]', kelasAktif === null
                ? 'Pilih kelas terlebih dahulu'
                : kelasAktif + ' · ' + visible.length + ' dari ' + list.length + ' siswa');

            setTeks('[data-kelasAktif]', kelasAktif === null
                ? 'Belum ada kelas yang diampu'
                : 'Memantau ' + kelasAktif);

            setTeks('[data-jumlahTampil]', visible.length);
            setTeks('[data-jumlahTotal]', list.length);

            if (!tbody) return;

            tbody.innerHTML = '';

            if (list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">' +
                    '<i class="fas fa-inbox mb-2 block text-2xl text-gray-300"></i>' +
                    'Belum ada kelas yang bisa dipantau.</td></tr>';
                return;
            }

            if (visible.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">' +
                    'Tidak ada siswa dengan status "' + filterStatus + '".</td></tr>';
                return;
            }

            // Terlama masuk di atas supaya tabel ikut mengalir seiring absensi.
            visible.slice().reverse().forEach(function (baris) {
                const inisial = (baris.nama || '?').charAt(0).toUpperCase();
                const badge = baris.status ? (PETA_BADGE[baris.status] || 'badge-belum') : 'badge-belum';
                const metode = baris.metode ? (PETA_BADGE_METODE[baris.metode] || 'badge-id') : 'badge-belum';

                const tr = document.createElement('tr');
                tr.className = 'realtime-row';
                tr.innerHTML =
                    '<td class="px-6 py-3">' +
                        '<div class="flex items-center gap-3">' +
                            '<span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-600">' +
                                inisial + '</span>' +
                            '<span class="min-w-0">' +
                                '<span class="block max-w-[14rem] truncate text-sm font-semibold text-gray-800">' +
                                    (baris.nama || '-') + '</span>' +
                                '<span class="block text-xs text-gray-400">NIS ' + (baris.nis || '-') + '</span>' +
                            '</span>' +
                        '</div>' +
                    '</td>' +
                    '<td class="px-4 py-3">' +
                        '<span class="inline-block rounded-full bg-purple-50 px-2.5 py-1 text-xs font-medium text-purple-600">' +
                            (baris.kelas || '-') + '</span>' +
                    '</td>' +
                    '<td class="px-4 py-3 text-sm text-gray-600">' + formatWaktu(baris.waktu) + '</td>' +
                    '<td class="px-4 py-3"><span class="' + metode + '">' +
                        (baris.metode || 'Belum absen') + '</span></td>' +
                    '<td class="px-4 py-3 text-right"><span class="' + badge + '">' +
                        (baris.status || 'Belum Absen') + '</span></td>';
            });
        }

        /**
         * Tampilkan toast untuk absensi yang baru masuk.
         *
         * Dipanggil hanya untuk catatan yang belum pernah dirender, supaya
         * polling berikutnya tidak memunculkan toast yang sama berulang.
         */
        function tampilkanToast(baris) {
            const wadah = document.getElementById('toastRealtime');
            if (!wadah) return;

            const inisial = (baris.nama || '?').charAt(0).toUpperCase();

            const toast = document.createElement('div');
            toast.className = 'toast-item flex items-start gap-3 rounded-xl border border-emerald-200 bg-white p-4 shadow-lg';
            toast.innerHTML =
                '<span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600">' +
                    '<i class="fas fa-circle-check text-sm"></i></span>' +
                '<span class="min-w-0 flex-1">' +
                    '<span class="block truncate text-sm font-semibold text-gray-800">' +
                        (baris.nama || 'Siswa') + ' sudah absen</span>' +
                    '<span class="block text-xs text-gray-500">' +
                        (baris.kelas || '-') + ' · ' + formatWaktu(baris.waktu) + '</span>' +
                '</span>' +
                '<span class="badge-hadir shrink-0">Hadir</span>';

            wadah.appendChild(toast);

            // Batasi jumlah toast yang menumpuk saat absensi brewerhalten.
            while (wadah.children.length > 4) {
                wadah.removeChild(wadah.firstChild);
            }

            window.setTimeout(function () {
                toast.remove();
            }, 6000);
        }

        function cekAbsensiBaru(gabungan) {
            gabungan.forEach(function (baris) {
                const kunci = kunciAbsensi(baris);

                if (absensiTerlihat.has(kunci)) return;

                absensiTerlihat.add(kunci);

                // Seluruh daftar awal hanya didaftarkan, tidak diumumkan.
                // Kalau tidak, membuka halaman atau ganti kelas akan
                // memunculkan tumpukan toast untuk absensi yang sebenarnya
                // sudah lama tercatat.
                if (!siudahDaftarkanAwal) return;

                tampilkanToast(baris);
            });

            siudahDaftarkanAwal = true;
        }

        /**
         * Tandai seluruh daftar saat ini sebagai sudah pernah dilihat.
         *
         * Dipakai saat ganti kelas: absensi kelas yang baru dipilih bukan
         * yang baru masuk, jadi tidak boleh ikut diumumkan sebagai toast.
         */
        function daftarkanAwal(gabungan) {
            gabungan.forEach(function (baris) {
                absensiTerlihat.add(kunciAbsensi(baris));
            });

            siudahDaftarkanAwal = true;
        }

        function renderSeluruhHalaman() {
            const gabungan = gabungkanAbsensi();

            renderPemilihKelas();
            renderStatistik(presensiList);
            renderDonut(presensiList);
            renderFeed(gabungan);
            renderTabel(presensiList);

            cekAbsensiBaru(gabungan);

            const jam = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            setTeks('[data-terakhirSinkron]', 'Terakhir diperbarui pukul ' + jam);
        }

        // ============================================================
        // BANTUAN
        // ============================================================
        function persen(jumlah, total) {
            if (!total) return 0;
            return Math.round((jumlah / total) * 100);
        }

        function setTeks(selector, nilai) {
            document.querySelectorAll(selector).forEach(function (el) {
                el.textContent = nilai;
            });
        }

        function formatWaktu(value) {
            if (!value) return '-';

            const date = value instanceof Date ? value : new Date(value);
            if (isNaN(date.getTime())) return '-';

            return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        }

        // ============================================================
        // INIT
        // ============================================================
        initHalamanGuru(function () {
            daftarKelas = daftarKelasDiajarkan();

            kelasAktif = daftarKelas.indexOf(identitasGuru.kelasLengkap) !== -1
                ? identitasGuru.kelasLengkap
                : (daftarKelas[0] || null);

            if (kelasAktif !== null) {
                loadDataKelas(kelasAktif);
            }

            // Absensi dari storage ikut dibaca sejak awal supaya halaman
            // tidak terlihat kosong walau database belum sempat terisi.
            daftarkanAwal(gabungkanAbsensi());

            renderSeluruhHalaman();
            startRealtimeAutoRefresh();

            const tombolRefresh = document.getElementById('tombolRefresh');
            if (tombolRefresh) tombolRefresh.addEventListener('click', muatDataServer);

            const tombolAuto = document.getElementById('tombolAutoRefresh');
            if (tombolAuto) tombolAuto.addEventListener('click', toggleAutoRefresh);

            const filter = document.getElementById('filterStatusRealtime');
            if (filter) {
                filter.addEventListener('change', function () {
                    filterStatus = filter.value;
                    renderTabel(presensiList);
                });
            }

            // Halaman lain di tab yang sama menulis ke storage, jadi
            // perubahan di sana langsung terlihat tanpa menunggu polling.
            window.addEventListener('storage', function (event) {
                if (event.key !== STORAGE_KEY_DAFTAR_ABSEN && event.key !== STORAGE_KEY_SISWA_ABSEN) {
                    return;
                }

                const gabungan = gabungkanAbsensi();

                renderFeed(gabungan);
                cekAbsensiBaru(gabungan);
            });
        });
    </script>
@endpush
