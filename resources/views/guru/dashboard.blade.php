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
    | kedua dashboard terasa satu sistem visual. `value` dipakai untuk warna
    | angka, `bar` untuk sparkline, `chip` untuk kotak ikon, `accent` untuk
    | garis atas kartu.
    */
    $tones = [
        'blue' => [
            'chip' => 'bg-gradient-to-br from-blue-500 to-sky-500 text-white shadow-md shadow-blue-500/30',
            'value' => 'text-blue-600',
            'bar' => 'bg-blue-500',
            'accent' => 'from-blue-500 to-sky-400',
        ],
        'green' => [
            'chip' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/30',
            'value' => 'text-emerald-600',
            'bar' => 'bg-emerald-500',
            'accent' => 'from-emerald-500 to-teal-400',
        ],
        'amber' => [
            'chip' => 'bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-md shadow-amber-500/30',
            'value' => 'text-amber-600',
            'bar' => 'bg-amber-500',
            'accent' => 'from-amber-500 to-orange-400',
        ],
        'red' => [
            'chip' => 'bg-gradient-to-br from-rose-500 to-red-500 text-white shadow-md shadow-rose-500/30',
            'value' => 'text-rose-600',
            'bar' => 'bg-rose-500',
            'accent' => 'from-rose-500 to-rose-400',
        ],
        'slate' => [
            'chip' => 'bg-gradient-to-br from-slate-500 to-slate-600 text-white shadow-md shadow-slate-500/30',
            'value' => 'text-slate-600',
            'bar' => 'bg-slate-400',
            'accent' => 'from-slate-500 to-slate-400',
        ],
    ];

    /*
    | Kartu statistik. Nilai angka, badge persentase, keterangan, dan sparkline
    | semuanya diisi JS dari `presensiList`, jadi definisi label/ikon/warna
    | tetap di Blade agar mudah disetel.
    |
    | `alpha` sengaja tetap jadi kartu sendiri walaupun isinya bisa 0. Guru
    | butuh melihat angka nol itu secara eksplisit, bukan lewat tidak
    | tampilnya kartu.
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
            'key' => 'belum',
            'label' => 'Belum Absen',
            'icon' => 'fas fa-hourglass-half',
            'tone' => 'slate',
            'spark' => [50, 46, 40, 52, 44, 38, 34],
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
    | `belum` mewakili siswa yang belum punya catatan absensi hari ini.
    */
    $rekapItems = [
        ['key' => 'hadir', 'label' => 'Hadir', 'dot' => 'bg-emerald-500', 'hex' => '#22c55e'],
        ['key' => 'izin', 'label' => 'Izin', 'dot' => 'bg-blue-500', 'hex' => '#3b82f6'],
        ['key' => 'sakit', 'label' => 'Sakit', 'dot' => 'bg-amber-500', 'hex' => '#f59e0b'],
        ['key' => 'alpha', 'label' => 'Alpa', 'dot' => 'bg-red-500', 'hex' => '#ef4444'],
        ['key' => 'belum', 'label' => 'Belum Absen', 'dot' => 'bg-slate-300', 'hex' => '#cbd5e1'],
    ];

    /*
    | Tren kehadiran 12 bulan, dikirim controller dari tabel `absensis`.
    |
    | Angkanya dari database, jadi kalau belum ada absensi, `$trenBulanan`
    | berisi deret yang seluruhnya nol dan `total`-nya 0.
    */
    $tren = $trenBulanan['tren'] ?? [];
    $punyaTren = ($trenBulanan['total'] ?? 0) > 0;

    $actions = new Illuminate\Support\HtmlString(
        view('guru.partials.banner-actions')->render()
    );
@endphp

@section('konten')
    @include('guru.partials.banner', [
        'subtitle' => 'Ringkasan dan pemantauan kehadiran kelas yang Anda ampu hari ini',
        'actions' => $actions,
    ])

    {{-- pemilih kelas + kendali penyegaran otomatis --}}
    <div class="mb-6 flex flex-col gap-4 rounded-xl border border-gray-100 bg-white px-5 py-4 shadow-sm lg:flex-row lg:items-center lg:justify-between">
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
            <div id="kelasChips" class="-mx-1 flex flex-wrap gap-2 px-1"></div>

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
    <div id="kosongKelas"
        class="mb-6 hidden rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
        <div class="flex items-start gap-3">
            <i class="fas fa-circle-info mt-0.5 shrink-0"></i>
            <div>
                <p class="font-semibold">Belum ada kelas yang diampu.</p>
                <p class="mt-0.5">
                    Hubungkan kelas dengan akun Anda di dashboard admin, menu
                    <a href="{{ route('cms.classes') }}" class="font-semibold underline">Classes</a>,
                    kolom <span class="font-semibold">Guru Pengampu</span>. Siswa
                    kelas yang terhubung akan otomatis muncul di halaman ini.
                </p>
            </div>
        </div>
    </div>

    {{-- kartu statistik --}}
    <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
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
                    <span data-persenStat="{{ $card['key'] }}"
                        class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-500">
                        0%
                    </span>
                </div>

                <div class="mt-4 flex items-end justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-gray-500">{{ $card['label'] }}</p>
                        <p class="mt-0.5 text-2xl font-bold tracking-tight {{ $tone['value'] }}"
                            data-nilaiStat="{{ $card['key'] }}">0</p>
                    </div>
                    <div class="flex h-9 shrink-0 items-end gap-[3px]" data-stat-spark="{{ $card['key'] }}" aria-hidden="true">
                        @foreach ($card['spark'] as $height)
                            <span
                                class="w-1.5 rounded-t-sm opacity-60 transition-all duration-300 group-hover:opacity-100 {{ $tone['bar'] }}"
                                style="height: {{ $height }}%"></span>
                        @endforeach
                    </div>
                </div>

                <p class="mt-1 truncate text-xs text-gray-400" data-keteranganStat="{{ $card['key'] }}">-</p>
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
                            <p class="text-xs text-gray-500">
                                @if ($punyaTren)
                                    Persentase hadir siswa sepanjang 12 bulan terakhir
                                @else
                                    Menunggu absensi pertama
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 text-[11px] font-medium text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-sm bg-emerald-500"></span> Hadir
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-sm bg-blue-500"></span> Izin
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-sm bg-amber-500"></span> Sakit
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-sm bg-rose-500"></span> Alpa
                        </span>
                    </div>
                </div>

                <div class="px-6 py-5">
                    @if ($punyaTren)
                        <div class="chart-container">
                            <canvas id="trenChart"></canvas>
                        </div>
                    @else
                        {{-- Batang kosong 12 bulan dengan label tetap tampil, supaya
                             kerangka grafiknya terlihat tapi tidak mengarang
                             persentase seperti sebelumnya. --}}
                        <div class="chart-container flex items-end gap-2 pb-6">
                            @foreach ($tren as $bar)
                                <div class="flex flex-1 flex-col items-center gap-2">
                                    <div class="flex w-full flex-1 items-end">
                                        <div class="w-full rounded-t-md border border-dashed border-gray-200 bg-gray-50"
                                            style="height: 4px"
                                            title="{{ $bar['label'] }}: belum ada catatan"></div>
                                    </div>
                                    <span class="text-[11px] text-gray-400">{{ $bar['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="-mt-2 text-center text-sm text-gray-400">
                            <i class="fas fa-chart-simple mb-2 block text-2xl text-gray-300"></i>
                            Belum ada catatan absensi untuk kelas yang Anda ampu.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <div class="flex h-full flex-col rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-emerald-50 text-emerald-600">
                            <i class="fas fa-gauge-high"></i>
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
                    <span data-terakhirSinkron>-</span>
                </div>
            </div>
        </div>
    </div>

    {{-- feed absensi masuk + progres per kelas --}}
    <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
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

                <div id="feedAbsensi" class="flex-1 divide-y divide-gray-100 overflow-y-auto"></div>
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

    {{-- daftar absensi kelas aktif --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-violet-50 text-violet-600">
                    <i class="fas fa-list-check"></i>
                </span>
                <div>
                    <h3 class="font-semibold text-gray-800">Daftar Absensi Siswa</h3>
                    <p class="text-xs text-gray-500" data-tabelSubtitle>-</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <span id="badgeAbsensiQr"
                    class="hidden items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-600">
                    <i class="fas fa-circle-check"></i>
                    <span data-absensiQrCount>0</span> absensi masuk
                </span>

                <label for="filterStatus" class="sr-only">Saring status</label>
                <select id="filterStatus"
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
                <tbody id="tabelAbsensi" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-6 py-3">
            <span class="text-xs text-gray-500">
                <span data-jumlahTampil>0</span> dari <span data-jumlahTotal>0</span> siswa ditampilkan
            </span>
            <a href="{{ route('guru.laporan') }}"
                class="text-xs font-semibold text-indigo-600 transition-colors hover:text-indigo-700">
                Laporan lengkap <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>

    {{-- wadah toast untuk absensi yang baru masuk --}}
    <div id="toastAbsensi" class="pointer-events-none fixed right-5 top-5 z-50 flex w-80 flex-col gap-2"></div>
@endsection

@push('styles')
    @include('guru.partials.styles')

    <style>
        /* transisi halus saat nilai kartu statistik berubah */
        [data-nilaiStat] {
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @include('guru.partials.data-guru')

    <script>
        // ============================================================
        // DASHBOARD GURU (RINGKASAN + PEMANTAUAN REAL-TIME)
        // ============================================================
        // Halaman ini dulu terbagi dua: dashboard (ringkasan hari ini) dan
        // real-time monitoring (pemantauan langsung). Keduanya membaca data
        // yang sama, punya pemilih kelas yang sama, dan tabel siswa yang
        // hampir identik, jadi sekarang digabung ke satu halaman.
        //
        // Semua angka diturunkan dari `presensiList`, yang datanya berasal dari
        // database yang sama dengan dashboard admin dan disegarkan berkala
        // lewat endpoint JSON halaman ini sendiri.
        //
        // Dua sumber data dipakai berdampingan:
        //
        // 1. **Server (database)** — `guru.dashboard` sebagai JSON, yang isinya
        //    sama dengan `window.HADIRIN_GURU`. Dipakai sebagai sumber
        //    kebenaran dan dimuat ulang tiap polling.
        // 2. **localStorage** — `daftar_absen`/`siswa_absen` yang diisi halaman
        //    absensi siswa. Langsung dipakai supaya catatan baru terlihat
        //    seketika, sebelum round-trip ke database selesai.

        const ENDPOINT_REFRESH = @json(route('guru.dashboard'));
        const INTERVAL_POLLING = 15000;
        const BATAS_FEED = 12;

        const DATA_TREN_BULANAN = @json($tren);

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

        let kelasAktif = null;
        let daftarKelas = [];
        let filterStatus = 'semua';
        let autoRefresh = true;
        let timerPolling = null;
        let trenChart = null;

        // Kunci absensi yang sudah pernah ditampilkan, dipakai untuk
        // membedakan "baris baru" dari sekadar hasil render ulang.
        const absensiTerlihat = new Set();

        // Menandai bahwa daftar awal sudah didaftarkan, sehingga render
        // berikutnya benar-benar bisa membedakan absensi baru dari data lama
        // yang baru pertama kali terlihat.
        let sudahDaftarkanAwal = false;

        // ============================================================
        // BANTUAN
        // ============================================================
        function setTeks(selector, nilai) {
            document.querySelectorAll(selector).forEach(function (el) {
                el.textContent = nilai;
            });
        }

        function persen(jumlah, total) {
            if (!total) return 0;
            return Math.round((jumlah / total) * 100);
        }

        function formatWaktu(value) {
            if (!value) return '-';

            const date = value instanceof Date ? value : new Date(value);
            if (isNaN(date.getTime())) return '-';

            return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        }

        // ============================================================
        // SELECTOR KELAS
        // ============================================================
        function daftarKelasDiajarkan() {
            // Hanya kelas dari database. Kalau guru belum diampu kelas,
            // daftarnya kosong dan halaman menampilkan kondisi kosong.
            return daftarKelasServer();
        }

        function renderPemilihKelas() {
            const container = document.getElementById('kelasChips');
            const kosong = document.getElementById('kosongKelas');

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
                chip.dataset.kelas = nama;
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

        // ============================================================
        // KARTU STATISTIK
        // ============================================================
        const POLA_GELOMBANG = [0.82, 0.95, 0.7, 1, 0.88, 0.98, 0.78];

        function renderStatistik(list) {
            const stat = hitungStatAbsensi(list);
            const total = list.length;
            const izinSakit = stat.izin + stat.sakit;

            const nilai = {
                total: total,
                hadir: stat.hadir,
                izinSakit: izinSakit,
                belum: stat.belum,
                alpha: stat.alpha
            };

            const keterangan = {
                total: kelasAktif === null ? '-' : 'siswa di ' + kelasAktif,
                hadir: 'dari ' + total + ' siswa',
                izinSakit: izinSakit === 0 ? 'tidak ada' : izinSakit + ' siswa tercatat',
                belum: total > 0 ? Math.round((stat.belum / total) * 100) + '% belum absen' : '-',
                alpha: total > 0 ? Math.round((stat.alpha / total) * 100) + '% dari total siswa' : '-'
            };

            Object.keys(nilai).forEach(function (key) {
                setTeks('[data-nilaiStat="' + key + '"]', nilai[key]);
                setTeks('[data-persenStat="' + key + '"]', persen(nilai[key], total) + '%');
                setTeks('[data-keteranganStat="' + key + '"]', keterangan[key]);
            });

            renderBadgeStatistik('total', total);
            renderBadgeStatistik('hadir', stat.hadir, total);
            renderBadgeStatistik('izinSakit', izinSakit, total);
            renderBadgeStatistik('belum', stat.belum, total);
            renderBadgeStatistik('alpha', stat.alpha, total);

            // Sparkline mengecil mengikuti nilai masing-masing kartu
            renderSpark('total', total > 0 ? 100 : 0);
            renderSpark('hadir', persen(stat.hadir, total));
            renderSpark('izinSakit', persen(izinSakit, total));
            renderSpark('belum', persen(stat.belum, total));
            renderSpark('alpha', persen(stat.alpha, total));
        }

        function renderBadgeStatistik(key, jumlah, total) {
            const badge = document.querySelector('[data-persenStat="' + key + '"]');
            if (!badge) return;

            // Kartu total siswa tidak punya rasio yang masuk akal, jadi
            // menampilkan kata "siswa" sebagai gantinya.
            if (total === undefined) {
                badge.className = 'inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-600';
                badge.textContent = 'siswa';
                return;
            }

            const nilai = persen(jumlah, total);

            if (nilai >= 75) {
                badge.className = 'inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-semibold text-green-600';
            } else if (nilai >= 40) {
                badge.className = 'inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-600';
            } else {
                badge.className = 'inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-600';
            }

            badge.textContent = nilai + '%';
        }

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

            setTeks('[data-donut-persen]', persen(stat.hadir, total) + '%');
            setTeks('[data-ringkasanKelas]', kelasAktif === null
                ? 'Belum ada kelas yang diampu'
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

        // ============================================================
        // TABEL ABSENSI
        // ============================================================
        function renderTabelAbsensi(list) {
            const tbody = document.getElementById('tabelAbsensi');
            const visible = list.filter(function (baris) {
                if (filterStatus === 'semua') return true;
                if (filterStatus === 'Belum') return !baris.status;

                return baris.status === filterStatus;
            });

            setTeks('[data-tabelSubtitle]', kelasAktif === null
                ? 'Pilih kelas terlebih dahulu'
                : kelasAktif + ' · ' + visible.length + ' dari ' + list.length + ' siswa');

            setTeks('[data-kelasAktif]', kelasAktif === null
                ? 'Belum ada kelas yang diampu'
                : 'Memantau ' + kelasAktif);

            setTeks('[data-jumlahTampil]', visible.length);
            setTeks('[data-jumlahTotal]', list.length);

            renderBadgeAbsensiMasuk();

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
                const badge = baris.status ? (BADGE_STATUS[baris.status] || 'badge-alpha') : 'badge-belum';
                const metode = baris.metode ? (BADGE_METODE[baris.metode] || 'badge-id') : 'badge-belum';

                const tr = document.createElement('tr');
                tr.className = 'absensi-row';
                tr.innerHTML =
                    '<td class="px-6 py-3">' +
                        '<div class="flex items-center gap-3">' +
                            '<span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-600">' +
                                inisial + '</span>' +
                            '<span class="min-w-0">' +
                                '<span class="block max-w-[14rem] truncate text-sm font-semibold text-gray-800">' + (baris.nama || '-') + '</span>' +
                                '<span class="block text-xs text-gray-400">NISN ' + (baris.nis || '-') + '</span>' +
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

                tbody.appendChild(tr);
            });
        }

        /**
         * Badge jumlah absensi yang masuk hari ini dari perangkat siswa.
         *
         * Dihitung dari localStorage, bukan dari database, karena tujuannya
         * memberi tahu guru ada aktivitas absen dari perangkat siswa yang
         * belum sempat tersimpan ke database. Catatan yang sudah tersimpan
         * sudah terlihat dari angka pada kartu dan dari tabel di bawah.
         */
        function renderBadgeAbsensiMasuk() {
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

        // ============================================================
        // FEED ABSENSI MASUK
        // ============================================================
        function renderFeed(gabungan) {
            const container = document.getElementById('feedAbsensi');

            if (!container) return;

            container.innerHTML = '';

            if (gabungan.length === 0) {
                container.innerHTML =
                    '<div class="flex h-full min-h-[16rem] flex-col items-center justify-center px-6 py-10 text-center">' +
                    '<span class="mb-3 grid h-14 w-14 place-items-center rounded-full bg-gray-100 text-gray-400">' +
                    '<i class="fas fa-inbox text-xl"></i></span>' +
                    '<p class="text-sm font-semibold text-gray-700">Belum ada absensi masuk</p>' +
                    '<p class="mt-1 max-w-xs text-xs leading-relaxed text-gray-500">' +
                    'Siswa yang absen di kelas akan langsung muncul di sini tanpa perlu reload halaman.' +
                    '</p></div>';
                return;
            }

            gabungan.slice(0, BATAS_FEED).forEach(function (baris) {
                const inisial = (baris.nama || '?').charAt(0).toUpperCase();
                const badge = baris.status ? (BADGE_STATUS[baris.status] || 'badge-belum') : 'badge-belum';

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

                container.appendChild(item);
            });
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

                    absensiTerlihat.clear();
                    daftarkanAwal(gabungkanAbsensi());

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
        // SUMBER DATA GABUNGAN
        // ============================================================
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
         * Catatan dari database selalu punya `waktu` presisi, sedangkan catatan
         * dari storage bisa lebih kasar, jadi nama + kelas + waktu dipakai
         * bersama agar keduanya bisa dibandingkan.
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
                console.warn('Gagal menyegarkan data absensi:', error);
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
        // ABSENSI YANG BARU MASUK
        // ============================================================
        function tampilkanToast(baris) {
            const wadah = document.getElementById('toastAbsensi');
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
                if (!sudahDaftarkanAwal) return;

                tampilkanToast(baris);
            });

            sudahDaftarkanAwal = true;
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

            sudahDaftarkanAwal = true;
        }

        // ============================================================
        // TREN KEHADIRAN BULANAN
        // ============================================================
        // Data berasal dari server (tabel `absensis`), jadi yang digambar di
        // sini hanya bentuk visualnya. Blade sudah menampilkan kondisi kosong
        // kalau belum ada absensi sama sekali, jadi fungsi ini tidak perlu
        // menggambar apa pun dalam kasus itu.
        function initTrenChart() {
            const canvas = document.getElementById('trenChart');
            if (!canvas || typeof Chart === 'undefined' || !DATA_TREN_BULANAN.length) return;

            const totalKeseluruhan = DATA_TREN_BULANAN.reduce(function (jumlah, bar) {
                return jumlah + bar.total;
            }, 0);

            if (totalKeseluruhan === 0) return;

            // Sumbu Y memakai persentase, jadi tiap batang dinormalkan ke 100%.
            // Angka aslinya tetap dipakai di tooltip supaya guru bisa melihat
            // jumlah siswa, bukan cuma rationya.
            const persenBar = function (jumlah, bar) {
                return bar.total > 0 ? (jumlah / bar.total) * 100 : 0;
            };

            trenChart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: DATA_TREN_BULANAN.map(function (bar) { return bar.label; }),
                    datasets: [
                        {
                            label: 'Hadir',
                            data: DATA_TREN_BULANAN.map(function (bar) { return persenBar(bar.hadir, bar); }),
                            backgroundColor: '#10b981',
                            borderRadius: { topLeft: 0, topRight: 0, bottomLeft: 6, bottomRight: 6 },
                            borderSkipped: false
                        },
                        {
                            label: 'Izin',
                            data: DATA_TREN_BULANAN.map(function (bar) { return persenBar(bar.izin, bar); }),
                            backgroundColor: '#3b82f6',
                            borderSkipped: false
                        },
                        {
                            label: 'Sakit',
                            data: DATA_TREN_BULANAN.map(function (bar) { return persenBar(bar.sakit, bar); }),
                            backgroundColor: '#f59e0b',
                            borderSkipped: false
                        },
                        {
                            label: 'Alpa',
                            data: DATA_TREN_BULANAN.map(function (bar) { return persenBar(bar.alpa, bar); }),
                            backgroundColor: '#f43f5e',
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
                                    const bar = DATA_TREN_BULANAN[ctx.dataIndex];
                                    const kunci = ctx.dataset.label.toLowerCase();

                                    return ctx.dataset.label + ': ' + bar[kunci]
                                        + ' (' + ctx.parsed.y.toFixed(0) + '%)';
                                },
                                footer: function (items) {
                                    const bar = DATA_TREN_BULANAN[items[0].dataIndex];
                                    return 'Total ' + bar.total + ' catatan';
                                }
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false }, ticks: { font: { size: 11 } } },
                        y: {
                            stacked: true,
                            min: 0,
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
        function renderSeluruhHalaman() {
            const gabungan = gabungkanAbsensi();

            renderPemilihKelas();
            renderStatistik(presensiList);
            renderDonut(presensiList);
            renderProgresKelas();
            renderFeed(gabungan);
            renderTabelAbsensi(presensiList);

            cekAbsensiBaru(gabungan);

            const jam = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            setTeks('[data-terakhirSinkron]', 'Terakhir diperbarui pukul ' + jam);
        }

        initHalamanGuru(function () {
            daftarKelas = daftarKelasDiajarkan();

            // Kalau kelas pada identitas tidak ada di daftar, pakai yang pertama.
            kelasAktif = daftarKelas.indexOf(identitasGuru.kelasLengkap) !== -1
                ? identitasGuru.kelasLengkap
                : (daftarKelas[0] || null);

            if (kelasAktif !== null) {
                loadDataKelas(kelasAktif);
            }

            // Absensi dari storage ikut dibaca sejak awal supaya halaman
            // tidak terlihat kosong walau database belum sempat terisi.
            daftarkanAwal(gabungkanAbsensi());

            initTrenChart();
            renderSeluruhHalaman();
            startRealtimeAutoRefresh();

            const tombolRefresh = document.getElementById('tombolRefresh');
            if (tombolRefresh) tombolRefresh.addEventListener('click', muatDataServer);

            const tombolAuto = document.getElementById('tombolAutoRefresh');
            if (tombolAuto) tombolAuto.addEventListener('click', toggleAutoRefresh);

            const filter = document.getElementById('filterStatus');
            if (filter) {
                filter.addEventListener('change', function () {
                    filterStatus = filter.value;
                    renderTabelAbsensi(presensiList);
                });
            }

            // Halaman lain di tab yang sama menulis ke storage, jadi
            // perubahan di sana langsung terlihat tanpa menunggu polling.
            window.addEventListener('storage', function (event) {
                if (event.key !== STORAGE_KEY_DAFTAR_ABSEN && event.key !== STORAGE_KEY_SISWA_ABSEN) {
                    return;
                }

                const gabungan = gabungkanAbsensi();

                renderBadgeAbsensiMasuk();
                renderFeed(gabungan);
                cekAbsensiBaru(gabungan);
            });
        });
    </script>
@endpush
