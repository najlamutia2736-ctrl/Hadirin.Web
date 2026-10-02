@extends('layouts.app')

@section('title', 'Progres Absensi · Hadirin.Web')

@php
    $pageTitle = 'Progres Absensi';
    $breadcrumbItems = [
        ['label' => 'Beranda'],
        ['label' => 'Dashboard Guru'],
        ['label' => 'Progres Absensi', 'current' => true],
    ];

    $total = $progres['total'];
    $selisih = $progres['selisih'];

    /*
    | Ambang & warna persentase disamakan dengan laporan bulanan dan dashboard
    | guru, jadi persentase yang sama selalu dibaca dengan warna yang sama.
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
    | Badge tren: selisih persentase periode ini dengan periode sebelumnya
    | sepanjang durasi yang sama. Ini yang membuat halaman ini "progres",
    | bukan sekadar rekap.
    */
    $badgeTren = match (true) {
        $selisih > 0 => [
            'kelas' => 'bg-emerald-50 text-emerald-600',
            'ikon' => 'fas fa-arrow-up',
            'teks' => '+' . $selisih . ' poin',
        ],
        $selisih < 0 => [
            'kelas' => 'bg-rose-50 text-rose-600',
            'ikon' => 'fas fa-arrow-down',
            'teks' => $selisih . ' poin',
        ],
        default => [
            'kelas' => 'bg-gray-100 text-gray-500',
            'ikon' => 'fas fa-minus',
            'teks' => 'Sama saja',
        ],
    };

    /*
    | Kartu statistik. Angkanya sudah dihitung di server dari tabel
    | `absensis`, jadi label/ikon/warna tetap di Blade dan angka ikut
    | diperbarui tiap kali filter periode atau kelas berubah.
    */
    $statCards = [
        [
            'label' => 'Rata-rata Kehadiran',
            'icon' => 'fas fa-chart-line',
            'tone' => $warnaRata,
            'value' => $total['persentase'] . '%',
            'sub' => 'dari ' . number_format($total['total'], 0, ',', '.') . ' catatan absensi',
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
            'value' => number_format($progres['hariEfektif'], 0, ',', '.'),
            'sub' => 'dari ' . $progres['jumlahHari'] . ' hari dalam periode',
        ],
        [
            'label' => 'Alpa',
            'icon' => 'fas fa-user-slash',
            'tone' => 'rose',
            'value' => number_format($total['alpa'], 0, ',', '.'),
            'sub' => $total['total'] > 0 ? round($total['alpa'] / $total['total'] * 100) . '% dari total catatan' : 'Belum ada catatan',
        ],
    ];

    // Daftar panjang dibatasi di layar; angka lengkapnya tetap di tabel bawah.
    $daftarPerhatian = array_slice($progres['perluPerhatian'], 0, 6);
@endphp

@section('konten')
    {{-- filter periode & kelas --}}
    <form method="GET" action="{{ route('guru.progres') }}"
        class="mb-6 flex flex-col gap-3 rounded-xl border border-gray-100 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div>
                <label for="periode" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Periode
                </label>
                <select id="periode" name="periode"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @foreach ($progres['pilihanPeriode'] as $pilihan)
                        <option value="{{ $pilihan['nilai'] }}" @selected($progres['periode'] === $pilihan['nilai'])>
                            {{ $pilihan['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="kelas" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Kelas
                </label>
                <select id="kelas" name="kelas"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua kelas yang diampu</option>
                    @foreach ($progres['pilihanKelas'] as $pilihan)
                        <option value="{{ $pilihan }}" @selected($progres['filterKelas'] === $pilihan)>{{ $pilihan }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <span class="text-xs text-gray-500">
                {{ $progres['filterKelas'] ?? 'Semua kelas yang diampu' }} &middot;
                {{ $progres['label'] }} &middot; dicetak {{ $progres['dicetak'] }}
            </span>
            @if ($progres['filterKelas'] !== null)
                <a href="{{ route('guru.progres', ['periode' => $progres['periode']]) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition-colors hover:bg-gray-50">
                    <i class="fas fa-filter-circle-xmark"></i>
                    Semua Kelas
                </a>
            @endif
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
                <i class="fas fa-arrows-rotate"></i>
                Terapkan
            </button>
        </div>
    </form>

    @if (! $progres['adaCatatan'])
        <div
            class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            <i class="fas fa-circle-info mt-0.5 shrink-0"></i>
            <div>
                <p class="font-semibold">Belum ada catatan absensi pada {{ $progres['label'] }}.</p>
                <p class="mt-0.5">
                    Kerangka grafik dan tabelnya sudah disiapkan, tapi angkanya masih nol karena
                    belum ada baris di tabel absensi. {{ $total['siswa'] }} siswa pada
                    {{ $progres['filterKelas'] ?? 'kelas yang diampu' }} akan terhitung begitu absensi
                    dicatat di halaman
                    <a href="{{ route('guru.realtime') }}" class="font-semibold underline">Real-Time Monitoring</a>.
                </p>
            </div>
        </div>
    @endif

    {{-- absensi hari ini, dibaca langsung dari data yang sama dengan dashboard --}}
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
                <span class="font-semibold text-gray-800">{{ $progres['sebelumnya']['persentase'] }}%</span>
            </p>
        </div>
    </div>

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
                    @isset($card['badge'])
                        <span
                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $card['badge']['kelas'] }}">
                            <i class="{{ $card['badge']['ikon'] }} text-[9px]"></i>
                            <span>{{ $card['badge']['teks'] }}</span>
                        </span>
                    @endisset
                </div>

                <p class="mt-4 text-sm font-medium text-gray-500">{{ $card['label'] }}</p>
                <p class="mt-0.5 text-2xl font-bold tracking-tight {{ $tone['value'] }}">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs text-gray-400">{{ $card['sub'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- tren harian + progres per kelas --}}
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
                            <p class="text-xs text-gray-500">{{ $progres['label'] }} &middot; rata-rata {{ $total['persentase'] }}%</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-[11px] font-medium text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Persentase Hadir
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-sm bg-indigo-300"></span> Jumlah Catatan
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="h-0.5 w-4 rounded bg-gray-400"></span> Rata-rata Periode
                        </span>
                    </div>
                </div>

                <div class="px-6 py-5">
                    @if ($progres['adaCatatan'])
                        <div class="chart-container">
                            <canvas id="grafikProgresHarian"></canvas>
                        </div>
                    @else
                        <p class="py-10 text-center text-sm text-gray-400">
                            <i class="fas fa-chart-simple mb-2 block text-2xl text-gray-300"></i>
                            Belum ada absensi harian pada {{ $progres['label'] }}.
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
                            <h3 class="font-semibold text-gray-800">Progres per Kelas</h3>
                            <p class="text-xs text-gray-500">Peringkat kehadiran pada periode ini</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 px-6 py-5">
                    @forelse ($progres['perKelas'] as $baris)
                        @php $aktif = $baris['kelas'] === $progres['filterKelas']; @endphp
                        <a href="{{ route('guru.progres', array_filter(['periode' => $progres['periode'], 'kelas' => $baris['kelas']])) }}"
                            class="kelas-progres block rounded-lg transition-colors hover:bg-gray-50">
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

    {{-- siswa perlu perhatian + tabel progres per siswa --}}
    <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div>
            <div class="flex h-full flex-col rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-rose-50 text-rose-600">
                            <i class="fas fa-triangle-exclamation"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">Perlu Perhatian</h3>
                            <p class="text-xs text-gray-500">
                                Kehadiran di bawah {{ $progres['batasPerhatian'] }}% &middot;
                                {{ count($progres['perluPerhatian']) }} siswa
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
                                Semua siswa sudah di atas {{ $progres['batasPerhatian'] }}% pada periode ini.
                            </p>
                        </div>
                    @endforelse
                </div>

                @if (count($progres['perluPerhatian']) > count($daftarPerhatian))
                    <div class="border-t border-gray-100 px-6 py-3 text-center text-xs text-gray-500">
                        <span class="font-semibold text-gray-800">
                            {{ count($progres['perluPerhatian']) - count($daftarPerhatian) }}
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
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-violet-50 text-violet-600">
                            <i class="fas fa-table-list"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">Progres per Siswa</h3>
                            <p class="text-xs text-gray-500">
                                {{ $progres['filterKelas'] ?? 'Semua kelas yang diampu' }} &middot;
                                {{ count($progres['perSiswa']) }} siswa, urutan kehadiran terendah
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="relative w-full sm:w-52">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                                <i class="fas fa-search text-sm"></i>
                            </span>
                            <input type="search" id="progresCariSiswa" placeholder="Cari nama, NIS, kelas..."
                                class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <select id="progresFilterSiswa"
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
                        <tbody id="progresTabelSiswa" class="divide-y divide-gray-100">
                            @forelse ($progres['perSiswa'] as $baris)
                                @php
                                    $kelompok = $baris['total'] === 0
                                        ? 'kosong'
                                        : ($baris['persentase'] < $progres['batasPerhatian'] ? 'perhatian' : ($baris['persentase'] >= 85 ? 'baik' : 'lainnya'));
                                @endphp
                                <tr class="progres-row" data-kelompok="{{ $kelompok }}"
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
                    <span id="progresJumlahBaris">{{ count($progres['perSiswa']) }}</span>
                    siswa ditampilkan dari {{ count($progres['perSiswa']) }} siswa
                </div>
            </div>
        </div>
    </div>

    {{-- laporan rincian --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <a href="{{ route('guru.laporan', array_filter(['kelas' => $progres['filterKelas']])) }}"
            class="group flex items-start gap-4 rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-indigo-100 hover:shadow-md">
            <span
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 text-lg text-white shadow-md shadow-amber-500/30">
                <i class="fas fa-file-lines"></i>
            </span>
            <span class="min-w-0 flex-1">
                <span class="flex items-center justify-between gap-2">
                    <span class="truncate text-sm font-semibold text-gray-800">Laporan Bulanan</span>
                    <i
                        class="fas fa-arrow-up-right-from-square shrink-0 text-[10px] text-gray-300 transition-colors group-hover:text-indigo-500"></i>
                </span>
                <span class="mt-1 block text-xs leading-relaxed text-gray-500">
                    Rekap per hari per bulan lengkap dengan rincian tiap siswa dan ekspor CSV.
                </span>
            </span>
        </a>

        <a href="{{ route('guru.dashboard') }}"
            class="group flex items-start gap-4 rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-indigo-100 hover:shadow-md">
            <span
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-sky-500 text-lg text-white shadow-md shadow-blue-500/30">
                <i class="fas fa-tachometer-alt"></i>
            </span>
            <span class="min-w-0 flex-1">
                <span class="flex items-center justify-between gap-2">
                    <span class="truncate text-sm font-semibold text-gray-800">Dashboard Guru</span>
                    <i
                        class="fas fa-arrow-up-right-from-square shrink-0 text-[10px] text-gray-300 transition-colors group-hover:text-indigo-500"></i>
                </span>
                <span class="mt-1 block text-xs leading-relaxed text-gray-500">
                    Kehadiran hari ini, daftar siswa yang sudah absen, dan tombol buka sesi absensi.
                </span>
            </span>
        </a>
    </div>
@endsection

@push('styles')
    @include('guru.partials.styles')

    <style>
        /* transisi halus saat nilai pada kartu statistik berubah */
        [data-hariIniPersen] {
            transition: color 0.3s ease;
        }

        /* baris tabel progres */
        .progres-row {
            transition: background-color 0.2s ease;
        }

        .progres-row:hover {
            background-color: #f9fafb;
        }

        /* baris progres per kelas */
        .kelas-progres {
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
        // PROGRES ABSENSI
        // ============================================================
        // Semua angka periode ini sudah dihitung di server dari tabel
        // `absensis`, jadi script ini tidak merangkum apa pun. Tugasnya
        // hanya: menggambar tren harian, memuat data hari ini, dan
        // menyaring tabel per siswa.

        const TREN_HARIAN = @json($progres['trenHarian']);
        const KELAS_AKTIF = @json($progres['filterKelas']);
        const RATA_PERIODE = {{ $total['persentase'] }};

        // ============================================================
        // GRAFIK TREN HARIAN
        // ============================================================
        function initGrafikTren() {
            const canvas = document.getElementById('grafikProgresHarian');

            if (!canvas || typeof Chart === 'undefined' || TREN_HARIAN.length === 0) return;

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
        }

        // ============================================================
        // ABSENSI HARI INI
        // ============================================================
        // Berbeda dengan kartu di atas yang agregasi periode, panel ini
        // menampilkan kondisi hari ini dari kelas yang sedang difilter.
        function renderHariIni() {
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
        // TABEL PROGRES PER SISWA
        // ============================================================
        function setTeks(selector, nilai) {
            document.querySelectorAll(selector).forEach(function (el) {
                el.textContent = nilai;
            });
        }

        function saringTabelSiswa() {
            const cari = document.getElementById('progresCariSiswa');
            const filter = document.getElementById('progresFilterSiswa');

            const kataKunci = cari ? cari.value.trim().toLowerCase() : '';
            const kelompok = filter ? filter.value : 'semua';

            const baris = document.querySelectorAll('#progresTabelSiswa .progres-row');
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

            setTeks('#progresJumlahBaris', tampil);
        }

        // ============================================================
        // INIT
        // ============================================================
        initHalamanGuru(function () {
            renderHariIni();
            initGrafikTren();

            const cari = document.getElementById('progresCariSiswa');
            if (cari) cari.addEventListener('input', saringTabelSiswa);

            const filter = document.getElementById('progresFilterSiswa');
            if (filter) filter.addEventListener('change', saringTabelSiswa);
        });
    </script>
@endpush
