@extends('layouts.app')

@section('title', 'Dashboard Guru · Hadirin.Web')

@php
    $pageTitle = 'Dashboard';
    $breadcrumbItems = [
        ['label' => 'Beranda'],
        ['label' => 'Dashboard Guru', 'current' => true],
    ];

    /*
    | Palet warna kartu. Mengikuti dashboard admin (cms/dashboard) supaya
    | kedua dashboard terasa satu sistem visual.
    */
    $tones = [
        'blue' => [
            'chip' => 'bg-gradient-to-br from-blue-500 to-sky-500 text-white shadow-md shadow-blue-500/30',
            'bar' => 'bg-blue-500',
            'accent' => 'from-blue-500 to-sky-400',
        ],
        'green' => [
            'chip' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/30',
            'bar' => 'bg-emerald-500',
            'accent' => 'from-emerald-500 to-teal-400',
        ],
        'amber' => [
            'chip' => 'bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-md shadow-amber-500/30',
            'bar' => 'bg-amber-500',
            'accent' => 'from-amber-500 to-orange-400',
        ],
        'red' => [
            'chip' => 'bg-gradient-to-br from-rose-500 to-red-500 text-white shadow-md shadow-rose-500/30',
            'bar' => 'bg-rose-500',
            'accent' => 'from-rose-500 to-red-400',
        ],
    ];

    /*
    | Kartu statistik. Nilai angka & badge tren diisi oleh JS dari `presensiList`,
    | jadi definisi label/ikon/warna tetap di Blade agar mudah disetel.
    */
    $statCards = [
        [
            'key' => 'total',
            'label' => 'Total Siswa',
            'icon' => 'fas fa-user-graduate',
            'tone' => 'blue',
            'spark' => [40, 55, 45, 70, 62, 85, 78],
        ],
        [
            'key' => 'hadir',
            'label' => 'Hadir Hari Ini',
            'icon' => 'fas fa-circle-check',
            'tone' => 'green',
            'spark' => [55, 50, 62, 58, 70, 66, 74],
        ],
        [
            'key' => 'izinSakit',
            'label' => 'Izin / Sakit',
            'icon' => 'fas fa-file-circle-exclamation',
            'tone' => 'amber',
            'spark' => [60, 72, 66, 60, 64, 52, 48],
        ],
        [
            'key' => 'alpha',
            'label' => 'Alpha',
            'icon' => 'fas fa-user-slash',
            'tone' => 'red',
            'spark' => [34, 26, 42, 30, 38, 24, 20],
        ],
    ];

    /*
    | Ringkasan kehadiran pada donut. Nilai & persentasenya diisi JS.
    | `Belum` mewakili siswa yang belum punya catatan absensi hari ini.
    */
    $rekapItems = [
        ['key' => 'hadir', 'label' => 'Hadir', 'dot' => 'bg-emerald-500', 'hex' => '#22c55e'],
        ['key' => 'izin', 'label' => 'Izin', 'dot' => 'bg-blue-500', 'hex' => '#3b82f6'],
        ['key' => 'sakit', 'label' => 'Sakit', 'dot' => 'bg-amber-500', 'hex' => '#f59e0b'],
        ['key' => 'alpha', 'label' => 'Alpha', 'dot' => 'bg-red-500', 'hex' => '#ef4444'],
        ['key' => 'belum', 'label' => 'Belum Absen', 'dot' => 'bg-slate-300', 'hex' => '#cbd5e1'],
    ];

    /*
    | Tren kehadiran 12 bulan. sourced dari controller bila someday sudah ada
    | (`$monthlyTrend ?? ...`), sementara ini masih placeholder dengan satuan
    | persen, sama seperti dashboard admin.
    */
    $monthlyTrend = $monthlyTrend ?? [
        ['month' => 'Jan', 'hadir' => 92, 'izin' => 8],
        ['month' => 'Feb', 'hadir' => 95, 'izin' => 5],
        ['month' => 'Mar', 'hadir' => 89, 'izin' => 11],
        ['month' => 'Apr', 'hadir' => 94, 'izin' => 6],
        ['month' => 'Mei', 'hadir' => 91, 'izin' => 9],
        ['month' => 'Jun', 'hadir' => 96, 'izin' => 4],
        ['month' => 'Jul', 'hadir' => 88, 'izin' => 12],
        ['month' => 'Agu', 'hadir' => 93, 'izin' => 7],
        ['month' => 'Sep', 'hadir' => 96, 'izin' => 4],
        ['month' => 'Okt', 'hadir' => 90, 'izin' => 10],
        ['month' => 'Nov', 'hadir' => 94, 'izin' => 6],
        ['month' => 'Des', 'hadir' => 92, 'izin' => 8],
    ];

    /*
    | Pintasan ke halaman guru lain. Halamannya masih kosong, jadi usefulness
    | kartu ini sekaligus became reminder-route saat nanti diisi.
    */
    $shortcuts = [
        [
            'label' => 'Progres Absensi',
            'description' => 'Pantau tren kehadiran siswa dari awal semester.',
            'route' => 'guru.progres',
            'icon' => 'fas fa-chart-line',
            'tone' => 'blue',
        ],
        [
            'label' => 'Kelola Data Kelas',
            'description' => 'Atur siswa, jadwal, dan مادة yang diampu.',
            'route' => 'guru.kelola',
            'icon' => 'fas fa-users-cog',
            'tone' => 'green',
        ],
        [
            'label' => 'Laporan Bulanan',
            'description' => 'Unduh rekap kehadiran siap cetak per bulan.',
            'route' => 'guru.laporan',
            'icon' => 'fas fa-file-alt',
            'tone' => 'amber',
        ],
        [
            'label' => 'Real-Time Monitoring',
            'description' => 'Lihat absensi masuk langsung saat siswa memindai QR.',
            'route' => 'guru.realtime',
            'icon' => 'fas fa-clock',
            'tone' => 'red',
        ],
    ];

    $actions = new Illuminate\Support\HtmlString(
        view('guru.partials.banner-actions')->render()
    );
@endphp

@section('konten')
    @include('guru.partials.banner', [
        'subtitle' => 'Ringkasan kehadiran kelas yang Anda ampu hari ini',
        'actions' => $actions,
    ])

    {{-- pemilih kelas: semua kartu di bawah mengikuti kelas yang aktif --}}
    <div class="mb-6 flex flex-col gap-3 rounded-xl border border-gray-100 bg-white px-5 py-4 shadow-sm lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                <i class="fas fa-chalkboard"></i>
            </span>
            <div>
                <h3 class="text-sm font-semibold text-gray-800">Kelas yang Diampu</h3>
                <p class="text-xs text-gray-500">Pilih kelas untuk melihat rincian absensinya</p>
            </div>
        </div>
        <div id="kelasChips" class="-mx-1 flex flex-wrap gap-2 px-1"></div>
    </div>

    {{-- muncul kalau guru ini belum punya kelas yang diampu --}}
    <div id="kosongKelas"
        class="mb-6 hidden rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
        <div class="flex items-start gap-3">
            <i class="fas fa-circle-info mt-0.5 shrink-0"></i>
            <div>
                <p class="font-semibold">Belum ada kelas yang diampu.</p>
                <p class="mt-0.5">
                    Hubungkan kelas dengan akun guru ini di dashboard admin, menu
                    <a href="{{ route('cms.classes') }}" class="font-semibold underline">Classes</a>,
                    kolom <span class="font-semibold">Guru Pengampu</span>. Siswa
                    kelas yang terhubung akan otomatis muncul di halaman ini.
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
                <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $tone['accent'] }}"></span>

                <div class="flex items-start justify-between">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl text-lg transition-transform duration-300 group-hover:scale-110 {{ $tone['chip'] }}">
                        <i class="{{ $card['icon'] }}"></i>
                    </div>
                    <span data-stat-badge="{{ $card['key'] }}"
                        class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-500">
                        <i class="fas fa-minus text-[9px]"></i>
                        <span>0%</span>
                    </span>
                </div>

                <div class="mt-4 flex items-end justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ $card['label'] }}</p>
                        <p class="mt-0.5 text-2xl font-bold tracking-tight text-gray-800"
                            data-stat-value="{{ $card['key'] }}">0</p>
                    </div>
                    <div class="flex h-9 items-end gap-[3px]" data-stat-spark="{{ $card['key'] }}" aria-hidden="true">
                        @foreach ($card['spark'] as $height)
                            <span
                                class="w-1.5 rounded-t-sm opacity-60 transition-all duration-300 group-hover:opacity-100 {{ $tone['bar'] }}"
                                style="height: {{ $height }}%"></span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- tren bulanan + ringkasan hari ini --}}
    <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="h-full rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                            <i class="fas fa-chart-column"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">Tren Kehadiran Bulanan</h3>
                            <p class="text-xs text-gray-500">Persentase hadir siswa sepanjang tahun</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 text-[11px] font-medium text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-sm bg-gradient-to-t from-emerald-500 to-emerald-400"></span> Hadir
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-sm bg-gradient-to-t from-amber-500 to-amber-400"></span> Izin / Sakit
                        </span>
                    </div>
                </div>

                <div class="px-6 py-5">
                    <div class="chart-container">
                        <canvas id="trenChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="h-full rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-emerald-50 text-emerald-600">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">Ringkasan Hari Ini</h3>
                            <p class="text-xs text-gray-500" data-ringkasanKelas>-</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-center px-6 py-6">
                    <div class="relative h-36 w-36">
                        <div id="donutKehadiran" class="h-36 w-36 rounded-full transition-all duration-500"
                            style="background: conic-gradient(#e5e7eb 0 100%);"></div>
                        <div class="absolute inset-[14px] flex flex-col items-center justify-center rounded-full bg-white">
                            <span class="text-3xl font-bold tracking-tight text-gray-800" data-donut-persen>0%</span>
                            <span class="text-[11px] font-medium text-gray-500">Hadir</span>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-gray-100 border-t border-gray-100">
                    @foreach ($rekapItems as $row)
                        <div class="flex items-center justify-between px-6 py-3">
                            <span class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="h-2.5 w-2.5 rounded-full {{ $row['dot'] }}"></span>
                                {{ $row['label'] }}
                            </span>
                            <span class="text-sm font-semibold text-gray-800">
                                <span data-rekap-value="{{ $row['key'] }}">0</span>
                                <span class="font-normal text-gray-400">siswa</span>
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 px-6 py-3 text-center text-xs text-gray-500">
                    Total <span class="font-semibold text-gray-800" data-rekap-total>0</span> siswa terpantau hari ini
                </div>
            </div>
        </div>
    </div>

    {{-- daftar absensi kelas aktif + progres per kelas --}}
    <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="flex h-full flex-col rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-violet-50 text-violet-600">
                            <i class="fas fa-list-check"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">Daftar Absensi Siswa</h3>
                            <p class="text-xs text-gray-500" data-tabelSubtitle>-</p>
                        </div>
                    </div>
                    <span id="badgeAbsensiQr"
                        class="hidden items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-600">
                        <i class="fas fa-circle-check"></i>
                        <span data-absensiQrCount>0</span> absensi QR masuk
                    </span>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Siswa</th>
                                <th class="px-4 py-3 font-semibold">Waktu</th>
                                <th class="px-4 py-3 font-semibold">Metode</th>
                                <th class="px-4 py-3 text-right font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody id="tabelAbsensi" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-3 text-center">
                    <a href="{{ route('guru.realtime') }}"
                        class="text-xs font-semibold text-indigo-600 transition-colors hover:text-indigo-700">
                        Lihat riwayat lengkap <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>

        <div>
            <div class="flex h-full flex-col rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-amber-50 text-amber-600">
                            <i class="fas fa-layer-group"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">Progres per Kelas</h3>
                            <p class="text-xs text-gray-500">Persentase siswa hadir hari ini</p>
                        </div>
                    </div>
                </div>

                <div id="progresKelas" class="flex-1 space-y-4 px-6 py-5"></div>
            </div>
        </div>
    </div>

    {{-- pintasan halaman guru lainnya --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($shortcuts as $shortcut)
            @php $tone = $tones[$shortcut['tone']]; @endphp
            <a href="{{ route($shortcut['route']) }}"
                class="group flex items-start gap-4 rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-indigo-100 hover:shadow-md">
                <span
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-lg transition-transform duration-300 group-hover:scale-110 {{ $tone['chip'] }}">
                    <i class="{{ $shortcut['icon'] }}"></i>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="flex items-center justify-between gap-2">
                        <span class="truncate text-sm font-semibold text-gray-800">{{ $shortcut['label'] }}</span>
                        <i
                            class="fas fa-arrow-up-right-from-square shrink-0 text-[10px] text-gray-300 transition-colors group-hover:text-indigo-500"></i>
                    </span>
                    <span class="mt-1 block text-xs leading-relaxed text-gray-500">{{ $shortcut['description'] }}</span>
                </span>
            </a>
        @endforeach
    </div>
@endsection

@push('styles')
    @include('guru.partials.styles')

    <style>
        /* transisi halus saat nilai kartu statistik berubah */
        [data-stat-value] {
            transition: color 0.3s ease;
        }

        /* baris tabel absensi */
        .absensi-row {
            transition: background-color 0.2s ease;
        }

        .absensi-row:hover {
            background-color: #f9fafb;
        }

        /* chip pemilih kelas */
        .kelas-chip {
            transition: all 0.2s ease;
        }
    </style>
@endpush

@push('scripts')
    {{-- Data kelas & siswa dari database, dibaca data-guru.blade.php --}}
    <script>
        window.HADIRIN_GURU = @json($guruData ?? null);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @include('guru.partials.data-guru')

    <script>
        // ============================================================
        // DASHBOARD GURU
        // ============================================================
        // Semua angka di halaman ini diturunkan dari `presensiList`, yang
        // datanya berasal dari database yang sama dengan dashboard admin.

        let kelasAktif = null;
        let daftarKelas = [];
        let trenChart = null;

        const DATA_TREN_BULANAN = @json($monthlyTrend);

        const BADGE_STATUS = {
            'Hadir': 'badge-hadir',
            'Izin': 'badge-izin',
            'Sakit': 'badge-sakit',
            'Alpha': 'badge-alpha'
        };

        const BADGE_METODE = {
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

        // ============================================================
        // SELECTOR KELAS
        // ============================================================
        function daftarKelasDiajarkan() {
            const dariServer = daftarKelasServer();

            if (dariServer.length > 0) return dariServer;

            return Object.keys(globalData.siswaPerKelas || {});
        }

        function renderPemilihKelas() {
            const container = document.getElementById('kelasChips');
            const kosong = document.getElementById('kosongKelas');

            if (kosong) kosong.classList.toggle('hidden', daftarKelas.length > 0);
            if (!container) return;

            container.innerHTML = '';

            if (daftarKelas.length === 0) return;

            daftarKelas.forEach(function (nama) {
                const siswa = (globalData.siswaPerKelas || {})[nama] || [];
                const aktif = nama === kelasAktif;

                const chip = document.createElement('button');
                chip.type = 'button';
                chip.dataset.kelas = nama;
                chip.className = 'kelas-chip inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-xs font-semibold ring-1 ' +
                    (aktif
                        ? 'bg-indigo-600 text-white ring-indigo-600 shadow-md shadow-indigo-500/30'
                        : 'bg-white text-gray-600 ring-gray-200 hover:bg-indigo-50 hover:text-indigo-600 hover:ring-indigo-200');
                chip.innerHTML = '<span>' + nama + '</span>' +
                    '<span class="rounded-full px-1.5 py-0.5 text-[10px] ' +
                    (aktif ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500') + '">' +
                    siswa.length + '</span>';

                chip.addEventListener('click', function () {
                    kelasAktif = nama;
                    loadDataKelas(nama);
                    renderSeluruhHalaman();
                });

                container.appendChild(chip);
            });
        }

        // ============================================================
        // KARTU STATISTIK
        // ============================================================
        function setTeks(selector, nilai) {
            const el = document.querySelector(selector);
            if (el) el.textContent = nilai;
        }

        function renderStatistik(list) {
            const stat = hitungStatAbsensi(list);
            const total = list.length;
            const izinSakit = stat.izin + stat.sakit;

            const nilai = {
                total: total,
                hadir: stat.hadir,
                izinSakit: izinSakit,
                alpha: stat.alpha
            };

            Object.keys(nilai).forEach(function (key) {
                setTeks('[data-stat-value="' + key + '"]', nilai[key]);
            });

            renderBadgeStatistik('total', total, 0, 'siswa');
            renderBadgeStatistik('hadir', stat.hadir, total);
            renderBadgeStatistik('izinSakit', izinSakit, total);
            renderBadgeStatistik('alpha', stat.alpha, total);

            // sparkline Mengecil mengikuti nilai masing-masing kartu
            renderSpark('total', total > 0 ? 100 : 0);
            renderSpark('hadir', persen(stat.hadir, total));
            renderSpark('izinSakit', persen(izinSakit, total));
            renderSpark('alpha', persen(stat.alpha, total));
        }

        function renderBadgeStatistik(key, jumlah, total, teksBawaan) {
            const badge = document.querySelector('[data-stat-badge="' + key + '"]');
            if (!badge) return;

            const icon = badge.querySelector('i');
            const label = badge.querySelector('span');

            if (teksBawaan !== undefined) {
                badge.className = 'inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-600';
                label.textContent = teksBawaan;
                return;
            }

            const nilai = persen(jumlah, total);

            if (nilai >= 75) {
                badge.className = 'inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-semibold text-green-600';
                icon.className = 'fas fa-arrow-up text-[9px]';
            } else if (nilai >= 40) {
                badge.className = 'inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-600';
                icon.className = 'fas fa-minus text-[9px]';
            } else {
                badge.className = 'inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-600';
                icon.className = 'fas fa-arrow-down text-[9px]';
            }

            label.textContent = nilai + '%';
        }

        const POLA_GELOMBANG = [0.82, 0.95, 0.7, 1, 0.88, 0.98, 0.78];

        function renderSpark(key, nilaiPersen) {
            const wrapper = document.querySelector('[data-stat-spark="' + key + '"]');
            if (!wrapper) return;

            const bars = wrapper.querySelectorAll('span');

            Array.prototype.forEach.call(bars, function (bar, index) {
                const tinggi = Math.max(12, Math.min(100, nilaiPersen * POLA_GELOMBANG[index % POLA_GELOMBANG.length]));
                bar.style.height = tinggi + '%';
            });
        }

        // ============================================================
        // DONUT RINGKASAN
        // ============================================================
        function renderDonut(list) {
            const stat = hitungStatAbsensi(list);
            const total = list.length;
            const donut = document.getElementById('donutKehadiran');

            Object.keys(WARNA_DONUT).forEach(function (key) {
                setTeks('[data-rekap-value="' + key + '"]', stat[key]);
            });

            setTeks('[data-rekap-total]', total);
            setTeks('[data-donut-persen]', persen(stat.hadir, total) + '%');
            setTeks('[data-ringkasanKelas]', kelasAktif + ' · hari ini');

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

        // ============================================================
        // TABEL ABSENSI
        // ============================================================
        function renderTabelAbsensi(list) {
            const tbody = document.getElementById('tabelAbsensi');
            if (!tbody) return;

            setTeks('[data-tabelSubtitle]', kelasAktif + ' · ' + list.length + ' siswa tercatat');

            tbody.innerHTML = '';

            if (list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-10 text-center text-sm text-gray-400">' +
                    '<i class="fas fa-inbox mb-2 block text-2xl text-gray-300"></i>' +
                    'Belum ada data absensi untuk kelas ini.</td></tr>';
                return;
            }

            list.forEach(function (row) {
                const inisial = (row.nama || '?').charAt(0).toUpperCase();

                const tr = document.createElement('tr');
                tr.className = 'absensi-row';
                tr.innerHTML =
                    '<td class="px-6 py-3">' +
                        '<div class="flex items-center gap-3">' +
                            '<span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-600">' +
                                inisial + '</span>' +
                            '<span class="min-w-0">' +
                                '<span class="block truncate text-sm font-semibold text-gray-800">' + (row.nama || '-') + '</span>' +
                                '<span class="block text-xs text-gray-400">NIS ' + (row.nis || '-') + '</span>' +
                            '</span>' +
                        '</div>' +
                    '</td>' +
                    '<td class="px-4 py-3 text-sm text-gray-600">' + formatWaktu(row.waktu) + '</td>' +
                    '<td class="px-4 py-3"><span class="' +
                        (row.metode ? (BADGE_METODE[row.metode] || 'badge-id') : 'badge-belum') + '">' +
                        (row.metode || 'Belum absen') + '</span></td>' +
                    '<td class="px-4 py-3 text-right"><span class="' +
                        (row.status ? (BADGE_STATUS[row.status] || 'badge-alpha') : 'badge-belum') + '">' +
                        (row.status || 'Belum Absen') + '</span></td>';

                tbody.appendChild(tr);
            });
        }

        function renderBadgeAbsensiQr() {
            const badge = document.getElementById('badgeAbsensiQr');
            if (!badge) return;

            const data = loadAllAbsensiSiswa();
            const hariIni = data.filter(function (d) {
                return d.timestamp && new Date(d.timestamp).toDateString() === new Date().toDateString();
            });

            if (hariIni.length === 0) {
                badge.classList.add('hidden');
                badge.classList.remove('inline-flex');
                return;
            }

            badge.classList.remove('hidden');
            badge.classList.add('inline-flex');
            setTeks('[data-absensiQrCount]', hariIni.length);
        }

        function formatWaktu(value) {
            if (!value) return '-';

            const date = value instanceof Date ? value : new Date(value);
            if (isNaN(date.getTime())) return '-';

            return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        }

        // ============================================================
        // PROGRES PER KELAS
        // ============================================================
        function renderProgresKelas() {
            const container = document.getElementById('progresKelas');
            if (!container) return;

            container.innerHTML = '';

            if (daftarKelas.length === 0) {
                container.innerHTML = '<p class="py-6 text-center text-sm text-gray-400">Belum ada kelas yang diampu.</p>';
                return;
            }

            daftarKelas.forEach(function (nama) {
                const list = loadDataKelas(nama);
                const stat = hitungStatAbsensi(list);
                const total = list.length;
                const nilai = persen(stat.hadir, total);

                const warna = nilai >= 75 ? 'bg-emerald-500' : (nilai >= 40 ? 'bg-amber-500' : 'bg-rose-500');
                const aktif = nama === kelasAktif;

                const baris = document.createElement('div');
                baris.className = 'cursor-pointer';
                baris.innerHTML =
                    '<div class="mb-1.5 flex items-center justify-between gap-2">' +
                        '<span class="truncate text-sm font-semibold ' + (aktif ? 'text-indigo-600' : 'text-gray-700') + '">' +
                            nama + '</span>' +
                        '<span class="shrink-0 text-xs text-gray-500">' + stat.hadir + '/' + total +
                            ' <span class="font-semibold text-gray-800">' + nilai + '%</span></span>' +
                    '</div>' +
                    '<div class="progress-bar"><div class="progress-fill ' + warna +
                        '" style="width: ' + nilai + '%"></div></div>';

                baris.addEventListener('click', function () {
                    kelasAktif = nama;
                    loadDataKelas(nama);
                    renderSeluruhHalaman();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });

                container.appendChild(baris);
            });

            // kembalikan daftar kelas aktif karena loadDataKelas di atas
            // mengganti isi presensiList untuk setiap kelas.
            if (kelasAktif) loadDataKelas(kelasAktif);
        }

        // ============================================================
        // TREN KEHADIRAN BULANAN
        // ============================================================
        function initTrenChart() {
            const canvas = document.getElementById('trenChart');
            if (!canvas || typeof Chart === 'undefined') return;

            trenChart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: DATA_TREN_BULANAN.map(function (bar) { return bar.month; }),
                    datasets: [
                        {
                            label: 'Hadir',
                            data: DATA_TREN_BULANAN.map(function (bar) { return bar.hadir; }),
                            backgroundColor: '#10b981',
                            borderRadius: { topLeft: 0, topRight: 0, bottomLeft: 6, bottomRight: 6 },
                            borderSkipped: false
                        },
                        {
                            label: 'Izin / Sakit',
                            data: DATA_TREN_BULANAN.map(function (bar) { return bar.izin; }),
                            backgroundColor: '#f59e0b',
                            borderRadius: { topLeft: 6, topRight: 6, bottomLeft: 0, bottomRight: 0 },
                            borderSkipped: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    return ctx.dataset.label + ': ' + ctx.parsed.y + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false }, ticks: { font: { size: 11 } } },
                        y: {
                            stacked: true,
                            max: 100,
                            grid: { color: '#f1f5f9' },
                            ticks: { font: { size: 11 }, callback: function (v) { return v + '%'; } }
                        }
                    }
                }
            });
        }

        // ============================================================
        // RENDER SEBUAH HALAMAN
        // ============================================================
        function persen(jumlah, total) {
            if (!total) return 0;
            return Math.round((jumlah / total) * 100);
        }

        function renderSeluruhHalaman() {
            renderPemilihKelas();
            renderStatistik(presensiList);
            renderDonut(presensiList);
            renderTabelAbsensi(presensiList);
            renderBadgeAbsensiQr();
            renderProgresKelas();
            renderTombolSesi();
        }

        function renderTombolSesi() {
            const tombol = document.getElementById('tombolMulaiSesi');
            const label = document.getElementById('labelMulaiSesi');

            if (!tombol || !label) return;

            const total = presensiList.length;
            const sudah = hitungStatAbsensi(presensiList).hadir;

            label.textContent = total > 0 ? 'Sesi Berjalan (' + sudah + '/' + total + ')' : 'Mulai Sesi Absen';
        }

        initHalamanGuru(function () {
            daftarKelas = daftarKelasDiajarkan();

            // Kalau kelas pada identitas tidak ada di daftar, pakai yang pertama.
            kelasAktif = daftarKelas.indexOf(identitasGuru.kelasLengkap) !== -1
                ? identitasGuru.kelasLengkap
                : (daftarKelas[0] || null);

            if (kelasAktif) loadDataKelas(kelasAktif);

            initTrenChart();
            renderSeluruhHalaman();

            const tombol = document.getElementById('tombolMulaiSesi');
            if (tombol) {
                // Sesi absensi belum butuh server, jadi tombol disembunyikan
                // selama belum ada kelas yang diampu.
                tombol.classList.toggle('hidden', kelasAktif === null);

                tombol.addEventListener('click', function () {
                    alert('Pembukaan sesi absensi untuk ' + kelasAktif + ' belum diimplementasikan.');
                });
            }
        });
    </script>
@endpush
