@extends('layouts.app')

@section('title', 'Laporan Bulanan · Hadirin.Web')

@php
    $pageTitle = 'Laporan Bulanan';
    $breadcrumbItems = [
        ['label' => 'Beranda'],
        ['label' => 'Dashboard Guru'],
        ['label' => 'Laporan Bulanan', 'current' => true],
    ];

    $kelasAktif = $laporan['filterKelas'];
    $adaCatatan = $laporan['adaCatatan'];
    $total = $laporan['total'];

    // Tab periode punya pembanding dengan periode sebelumnya, tab bulanan tidak.
    $perbandingan = $laporan['tab'] === 'periode';

    /*
    | Warna bar persentase kehadiran. Nilainya dipakai bersama tabel per kelas,
    | tabel per siswa, dan kartu ringkasan supaya persentase yang sama selalu
    | dibaca dengan warna yang sama.
    */
    $warnaPersentase = fn (int $nilai): string => match (true) {
        $nilai >= 85 => 'bg-emerald-500',
        $nilai >= 70 => 'bg-amber-500',
        default => 'bg-rose-500',
    };

    $warnaRata = match (true) {
        $total['persentase'] >= 85 => 'emerald',
        $total['persentase'] >= 70 => 'amber',
        default => 'rose',
    };

    $tones = [
        'emerald' => [
            'chip' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/30',
            'accent' => 'from-emerald-500 to-teal-400',
            'value' => 'text-emerald-600',
            'bar' => 'bg-emerald-500',
        ],
        'blue' => [
            'chip' => 'bg-gradient-to-br from-blue-500 to-sky-500 text-white shadow-md shadow-blue-500/30',
            'accent' => 'from-blue-500 to-sky-400',
            'value' => 'text-blue-600',
            'bar' => 'bg-blue-500',
        ],
        'amber' => [
            'chip' => 'bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-md shadow-amber-500/30',
            'accent' => 'from-amber-500 to-orange-400',
            'value' => 'text-amber-600',
            'bar' => 'bg-amber-500',
        ],
        'rose' => [
            'chip' => 'bg-gradient-to-br from-rose-500 to-red-500 text-white shadow-md shadow-rose-500/30',
            'accent' => 'from-rose-500 to-red-400',
            'value' => 'text-rose-600',
            'bar' => 'bg-rose-500',
        ],
    ];

    /*
    | Badge tren. Hanya tab periode yang punya pembanding, jadi tab bulanan
    | menampilkan nilai absen dan hari efektif sebagai gantinya.
    */
    $badgeTren = match (true) {
        ! $perbandingan => null,
        $laporan['selisih'] > 0 => [
            'kelas' => 'bg-emerald-50 text-emerald-600',
            'ikon' => 'fas fa-arrow-up',
            'teks' => '+' . $laporan['selisih'] . ' poin',
        ],
        $laporan['selisih'] < 0 => [
            'kelas' => 'bg-rose-50 text-rose-600',
            'ikon' => 'fas fa-arrow-down',
            'teks' => $laporan['selisih'] . ' poin',
        ],
        default => [
            'kelas' => 'bg-gray-100 text-gray-500',
            'ikon' => 'fas fa-minus',
            'teks' => 'Sama saja',
        ],
    };

    /*
    | Kartu statistik. Angkanya sudah dihitung di server dari tabel `absensis`,
    | jadi label/ikon/warna tetap di Blade dan angka ikut terupdate tiap kali
    | tab, filter periode, atau filter kelas berubah.
    */
    $statCards = [
        [
            'label' => 'Rata-rata Kehadiran',
            'icon' => 'fas fa-chart-line',
            'tone' => $warnaRata,
            'value' => $total['persentase'] . '%',
            'sub' => $perbandingan
                ? $total['siswa'] . ' siswa ikut dihitung'
                : 'dari ' . number_format($total['total'], 0, ',', '.') . ' catatan absensi',
            'badge' => $badgeTren,
        ],
        [
            'label' => 'Total Catatan',
            'icon' => 'fas fa-list-check',
            'tone' => 'blue',
            'value' => number_format($total['total'], 0, ',', '.'),
            'sub' => $total['siswa'] . ' siswa ikut dihitung',
        ],
        [
            'label' => 'Hari Efektif',
            'icon' => 'fas fa-calendar-check',
            'tone' => 'amber',
            'value' => number_format($laporan['hariEfektif'], 0, ',', '.'),
            'sub' => 'dari ' . $laporan['jumlahHari'] . ' hari dalam periode',
        ],
        [
            'label' => 'Alpa',
            'icon' => 'fas fa-user-slash',
            'tone' => 'rose',
            'value' => number_format($total['alpa'], 0, ',', '.'),
            'sub' => $total['total'] > 0
                ? round($total['alpa'] / $total['total'] * 100) . '% dari total catatan'
                : 'Belum ada catatan',
        ],
    ];

    // Daftar panjang dibatasi di layar; angka lengkapnya tetap di tabel bawah.
    $daftarPerhatian = array_slice($laporan['perluPerhatian'], 0, 6);
@endphp

@section('konten')
    {{-- tab: rekap bulanan & progres absensi sekarang jadi satu halaman --}}
    <div class="mb-6 inline-flex rounded-xl border border-gray-100 bg-white p-1 shadow-sm">
        @foreach ($laporan['pilihanTab'] as $pilihan)
            @php $aktif = $laporan['tab'] === $pilihan['nilai']; @endphp
            <a href="{{ route('guru.laporan', array_filter([
                        'tab' => $pilihan['nilai'] === 'bulanan' ? null : $pilihan['nilai'],
                        'bulan' => $laporan['bulan'],
                        'periode' => $laporan['periode'],
                        'kelas' => $kelasAktif,
                    ])) }}"
                @class([
                    'inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-colors',
                    'bg-indigo-600 text-white shadow-sm' => $aktif,
                    'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' => ! $aktif,
                ])>
                <i class="{{ $aktif ? 'fas fa-file-alt' : 'fas fa-chart-line' }} text-xs"></i>
                {{ $pilihan['label'] }}
            </a>
        @endforeach
    </div>

    {{-- filter periode & kelas --}}
    <form method="GET" action="{{ route('guru.laporan') }}"
        class="mb-6 flex flex-col gap-3 rounded-xl border border-gray-100 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-end sm:justify-between">
        {{-- tab aktif ikut dibawa supaya filter tidak melompat ke tab bulanan --}}
        <input type="hidden" name="tab" value="{{ $laporan['tab'] }}">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            @if ($perbandingan)
                <div>
                    <label for="periode" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Periode
                    </label>
                    <select id="periode" name="periode"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @foreach ($laporan['pilihanPeriode'] as $pilihan)
                            <option value="{{ $pilihan['nilai'] }}" @selected($laporan['periode'] === $pilihan['nilai'])>
                                {{ $pilihan['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div>
                    <label for="bulan" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Periode Bulan
                    </label>
                    <select id="bulan" name="bulan"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @foreach ($laporan['pilihanBulan'] as $pilihan)
                            <option value="{{ $pilihan['nilai'] }}" @selected($laporan['bulan'] === $pilihan['nilai'])>
                                {{ $pilihan['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- disembunyikan supaya pindah tab periode tidak kehilangan bulan pilihan --}}
                <input type="hidden" name="periode" value="{{ $laporan['periode'] }}">
            @endif

            <div>
                <label for="kelas" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Kelas
                </label>
                <select id="kelas" name="kelas"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua kelas yang diampu</option>
                    @foreach ($laporan['pilihanKelas'] as $pilihan)
                        <option value="{{ $pilihan }}" @selected($kelasAktif === $pilihan)>{{ $pilihan }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <span class="text-xs text-gray-500">
                {{ $kelasAktif ?? 'Semua kelas yang diampu' }} &middot;
                {{ $laporan['label'] }} &middot; dicetak {{ $laporan['dicetak'] }}
            </span>

            @if ($kelasAktif !== null)
                <a href="{{ route('guru.laporan', array_filter([
                            'tab' => $laporan['tab'] === 'bulanan' ? null : $laporan['tab'],
                            'bulan' => $laporan['bulan'],
                            'periode' => $laporan['periode'],
                        ])) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition-colors hover:bg-gray-50">
                    <i class="fas fa-filter-circle-xmark"></i>
                    Semua Kelas
                </a>
            @endif

            @unless ($perbandingan)
                <a href="{{ route('guru.laporan.export', array_filter(['bulan' => $laporan['bulan'], 'kelas' => $kelasAktif])) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition-colors hover:bg-indigo-100">
                    <i class="fas fa-file-csv"></i>
                    Ekspor CSV
                </a>
            @endunless

            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
                <i class="fas fa-filter"></i>
                Terapkan
            </button>
        </div>
    </form>

    @unless ($adaCatatan)
        <div
            class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            <i class="fas fa-circle-info mt-0.5 shrink-0"></i>
            <div>
                <p class="font-semibold">Belum ada catatan absensi pada {{ $laporan['label'] }}.</p>
                <p class="mt-0.5">
                    Laporan ini dihitung dari tabel absensi yang sama dengan dashboard.
                    {{ $total['siswa'] }} siswa tetap terdaftar di bawah, tetapi semua kolom
                    kehadiran masih kosong sampai absensi dicatat di halaman
                    <a href="{{ route('guru.realtime') }}" class="font-semibold underline">Real-Time Monitoring</a>.
                </p>
            </div>
        </div>
    @endunless

    {{-- absensi hari ini, hanya ada di tab progres karena ada pembanding periode --}}
    @if ($perbandingan)
        <div
            class="mb-6 flex flex-col gap-4 rounded-xl border border-gray-100 bg-white px-5 py-4 shadow-sm lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-emerald-50 text-emerald-600">
                    <i class="fas fa-bolt"></i>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">Absensi Hari Ini</h3>
                    <p class="text-xs text-gray-500" data-hariIniKelas>Memuat data kelas...</p>
                </div>
            </div>

            <div class="flex flex-1 flex-col gap-2 lg:max-w-md">
                <div class="flex items-center gap-3">
                    <div class="progress-bar flex-1">
                        <div class="progress-fill bg-emerald-500" data-hariIniBar style="width: 0%"></div>
                    </div>
                    <span class="shrink-0 text-sm text-gray-600">
                        <span class="font-semibold text-gray-800" data-hariIniHadir>0</span>
                        /
                        <span data-hariIniTotal>0</span>
                        <span class="ml-1 font-semibold text-emerald-600" data-hariIniPersen>0%</span>
                    </span>
                </div>
                <p class="text-xs text-gray-500">
                    Bandingkan dengan rata-rata periode
                    <span class="font-semibold text-gray-800">{{ $total['persentase'] }}%</span>
                    &middot; rata-rata periode sebelumnya
                    <span class="font-semibold text-gray-800">{{ $laporan['sebelumnya']['persentase'] }}%</span>
                </p>
            </div>
        </div>
    @endif

    {{-- kartu statistik --}}
    <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($statCards as $card)
            @php $tone = $tones[$card['tone']]; @endphp
            <div
                class="group relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $tone['accent'] }}"></span>

                <div class="flex items-start justify-between gap-3">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-lg transition-transform duration-300 group-hover:scale-110 {{ $tone['chip'] }}">
                        <i class="{{ $card['icon'] }}"></i>
                    </div>
                    @if (isset($card['badge']) && $card['badge'] !== null)
                        <span
                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $card['badge']['kelas'] }}">
                            <i class="{{ $card['badge']['ikon'] }} text-[9px]"></i>
                            <span>{{ $card['badge']['teks'] }}</span>
                        </span>
                    @endif
                </div>

                <p class="mt-4 text-sm font-medium text-gray-500">{{ $card['label'] }}</p>
                <p class="mt-0.5 text-2xl font-bold tracking-tight {{ $tone['value'] }}">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs text-gray-400">{{ $card['sub'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- tren harian + rekap/progres per kelas --}}
    <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="h-full rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">Tren Kehadiran Harian</h3>
                            <p class="text-xs text-gray-500">
                                {{ $laporan['label'] }} &middot; rata-rata {{ $total['persentase'] }}%
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-[11px] font-medium text-gray-500">
                        @if ($perbandingan)
                            <span class="flex items-center gap-1.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Persentase Hadir
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="h-2.5 w-2.5 rounded-sm bg-indigo-300"></span> Jumlah Catatan
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="h-0.5 w-4 rounded bg-gray-400"></span> Rata-rata Periode
                            </span>
                        @else
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
                        @endif
                    </div>
                </div>

                <div class="px-6 py-5">
                    @if ($adaCatatan)
                        <div class="chart-container">
                            <canvas id="grafikTrenHarian"></canvas>
                        </div>
                    @else
                        <p class="py-10 text-center text-sm text-gray-400">
                            <i class="fas fa-chart-simple mb-2 block text-2xl text-gray-300"></i>
                            Belum ada absensi harian pada {{ $laporan['label'] }}.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <div class="h-full rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-amber-50 text-amber-600">
                            <i class="fas fa-layer-group"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">
                                {{ $perbandingan ? 'Progres per Kelas' : 'Rekap per Kelas' }}
                            </h3>
                            <p class="text-xs text-gray-500">
                                {{ $perbandingan ? 'Peringkat kehadiran pada periode ini' : 'Ringkasan kehadiran pada ' . $laporan['label'] }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 px-6 py-5">
                    @forelse ($laporan['perKelas'] as $baris)
                        @php $aktif = $baris['kelas'] === $kelasAktif; @endphp
                        <a href="{{ route('guru.laporan', array_filter([
                                    'tab' => $perbandingan ? 'periode' : null,
                                    'bulan' => $laporan['bulan'],
                                    'periode' => $laporan['periode'],
                                    'kelas' => $baris['kelas'],
                                ])) }}"
                            class="kelas-rekap block rounded-lg transition-colors hover:bg-gray-50">
                            <div class="mb-1.5 flex items-center justify-between gap-2">
                                <span class="flex min-w-0 items-center gap-2">
                                    <span class="truncate text-sm font-semibold {{ $aktif ? 'text-indigo-600' : 'text-gray-700' }}">
                                        {{ $baris['kelas'] }}
                                    </span>
                                    @if ($aktif)
                                        <span
                                            class="shrink-0 rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-600">
                                            difilter
                                        </span>
                                    @endif
                                </span>
                                <span class="shrink-0 text-xs text-gray-500">
                                    {{ $baris['hadir'] }}/{{ $baris['total'] }}
                                    <span class="font-semibold text-gray-800">{{ $baris['persentase'] }}%</span>
                                </span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill {{ $warnaPersentase($baris['persentase']) }}"
                                    style="width: {{ $baris['persentase'] }}%"></div>
                            </div>
                            <div class="mt-1.5 flex items-center gap-3 text-[11px] text-gray-400">
                                <span class="font-medium text-emerald-600">{{ $baris['hadir'] }} hadir</span>
                                <span class="font-medium text-blue-600">{{ $baris['izin'] }} izin</span>
                                <span class="font-medium text-amber-600">{{ $baris['sakit'] }} sakit</span>
                                <span class="font-medium text-rose-600">{{ $baris['alpa'] }} alpa</span>
                            </div>
                        </a>
                    @empty
                        <p class="py-6 text-center text-sm text-gray-400">
                            <i class="fas fa-school mb-2 block text-2xl text-gray-300"></i>
                            Belum ada kelas yang diampu.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- rekap per kelas + siswa perlu perhatian --}}
    <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div>
            <div class="flex h-full flex-col rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-rose-50 text-rose-600">
                            <i class="fas fa-triangle-exclamation"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">Perlu Perhatian</h3>
                            <p class="text-xs text-gray-500">
                                Kehadiran di bawah {{ $laporan['batasPerhatian'] }}% &middot;
                                {{ count($laporan['perluPerhatian']) }} siswa
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex-1 space-y-3 px-6 py-5">
                    @forelse ($daftarPerhatian as $baris)
                        <div class="flex items-center gap-3">
                            <span
                                class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-rose-50 text-xs font-semibold text-rose-600">
                                {{ mb_strtoupper(mb_substr($baris['nama'] ?: '?', 0, 1)) }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-gray-800">
                                    {{ $baris['nama'] }}
                                </span>
                                <span class="block text-xs text-gray-400">
                                    {{ $baris['kelas'] }} &middot; {{ $baris['hadir'] }} dari
                                    {{ $baris['total'] }} hari hadir
                                </span>
                            </span>
                            <span
                                class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $baris['persentase'] >= 60 ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600' }}">
                                {{ $baris['persentase'] }}%
                            </span>
                        </div>
                    @empty
                        <div class="flex h-full flex-col items-center justify-center py-6 text-center">
                            <span class="mb-2 grid h-12 w-12 place-items-center rounded-full bg-emerald-50 text-emerald-600">
                                <i class="fas fa-thumbs-up"></i>
                            </span>
                            <p class="text-sm font-semibold text-gray-700">Tidak ada siswa bermasalah</p>
                            <p class="mt-0.5 text-xs text-gray-500">
                                Semua siswa sudah di atas {{ $laporan['batasPerhatian'] }}% pada periode ini.
                            </p>
                        </div>
                    @endforelse
                </div>

                @if (count($laporan['perluPerhatian']) > count($daftarPerhatian))
                    <div class="border-t border-gray-100 px-6 py-3 text-center text-xs text-gray-500">
                        <span class="font-semibold text-gray-800">
                            {{ count($laporan['perluPerhatian']) - count($daftarPerhatian) }}
                        </span>
                        siswa lain bisa difilter di tabel bawah
                    </div>
                @endif
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="flex h-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-violet-50 text-violet-600">
                            <i class="fas fa-table-list"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">
                                {{ $perbandingan ? 'Progres per Siswa' : 'Rincian per Siswa' }}
                            </h3>
                            <p class="text-xs text-gray-500">
                                {{ $kelasAktif ?? 'Semua kelas yang diampu' }} &middot;
                                {{ count($laporan['perSiswa']) }} siswa
                                @if ($perbandingan)
                                    , urutan kehadiran terendah
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="relative w-full sm:w-52">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                                <i class="fas fa-search text-sm"></i>
                            </span>
                            <input type="search" id="laporanCariSiswa" placeholder="Cari nama, NIS, kelas..."
                                class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <select id="laporanFilterSiswa"
                            class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="semua">Semua siswa</option>
                            <option value="perhatian">Perlu perhatian</option>
                            <option value="baik">Kehadiran baik</option>
                            <option value="kosong">Belum ada catatan</option>
                        </select>
                    </div>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Siswa</th>
                                <th class="px-4 py-3 font-semibold">Kelas</th>
                                <th class="px-4 py-3 text-center font-semibold">Hadir</th>
                                <th class="px-4 py-3 text-center font-semibold">Izin</th>
                                <th class="px-4 py-3 text-center font-semibold">Sakit</th>
                                <th class="px-4 py-3 text-center font-semibold">Alpa</th>
                                <th class="px-4 py-3 text-center font-semibold">Hari</th>
                                <th class="px-6 py-3 font-semibold">Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody id="laporanTabelSiswa" class="divide-y divide-gray-100">
                            @forelse ($laporan['perSiswa'] as $baris)
                                @php
                                    $kelompok = $baris['total'] === 0
                                        ? 'kosong'
                                        : ($baris['persentase'] < $laporan['batasPerhatian'] ? 'perhatian' : ($baris['persentase'] >= 85 ? 'baik' : 'lainnya'));
                                @endphp
                                <tr class="laporan-row" data-kelompok="{{ $kelompok }}"
                                    data-cari="{{ mb_strtolower($baris['nama'] . ' ' . $baris['nis'] . ' ' . $baris['kelas']) }}">
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-600">
                                                {{ mb_strtoupper(mb_substr($baris['nama'] ?: '?', 0, 1)) }}
                                            </span>
                                            <span class="min-w-0">
                                                <span class="block max-w-[14rem] truncate text-sm font-semibold text-gray-800">
                                                    {{ $baris['nama'] }}
                                                </span>
                                                <span class="block text-xs text-gray-400">
                                                    NIS {{ $baris['nis'] }}
                                                </span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-block rounded-full bg-purple-50 px-2.5 py-1 text-xs font-medium text-purple-600">
                                            {{ $baris['kelas'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-semibold text-emerald-600">
                                        {{ $baris['hadir'] }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-blue-600">{{ $baris['izin'] }}</td>
                                    <td class="px-4 py-3 text-center text-amber-600">{{ $baris['sakit'] }}</td>
                                    <td class="px-4 py-3 text-center font-semibold text-rose-600">
                                        {{ $baris['alpa'] }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-600">{{ $baris['hari'] }}</td>
                                    <td class="px-6 py-3">
                                        @if ($baris['total'] > 0)
                                            <div class="flex items-center gap-3">
                                                <div class="progress-bar w-24">
                                                    <div class="progress-fill {{ $warnaPersentase($baris['persentase']) }}"
                                                        style="width: {{ $baris['persentase'] }}%"></div>
                                                </div>
                                                <span class="text-sm font-semibold text-gray-800">
                                                    {{ $baris['persentase'] }}%
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">Belum ada catatan</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500">
                                        Belum ada siswa di kelas yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-3 text-xs text-gray-500">
                    <span id="laporanJumlahBaris">{{ count($laporan['perSiswa']) }}</span>
                    siswa ditampilkan dari {{ count($laporan['perSiswa']) }} siswa
                </div>
            </div>
        </div>
    </div>

    {{-- tabel rekap per kelas + total, tetap dipakai kedua tab --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <div class="flex items-center gap-3">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-violet-50 text-violet-600">
                    <i class="fas fa-school"></i>
                </span>
                <div>
                    <h3 class="font-semibold text-gray-800">Ringkasan per Kelas</h3>
                    <p class="text-xs text-gray-500">Total keseluruhan pada {{ $laporan['label'] }}</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Kelas</th>
                        <th class="px-4 py-3 text-center font-semibold">Siswa</th>
                        <th class="px-4 py-3 text-center font-semibold">Hadir</th>
                        <th class="px-4 py-3 text-center font-semibold">Izin</th>
                        <th class="px-4 py-3 text-center font-semibold">Sakit</th>
                        <th class="px-4 py-3 text-center font-semibold">Alpa</th>
                        <th class="px-4 py-3 text-center font-semibold">Total Catatan</th>
                        <th class="px-6 py-3 font-semibold">Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($laporan['perKelas'] as $baris)
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('guru.laporan', array_filter([
                                            'tab' => $perbandingan ? 'periode' : null,
                                            'bulan' => $laporan['bulan'],
                                            'periode' => $laporan['periode'],
                                            'kelas' => $baris['kelas'],
                                        ])) }}"
                                    class="inline-flex items-center gap-2 font-medium text-gray-800 hover:text-indigo-600">
                                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-purple-100 text-purple-600">
                                        <i class="fas fa-chalkboard text-xs"></i>
                                    </span>
                                    {{ $baris['kelas'] }}
                                </a>
                            </td>
                            <td class="px-4 py-4 text-center text-gray-700">{{ $baris['siswa'] }}</td>
                            <td class="px-4 py-4 text-center font-semibold text-emerald-600">{{ $baris['hadir'] }}</td>
                            <td class="px-4 py-4 text-center font-semibold text-blue-600">{{ $baris['izin'] }}</td>
                            <td class="px-4 py-4 text-center font-semibold text-amber-600">{{ $baris['sakit'] }}</td>
                            <td class="px-4 py-4 text-center font-semibold text-rose-600">{{ $baris['alpa'] }}</td>
                            <td class="px-4 py-4 text-center text-gray-600">{{ $baris['total'] }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="progress-bar w-28">
                                        <div class="progress-fill {{ $warnaPersentase($baris['persentase']) }}"
                                            style="width: {{ $baris['persentase'] }}%"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-800">
                                        {{ $baris['persentase'] }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                Belum ada kelas yang bisa dilaporkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if (count($laporan['perKelas']) > 0)
                    <tfoot class="border-t-2 border-gray-200 bg-gray-50 text-sm">
                        <tr>
                            <td class="px-6 py-4 font-bold text-gray-800">TOTAL</td>
                            <td class="px-4 py-4 text-center font-bold text-gray-800">{{ $total['siswa'] }}</td>
                            <td class="px-4 py-4 text-center font-bold text-emerald-600">{{ $total['hadir'] }}</td>
                            <td class="px-4 py-4 text-center font-bold text-blue-600">{{ $total['izin'] }}</td>
                            <td class="px-4 py-4 text-center font-bold text-amber-600">{{ $total['sakit'] }}</td>
                            <td class="px-4 py-4 text-center font-bold text-rose-600">{{ $total['alpa'] }}</td>
                            <td class="px-4 py-4 text-center font-bold text-gray-800">{{ $total['total'] }}</td>
                            <td class="px-6 py-4 font-bold text-gray-800">{{ $total['persentase'] }}%</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection

@push('styles')
    @include('guru.partials.styles')

    <style>
        .chart-container {
            position: relative;
            height: 280px;
        }

        /* transisi halus saat nilai pada panel absensi hari ini berubah */
        [data-hariIniPersen] {
            transition: color 0.3s ease;
        }

        /* baris tabel siswa */
        .laporan-row {
            transition: background-color 0.2s ease;
        }

        .laporan-row:hover {
            background-color: #f9fafb;
        }

        /* baris rekap per kelas */
        .kelas-rekap {
            padding-bottom: 0.25rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        window.HADIRIN_GURU = @json($guruData ?? null);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @include('guru.partials.data-guru')

    <script>
        // ============================================================
        // LAPORAN BULANAN
        // ============================================================
        // Halaman ini menggabungkan dua laporan lama: rekap bulanan dan
        // progres absensi. Periodenya dibedakan lewat tab, tapi tabel dan
        // kartu di bawahnya sama persis, jadi yang digambar di sini hanya
        // grafik tren harian, panel absensi hari ini, dan penyaring tabel.
        //
        // Seluruh angka sudah dihitung di server dari tabel `absensis`, jadi
        // script ini tidak merangkum apa pun.

        const TAB = @json($laporan['tab']);
        const TREN_HARIAN = @json($laporan['trenHarian']);
        const KELAS_AKTIF = @json($laporan['filterKelas']);
        const RATA_PERIODE = {{ $total['persentase'] }};

        // ============================================================
        // GRAFIK TREN HARIAN
        // ============================================================
        // Tab periode memakai garis persentase hadir dengan garis rata-rata
        // sebagai pembanding. Tab bulanan memakai batang bertumpuk per
        // status, karena dalam satu bulan guru lebih butuh komposisi
        // kehadiran per hari daripada persentase saja.
        function initGrafikTren() {
            const canvas = document.getElementById('grafikTrenHarian');

            if (!canvas || typeof Chart === 'undefined' || TREN_HARIAN.length === 0) return;

            if (TAB === 'periode') {
                new Chart(canvas, {
                    data: {
                        labels: TREN_HARIAN.map(function (baris) { return baris.label; }),
                        datasets: [
                            {
                                type: 'line',
                                label: 'Persentase Hadir',
                                data: TREN_HARIAN.map(function (baris) { return baris.persentase; }),
                                yAxisID: 'y',
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.12)',
                                borderWidth: 2,
                                tension: 0.35,
                                fill: true,
                                pointRadius: 3,
                                pointHoverRadius: 5,
                                pointBackgroundColor: '#10b981'
                            },
                            {
                                type: 'line',
                                label: 'Rata-rata Periode',
                                data: TREN_HARIAN.map(function () { return RATA_PERIODE; }),
                                yAxisID: 'y',
                                borderColor: '#9ca3af',
                                borderWidth: 1.5,
                                borderDash: [6, 4],
                                tension: 0,
                                fill: false,
                                pointRadius: 0,
                                pointHoverRadius: 0
                            },
                            {
                                type: 'bar',
                                label: 'Jumlah Catatan',
                                data: TREN_HARIAN.map(function (baris) { return baris.catatan; }),
                                yAxisID: 'y1',
                                backgroundColor: 'rgba(129, 140, 248, 0.35)',
                                hoverBackgroundColor: 'rgba(99, 102, 241, 0.6)',
                                borderRadius: 4,
                                borderSkipped: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    afterBody: function (items) {
                                        const hari = TREN_HARIAN[items[0].dataIndex];

                                        return [
                                            'Tanggal: ' + hari.tanggal,
                                            'Hadir ' + hari.hadir + ' · Izin ' + hari.izin
                                            + ' · Sakit ' + hari.sakit + ' · Alpa ' + hari.alpa
                                        ];
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font: { size: 11 },
                                    autoSkip: true,
                                    maxRotation: 0,
                                    maxTicksLimit: 15
                                }
                            },
                            y: {
                                position: 'left',
                                min: 0,
                                max: 100,
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    font: { size: 11 },
                                    callback: function (nilai) { return nilai + '%'; }
                                }
                            },
                            y1: {
                                position: 'right',
                                beginAtZero: true,
                                grid: { display: false },
                                ticks: { font: { size: 11 }, precision: 0 }
                            }
                        }
                    }
                });

                return;
            }

            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: TREN_HARIAN.map(function (baris) { return baris.label; }),
                    datasets: [
                        {
                            label: 'Hadir',
                            data: TREN_HARIAN.map(function (baris) { return baris.hadir; }),
                            backgroundColor: '#10b981',
                            borderRadius: { topLeft: 0, topRight: 0, bottomLeft: 4, bottomRight: 4 },
                            borderSkipped: false
                        },
                        {
                            label: 'Izin',
                            data: TREN_HARIAN.map(function (baris) { return baris.izin; }),
                            backgroundColor: '#3b82f6',
                            borderRadius: 4,
                            borderSkipped: false
                        },
                        {
                            label: 'Sakit',
                            data: TREN_HARIAN.map(function (baris) { return baris.sakit; }),
                            backgroundColor: '#f59e0b',
                            borderRadius: 4,
                            borderSkipped: false
                        },
                        {
                            label: 'Alpa',
                            data: TREN_HARIAN.map(function (baris) { return baris.alpa; }),
                            backgroundColor: '#f43f5e',
                            borderRadius: 4,
                            borderSkipped: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: { display: false },
                            ticks: { font: { size: 11 }, autoSkip: true, maxRotation: 0 }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: { font: { size: 11 }, precision: 0 }
                        }
                    }
                }
            });
        }

        // ============================================================
        // ABSENSI HARI INI
        // ============================================================
        // Hanya dirender di tab periode. Menampilkan kondisi hari ini dari
        // kelas yang sedang difilter, supaya angkanya bisa langsung
        // dibandingkan dengan rata-rata periode di atasnya.
        function renderHariIni() {
            if (TAB !== 'periode') return;

            const kelas = (SERVER_DATA && Array.isArray(SERVER_DATA.kelas)) ? SERVER_DATA.kelas : [];
            const terpilih = kelas.find(function (item) {
                return item.nama === KELAS_AKTIF;
            }) || kelas[0] || null;

            setTeks('[data-hariIniKelas]', terpilih === null
                ? 'Belum ada kelas yang diampu'
                : terpilih.nama + ' · ' + terpilih.total + ' siswa terdaftar');

            if (terpilih === null) {
                setTeks('[data-hariIniTotal]', 0);
                setTeks('[data-hariIniHadir]', 0);
                setTeks('[data-hariIniPersen]', '0%');
                return;
            }

            const list = loadDataKelas(terpilih.nama);
            const stat = hitungStatAbsensi(list);
            const total = list.length;
            const nilai = total > 0 ? Math.round((stat.hadir / total) * 100) : 0;

            setTeks('[data-hariIniTotal]', total);
            setTeks('[data-hariIniHadir]', stat.hadir);
            setTeks('[data-hariIniPersen]', nilai + '%');

            const bar = document.querySelector('[data-hariIniBar]');
            if (bar) {
                bar.style.width = nilai + '%';
                bar.className = 'progress-fill ' + (
                    nilai >= 75 ? 'bg-emerald-500' : (nilai >= 40 ? 'bg-amber-500' : 'bg-rose-500')
                );
            }
        }

        // ============================================================
        // TABEL PER SISWA
        // ============================================================
        function setTeks(selector, nilai) {
            document.querySelectorAll(selector).forEach(function (el) {
                el.textContent = nilai;
            });
        }

        function saringTabelSiswa() {
            const cari = document.getElementById('laporanCariSiswa');
            const filter = document.getElementById('laporanFilterSiswa');

            const kataKunci = cari ? cari.value.trim().toLowerCase() : '';
            const kelompok = filter ? filter.value : 'semua';

            const baris = document.querySelectorAll('#laporanTabelSiswa .laporan-row');
            let tampil = 0;

            baris.forEach(function (tr) {
                const cocokTeks = kataKunci === '' || tr.dataset.cari.indexOf(kataKunci) !== -1;
                // `lainnya` ikut terbawa filter "semua" supaya tidak ada baris
                // yang hilang saat pengguna menyaring.
                const cocokKelompok = kelompok === 'semua' || tr.dataset.kelompok === kelompok;

                const tampilkan = cocokTeks && cocokKelompok;
                tr.classList.toggle('hidden', !tampilkan);

                if (tampilkan) tampil++;
            });

            setTeks('#laporanJumlahBaris', tampil);
        }

        // ============================================================
        // INIT
        // ============================================================
        initHalamanGuru(function () {
            renderHariIni();
            initGrafikTren();

            const cari = document.getElementById('laporanCariSiswa');
            if (cari) cari.addEventListener('input', saringTabelSiswa);

            const filter = document.getElementById('laporanFilterSiswa');
            if (filter) filter.addEventListener('change', saringTabelSiswa);
        });
    </script>
@endpush