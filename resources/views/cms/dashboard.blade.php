@extends('layouts.app')

@section('konten')
    @php
        $today = now()->locale('id');

        $stats = $stats ?? [
            [
                'label' => 'Siswa',
                'value' => '1,248',
                'icon' => 'fas fa-user-graduate',
                'tone' => 'blue',
                'trend' => '+4.2%',
                'up' => true,
                'spark' => [40, 55, 45, 70, 62, 85, 78],
            ],
            [
                'label' => 'Guru',
                'value' => '86',
                'icon' => 'fas fa-chalkboard-teacher',
                'tone' => 'green',
                'trend' => '+1.8%',
                'up' => true,
                'spark' => [55, 50, 62, 58, 70, 66, 74],
            ],
            [
                'label' => 'Kelas',
                'value' => '32',
                'icon' => 'fas fa-book-open',
                'tone' => 'purple',
                'trend' => '0%',
                'up' => null,
                'spark' => [60, 60, 60, 60, 60, 60, 60],
            ],
            [
                'label' => 'Pengguna',
                'value' => '154',
                'icon' => 'fas fa-users-cog',
                'tone' => 'amber',
                'trend' => '-2.1%',
                'up' => false,
                'spark' => [80, 72, 76, 60, 64, 52, 48],
            ],
        ];

        $tones = [
            'blue' => [
                'chip' => 'bg-gradient-to-br from-blue-500 to-sky-500 text-white shadow-md shadow-blue-500/30',
                'soft' => 'bg-blue-50 text-blue-600',
                'bar' => 'bg-blue-500',
                'accent' => 'from-blue-500 to-sky-400',
            ],
            'green' => [
                'chip' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/30',
                'soft' => 'bg-emerald-50 text-emerald-600',
                'bar' => 'bg-emerald-500',
                'accent' => 'from-emerald-500 to-teal-400',
            ],
            'purple' => [
                'chip' => 'bg-gradient-to-br from-violet-500 to-purple-500 text-white shadow-md shadow-violet-500/30',
                'soft' => 'bg-violet-50 text-violet-600',
                'bar' => 'bg-violet-500',
                'accent' => 'from-violet-500 to-fuchsia-400',
            ],
            'amber' => [
                'chip' => 'bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-md shadow-amber-500/30',
                'soft' => 'bg-amber-50 text-amber-600',
                'bar' => 'bg-amber-500',
                'accent' => 'from-amber-500 to-orange-400',
            ],
        ];

        $attendancePerMonth = $attendancePerMonth ?? [
            ['month' => 'Jan', 'value' => 92],
            ['month' => 'Feb', 'value' => 95],
            ['month' => 'Mar', 'value' => 89],
            ['month' => 'Apr', 'value' => 94],
            ['month' => 'Mei', 'value' => 91],
            ['month' => 'Jun', 'value' => 96],
            ['month' => 'Jul', 'value' => 88],
            ['month' => 'Ags', 'value' => 93],
            ['month' => 'Sep', 'value' => 96],
            ['month' => 'Okt', 'value' => 90],
            ['month' => 'Nov', 'value' => 94],
            ['month' => 'Des', 'value' => 92],
        ];

        $todayAttendance = $todayAttendance ?? [
            ['label' => 'Hadir', 'value' => 216, 'color' => 'bg-green-500', 'text' => 'text-green-600'],
            ['label' => 'Izin', 'value' => 8, 'color' => 'bg-blue-500', 'text' => 'text-blue-600'],
            ['label' => 'Sakit', 'value' => 5, 'color' => 'bg-amber-500', 'text' => 'text-amber-600'],
            ['label' => 'Alpa', 'value' => 3, 'color' => 'bg-red-500', 'text' => 'text-red-600'],
        ];

        $totalToday = array_sum(array_column($todayAttendance, 'value'));
        $presentToday = $todayAttendance[0]['value'];
        $presentPercent = (int) round(($presentToday / max($totalToday, 1)) * 100);
    @endphp

    {{-- banner sapaan --}}
    <div
        class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600 p-6 shadow-lg shadow-indigo-500/20">
        <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-24 right-40 h-56 w-56 rounded-full bg-white/5"></div>
        <div class="pointer-events-none absolute right-56 top-8 h-16 w-16 rotate-12 rounded-2xl bg-white/10"></div>

        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium text-indigo-100">Ringkasan data sekolah hari ini</p>
                <h2 class="mt-1 text-2xl font-bold text-white">Selamat datang, Admin 👋</h2>
                <p
                    class="mt-2 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white ring-1 ring-white/20">
                    <i class="fas fa-calendar-day"></i>
                    {{ $today->isoFormat('dddd, D MMMM YYYY') }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-white/15 px-4 py-2.5 text-sm font-semibold text-white ring-1 ring-white/20 transition-colors hover:bg-white/25">
                    <i class="fas fa-download"></i> Ekspor
                </button>
            </div>
        </div>
    </div>

    {{-- kartu statistik --}}
    <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            @php $tone = $tones[$stat['tone']]; @endphp
            <div
                class="group relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $tone['accent'] }}"></span>

                <div class="flex items-start justify-between">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl text-lg transition-transform duration-300 group-hover:scale-110 {{ $tone['chip'] }}">
                        <i class="{{ $stat['icon'] }}"></i>
                    </div>
                    <span @class([
                        'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold',
                        'bg-green-50 text-green-600' => $stat['up'] === true,
                        'bg-red-50 text-red-600' => $stat['up'] === false,
                        'bg-gray-100 text-gray-500' => $stat['up'] === null,
                    ])>
                        @if ($stat['up'] === true)
                            <i class="fas fa-arrow-up text-[9px]"></i>
                        @elseif ($stat['up'] === false)
                            <i class="fas fa-arrow-down text-[9px]"></i>
                        @endif
                        {{ $stat['trend'] }}
                    </span>
                </div>

                <div class="mt-4 flex items-end justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                        <p class="mt-0.5 text-2xl font-bold tracking-tight text-gray-800">{{ $stat['value'] }}</p>
                    </div>
                    {{-- sparkline mini --}}
                    <div class="flex h-9 items-end gap-[3px]" aria-hidden="true">
                        @foreach ($stat['spark'] as $bar)
                            <span
                                class="w-1.5 rounded-t-sm opacity-60 transition-all group-hover:opacity-100 {{ $tone['bar'] }}"
                                style="height: {{ $bar }}%"></span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- grafik kehadiran + rekap hari ini --}}
    <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="h-full rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                            <i class="fas fa-chart-bar"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">Tren Kehadiran (%)</h3>
                            <p class="text-xs text-gray-500">Persentase kehadiran siswa per bulan</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 rounded-lg bg-gray-100 p-1 text-xs font-medium">
                        <button type="button"
                            class="rounded-md bg-white px-3 py-1.5 text-gray-800 shadow-sm">Bulanan</button>
                        <button type="button"
                            class="rounded-md px-3 py-1.5 text-gray-500 hover:text-gray-700">Tahunan</button>
                    </div>
                </div>

                <div class="px-6 py-5">
                    <div class="flex h-56 items-stretch gap-2">
                        @foreach ($attendancePerMonth as $bar)
                            <div class="flex h-full flex-1 flex-col items-center gap-2">
                                <span class="text-[11px] font-medium text-gray-500">{{ $bar['value'] }}</span>
                                <div class="flex w-full flex-1 items-end">
                                    <div class="w-full rounded-t-md bg-gradient-to-t from-indigo-500 to-indigo-400 transition-all hover:from-indigo-600 hover:to-indigo-500"
                                        style="height: {{ $bar['value'] }}%"
                                        title="{{ $bar['month'] }}: {{ $bar['value'] }}%"></div>
                                </div>
                                <span class="text-[11px] text-gray-500">{{ $bar['month'] }}</span>
                            </div>
                        @endforeach
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
                            <h3 class="font-semibold text-gray-800">Rekap Hari Ini</h3>
                            <p class="text-xs text-gray-500">{{ $today->isoFormat('dddd, D MMMM YYYY') }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-center px-6 py-6">
                    <div class="relative h-36 w-36">
                        <div class="h-36 w-36 rounded-full"
                            style="background: conic-gradient(#22c55e 0 {{ $presentPercent }}%, #e5e7eb {{ $presentPercent }}% 100%);">
                        </div>
                        <div class="absolute inset-[14px] flex flex-col items-center justify-center rounded-full bg-white">
                            <span class="text-3xl font-bold tracking-tight text-gray-800">{{ $presentPercent }}%</span>
                            <span class="text-[11px] font-medium text-gray-500">Hadir</span>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-gray-100 border-t border-gray-100">
                    @foreach ($todayAttendance as $row)
                        <div class="flex items-center justify-between px-6 py-3">
                            <span class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="h-2.5 w-2.5 rounded-full {{ $row['color'] }}"></span> {{ $row['label'] }}
                            </span>
                            <span class="text-sm font-semibold text-gray-800">{{ $row['value'] }}
                                <span class="font-normal text-gray-400">siswa</span></span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 px-6 py-3 text-center text-xs text-gray-500">
                    Total <span class="font-semibold text-gray-800">{{ $totalToday }}</span> siswa terpantau hari ini
                </div>
            </div>
        </div>
    </div>

@endsection
