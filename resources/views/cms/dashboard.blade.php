@extends('layouts.app')

@section('title', 'Dashboard · Hadirin.Web')

@php
    /*
    | Definisi warna kartu, ikon, dan label status ditaruh di sini supaya mudah
    | disetel tanpa menyentuh Blade di bawah. Nilainya mengikuti warna yang
    | dipakai dashboard guru dan halaman Rekap supaya angka yang sama selalu
    | dibaca dengan warna yang sama di seluruh aplikasi.
    */
    $tones = [
        'blue' => [
            'chip' => 'bg-gradient-to-br from-blue-500 to-sky-500 text-white shadow-md shadow-blue-500/30',
            'accent' => 'from-blue-500 to-sky-400',
        ],
        'green' => [
            'chip' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/30',
            'accent' => 'from-emerald-500 to-teal-400',
        ],
        'purple' => [
            'chip' => 'bg-gradient-to-br from-violet-500 to-purple-500 text-white shadow-md shadow-violet-500/30',
            'accent' => 'from-violet-500 to-fuchsia-400',
        ],
        'violet' => [
            'chip' => 'bg-gradient-to-br from-violet-600 to-fuchsia-600 text-white shadow-md shadow-violet-600/30',
            'accent' => 'from-violet-600 to-pink-400',
        ],
        'rose' => [
            'chip' => 'bg-gradient-to-br from-rose-500 to-red-500 text-white shadow-md shadow-rose-500/30',
            'accent' => 'from-rose-500 to-red-400',
        ],
        'amber' => [
            'chip' => 'bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-md shadow-amber-500/30',
            'accent' => 'from-amber-500 to-orange-400',
        ],
    ];

    $trenAktif = $tren === 'tahunan' ? $trenTahunan : $trenBulanan;

    // Warna batang per status, mengikuti legenda di bawah grafik.
    $warnaBatang = [
        'hadir' => ['from-emerald-500 to-emerald-400', 'bg-emerald-500'],
        'izin' => ['from-blue-500 to-blue-400', 'bg-blue-500'],
        'sakit' => ['from-amber-500 to-amber-400', 'bg-amber-500'],
        'alpa' => ['from-rose-500 to-red-400', 'bg-rose-500'],
    ];

    $persenRekap = $rekapHariIni['persentase'];
@endphp

@section('konten')
    {{-- banner sapaan --}}
    <div
        class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600 p-6 shadow-lg shadow-indigo-500/20">
        <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-24 right-40 h-56 w-56 rounded-full bg-white/5"></div>
        <div class="pointer-events-none absolute right-56 top-8 h-16 w-16 rotate-12 rounded-2xl bg-white/10"></div>

        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium text-indigo-100">Ringkasan data sekolah hari ini</p>
                <h2 class="mt-1 text-2xl font-bold text-white">
                    Selamat datang, {{ \Illuminate\Support\Str::before(auth()->user()->name ?? 'Admin', ' ') }} 👋
                </h2>
                <p
                    class="mt-2 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white ring-1 ring-white/20">
                    <i class="fas fa-calendar-day"></i>
                    {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('cms.rekap') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-white/15 px-4 py-2.5 text-sm font-semibold text-white ring-1 ring-white/20 transition-colors hover:bg-white/25">
                    <i class="fas fa-clipboard-list"></i> Ringkasan Absensi
                </a>
                <a href="{{ route('cms.rekap.export.excel', ['bulan' => now()->format('Y-m')]) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 shadow-sm transition-colors hover:bg-indigo-50">
                    <i class="fas fa-download"></i> Ekspor CSV
                </a>
            </div>
        </div>
    </div>

    {{--
        Kartu statistik: satu kartu untuk tiap halaman di menu Manajemen.

        Enam kartu ini dibuat ringkas dan dalam satu baris, tapi baris penuh
        baru mulai di `xl`. Sidebar selebar 256px, jadi di `lg` (1024px) tiap
        kartu hanya mendapat sekitar 110px dan label seperti "Mata Pelajaran"
        terpotong. Di bawah itu kartu jadi tiga kolom, lalu dua di ponsel.

        Tata letak di dalam kartu: ikon dan label sebaris di atas, angka besar
        di bawahnya. Bukan ikon di satu sisi dan label di sisi lain, karena
        lebar 150an piksel tidak cukup untuk membelah dua.
    --}}
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
        @foreach ($stats as $stat)
            @php $tone = $tones[$stat['tone']]; @endphp
            <a href="{{ route($stat['route']) }}"
                class="group relative overflow-hidden rounded-xl border border-gray-100 bg-white p-3.5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-indigo-100 hover:shadow-md">
                <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r {{ $tone['accent'] }}"></span>

                <div class="flex items-center gap-2">
                    <span
                        class="grid h-7 w-7 shrink-0 place-items-center rounded-lg text-xs transition-transform duration-300 group-hover:scale-110 {{ $tone['chip'] }}">
                        <i class="{{ $stat['icon'] }}"></i>
                    </span>
                    <span class="truncate text-[11px] font-medium text-gray-500">{{ $stat['label'] }}</span>
                </div>

                <p class="mt-2 text-2xl font-bold leading-none tracking-tight text-gray-800">
                    {{ number_format($stat['value'], 0, ',', '.') }}
                </p>

                @if ($stat['detail'])
                    {{-- Detail dipotong karena kartunya sempit; teks penuhnya
                         ada di `title` dan angka lengkapnya di halaman tujuan. --}}
                    <p class="mt-1.5 truncate text-[11px] text-gray-400" title="{{ $stat['detail'] }}">
                        {{ $stat['detail'] }}
                    </p>
                @else
                    {{-- Sisipan baris kosong supaya semua kartu sama tinggi
                         walau detailnya kosong. --}}
                    <p class="mt-1.5 text-[11px]" aria-hidden="true">&nbsp;</p>
                @endif
            </a>
        @endforeach
    </div>

    {{-- grafik kehadiran + rekap hari ini --}}
    <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="h-full rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                            <i class="fas fa-chart-column"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">Tren Kehadiran</h3>
                            <p class="text-xs text-gray-500">
                                Persentase kehadiran siswa per {{ $tren === 'tahunan' ? 'tahun' : 'bulan' }}
                                &middot; {{ count($trenAktif) }} periode terakhir
                            </p>
                        </div>
                    </div>

                    {{-- toggle bulanan / tahunan. Keduanya memang dihitung server,
                         jadi pindah tab memuat ulang halaman dengan `?tren=`. --}}
                    <div class="flex items-center gap-1 rounded-lg bg-gray-100 p-1 text-xs font-medium">
                        <a href="{{ route('cms.dashboard', array_filter(['tren' => null])) }}"
                            @class([
                                'rounded-md px-3 py-1.5 transition-colors',
                                'bg-white text-gray-800 shadow-sm' => $tren === 'bulanan',
                                'text-gray-500 hover:text-gray-700' => $tren !== 'bulanan',
                            ])>Bulanan</a>
                        <a href="{{ route('cms.dashboard', ['tren' => 'tahunan']) }}"
                            @class([
                                'rounded-md px-3 py-1.5 transition-colors',
                                'bg-white text-gray-800 shadow-sm' => $tren === 'tahunan',
                                'text-gray-500 hover:text-gray-700' => $tren !== 'tahunan',
                            ])>Tahunan</a>
                    </div>
                </div>

                <div class="px-6 py-5">
                    @if (array_sum(array_column($trenAktif, 'total')) > 0)
                        <div class="flex h-56 items-stretch gap-2">
                            @foreach ($trenAktif as $bar)
                                @php
                                    $tinggi = $bar['persentase'];
                                    $isGelap = $bar['total'] === 0;
                                @endphp
                                <div class="flex h-full flex-1 flex-col items-center gap-2"
                                    title="{{ $bar['label'] }}: {{ $bar['persentase'] }}% hadir dari {{ $bar['total'] }} catatan">
                                    <span @class([
                                        'text-[11px] font-medium',
                                        'text-gray-500' => ! $isGelap,
                                        'text-gray-300' => $isGelap,
                                    ])>{{ $bar['persentase'] }}%</span>

                                    <div class="flex w-full flex-1 items-end">
                                        @if ($isGelap)
                                            {{-- Bulan tanpa absensi tetap batangnya kosong,
                                                 bukan diganti angka karangan. --}}
                                            <div
                                                class="w-full rounded-t-md border border-dashed border-gray-200 bg-gray-50"
                                                style="height: 4px"></div>
                                        @else
                                            <div class="flex w-full flex-col justify-end overflow-hidden rounded-t-md"
                                                style="height: {{ max($tinggi, 2) }}%">
                                                @foreach (['alpa', 'sakit', 'izin', 'hadir'] as $status)
                                                    @if ($bar[$status] > 0)
                                                        <div
                                                            class="w-full bg-gradient-to-t {{ $warnaBatang[$status][0] }} {{ $bar[$status] === $bar['total'] ? '' : 'opacity-90' }}"
                                                            style="height: {{ round($bar[$status] / $bar['total'] * 100) }}%"
                                                            title="{{ ucfirst($status) }}: {{ $bar[$status] }}"></div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <span class="text-[11px] text-gray-500">{{ $bar['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="py-16 text-center text-sm text-gray-400">
                            <i class="fas fa-chart-simple mb-2 block text-2xl text-gray-300"></i>
                            Belum ada catatan absensi untuk ditampilkan.
                            <br>
                            <span class="text-xs">Data muncul begitu absensi pertama tercatat di halaman absensi siswa.</span>
                        </p>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-4 border-t border-gray-100 px-6 py-3 text-[11px] font-medium text-gray-500">
                    @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'] as $status => $label)
                        <span class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-sm {{ $warnaBatang[$status][1] }}"></span> {{ $label }}
                        </span>
                    @endforeach
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
                            <h3 class="font-semibold text-gray-800">Rekap Hari Ini</h3>
                            <p class="text-xs text-gray-500">{{ $rekapHariIni['tanggal'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-center px-6 py-6">
                    @if ($rekapHariIni['total'] > 0)
                        <div class="relative h-36 w-36">
                            @php
                                // Segmen disusun manual supaya bisa menyisakan abu-abu
                                // untuk siswa yang belum absen hari ini.
                                $kumulatif = 0;
                                $segmen = [];
                                foreach ($rekapHariIni['baris'] as $baris) {
                                    if ($baris['value'] === 0) {
                                        continue;
                                    }
                                    $mulai = $kumulatif;
                                    $kumulatif += $baris['value'] / $rekapHariIni['total'] * 100;
                                    $segmen[] = $baris['hex'].' '.round($mulai, 2).'% '.round($kumulatif, 2).'%';
                                }
                            @endphp
                            <div id="donutHariIni" class="h-36 w-36 rounded-full transition-all duration-500"
                                style="background: conic-gradient({{ implode(', ', $segmen) }}, #e5e7eb {{ round($kumulatif, 2) }}% 100%);"></div>
                            <div class="absolute inset-[14px] flex flex-col items-center justify-center rounded-full bg-white">
                                <span class="text-3xl font-bold tracking-tight text-gray-800">{{ $persenRekap }}%</span>
                                <span class="text-[11px] font-medium text-gray-500">Hadir</span>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center py-6 text-center">
                            <span class="mb-2 grid h-14 w-14 place-items-center rounded-full bg-gray-100 text-gray-400">
                                <i class="fas fa-calendar-xmark text-xl"></i>
                            </span>
                            <p class="text-sm font-semibold text-gray-700">Belum ada absensi hari ini</p>
                            <p class="mt-0.5 max-w-[13rem] text-xs text-gray-500">
                                {{ number_format($rekapHariIni['siswaTerpantau'], 0, ',', '.') }} siswa terdaftar,
                                belum ada catatan masuk hari ini.
                            </p>
                        </div>
                    @endif
                </div>

                <div class="divide-y divide-gray-100 border-t border-gray-100">
                    @foreach ($rekapHariIni['baris'] as $baris)
                        <div class="flex items-center justify-between px-6 py-3">
                            <span class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="h-2.5 w-2.5 rounded-full {{ $baris['dot'] }}"></span> {{ $baris['label'] }}
                            </span>
                            <span class="text-sm font-semibold text-gray-800">
                                {{ number_format($baris['value'], 0, ',', '.') }}
                                <span class="font-normal text-gray-400">siswa</span>
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 px-6 py-3 text-center text-xs text-gray-500">
                    Total <span class="font-semibold text-gray-800">{{ number_format($rekapHariIni['total'], 0, ',', '.') }}</span>
                    siswa tercatat absensi hari ini
                </div>
            </div>
        </div>
    </div>

    {{-- jadwal hari ini + kelas teramai --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="flex h-full flex-col rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-rose-50 text-rose-600">
                            <i class="fas fa-calendar-days"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">Jadwal Mengajar Hari Ini</h3>
                            <p class="text-xs text-gray-500">
                                {{ $rekapHariIni['tanggal'] }}
                                &middot; {{ $jadwalHariIni->count() }} slot
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('cms.jadwal') }}"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 transition-colors hover:text-indigo-700">
                        Kelola jadwal
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

                @if ($jadwalHariIni->isEmpty())
                    <div class="flex flex-1 flex-col items-center justify-center px-6 py-10 text-center">
                        <span class="mb-2 grid h-12 w-12 place-items-center rounded-full bg-gray-100 text-gray-400">
                            <i class="fas fa-mug-hot mb-1"></i>
                        </span>
                        <p class="text-sm font-semibold text-gray-700">Tidak ada jadwal untuk hari ini</p>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Jadwal dihitung dari kolom <span class="font-semibold">Hari</span> pada halaman Timetables.
                        </p>
                    </div>
                @else
                    <div class="flex-1 divide-y divide-gray-100">
                        @foreach ($jadwalHariIni as $jadwal)
                            <div class="flex flex-wrap items-center gap-4 px-6 py-3.5 transition-colors hover:bg-gray-50">
                                <span class="shrink-0 rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-600">
                                    {{ $jadwal->rentangJam() }}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-gray-800">
                                        {{ $jadwal->mata_pelajaran }}
                                    </span>
                                    <span class="block truncate text-xs text-gray-400">
                                        {{ $jadwal->kelas?->nama_kelas ?? 'Tanpa kelas' }}
                                        &middot; {{ $jadwal->ruang ?: 'Tanpa ruang' }}
                                    </span>
                                </span>
                                <span class="shrink-0 rounded-full bg-purple-50 px-2.5 py-1 text-xs font-medium text-purple-600">
                                    {{ $jadwal->guru?->user?->name ?? 'Tanpa guru' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div>
            <div class="flex h-full flex-col rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-violet-50 text-violet-600">
                            <i class="fas fa-school"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">Kelas Terbanyak</h3>
                            <p class="text-xs text-gray-500">Lima kelas dengan siswa terbanyak</p>
                        </div>
                    </div>
                </div>

                @if (empty($kelasTeramai))
                    <div class="flex flex-1 flex-col items-center justify-center px-6 py-10 text-center">
                        <span class="mb-2 grid h-12 w-12 place-items-center rounded-full bg-gray-100 text-gray-400">
                            <i class="fas fa-users-slash"></i>
                        </span>
                        <p class="text-sm font-semibold text-gray-700">Belum ada siswa</p>
                        <p class="mt-0.5 text-xs text-gray-500">Tambahkan siswa dari menu Students.</p>
                    </div>
                @else
                    <div class="space-y-3 px-6 py-5">
                        @php $maksimum = max(array_column($kelasTeramai, 'siswa')); @endphp
                        @foreach ($kelasTeramai as $baris)
                            <a href="{{ route('cms.student', ['kelas' => $baris['kelas']]) }}"
                                class="block rounded-lg transition-colors hover:bg-gray-50">
                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                    <span class="truncate text-sm font-semibold text-gray-700">{{ $baris['kelas'] }}</span>
                                    <span class="shrink-0 text-xs text-gray-500">
                                        <span class="font-semibold text-gray-800">{{ $baris['siswa'] }}</span> siswa
                                    </span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill bg-violet-500"
                                        style="width: {{ $maksimum > 0 ? round($baris['siswa'] / $maksimum * 100) : 0 }}%"></div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="border-t border-gray-100 px-6 py-3 text-xs text-gray-500">
                    <a href="{{ route('cms.classes') }}"
                        class="font-semibold text-indigo-600 transition-colors hover:text-indigo-700">
                        Lihat semua kelas
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
