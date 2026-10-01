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
    $adaCatatan = $laporan['total']['total'] > 0;

    // Warna bar persentase kehadiran, dipakai bersama tabel per kelas & ringkasan.
    $warnaPersentase = fn (int $nilai): string => match (true) {
        $nilai >= 85 => 'bg-emerald-500',
        $nilai >= 70 => 'bg-amber-500',
        default => 'bg-rose-500',
    };
@endphp

@section('konten')
    {{-- filter periode --}}
    <form method="GET" action="{{ route('guru.laporan') }}"
        class="mb-6 flex flex-col gap-3 rounded-xl border border-gray-100 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
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

        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-500">Dicetak {{ $laporan['dicetak'] }}</span>
            <a href="{{ route('guru.laporan.export', array_filter(['bulan' => $laporan['bulan'], 'kelas' => $kelasAktif])) }}"
                class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition-colors hover:bg-indigo-100">
                <i class="fas fa-file-csv"></i>
                Ekspor CSV
            </a>
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
                <i class="fas fa-filter"></i>
                Terapkan
            </button>
        </div>
    </form>

    @if (! $adaCatatan)
        <div
            class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            <i class="fas fa-circle-info mt-0.5 shrink-0"></i>
            <div>
                <p class="font-semibold">Belum ada catatan absensi pada {{ $laporan['label'] }}.</p>
                <p class="mt-0.5">
                    Laporan ini dihitung dari tabel absensi yang sama dengan dashboard.
                    {{ $laporan['total']['siswa'] }} siswa tetap terdaftar di bawah,
                    tetapi semua kolom kehadiran masih kosong sampai absensi dicatat.
                </p>
            </div>
        </div>
    @endif

    {{-- tren harian --}}
    <div class="mb-6 rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-6 py-4">
            <div class="flex items-center gap-3">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="fas fa-chart-column"></i>
                </span>
                <div>
                    <h3 class="font-semibold text-gray-800">Tren Kehadiran Harian</h3>
                    <p class="text-xs text-gray-500">
                        {{ $laporan['label'] }} &middot; {{ count($laporan['perHari']) }} hari tercatat
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
            @if (count($laporan['perHari']) > 0)
                <div class="chart-container">
                    <canvas id="grafikHarian"></canvas>
                </div>
            @else
                <p class="py-10 text-center text-sm text-gray-400">
                    <i class="fas fa-chart-simple mb-2 block text-2xl text-gray-300"></i>
                    Belum ada absensi harian pada {{ $laporan['label'] }} untuk kelas ini.
                </p>
            @endif
        </div>
    </div>

    {{-- rekap per kelas --}}
    <div class="mb-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <div class="flex items-center gap-3">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-violet-50 text-violet-600">
                    <i class="fas fa-school"></i>
                </span>
                <div>
                    <h3 class="font-semibold text-gray-800">Rekap per Kelas</h3>
                    <p class="text-xs text-gray-500">Ringkasan kehadiran pada {{ $laporan['label'] }}</p>
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
                                <a href="{{ route('guru.laporan', ['bulan' => $laporan['bulan'], 'kelas' => $baris['kelas']]) }}"
                                    class="inline-flex items-center gap-2 font-medium text-gray-800 hover:text-indigo-600">
                                    <span
                                        class="grid h-8 w-8 place-items-center rounded-lg bg-purple-100 text-purple-600">
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
                            <td class="px-4 py-4 text-center font-bold text-gray-800">
                                {{ $laporan['total']['siswa'] }}
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-emerald-600">
                                {{ $laporan['total']['hadir'] }}
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-blue-600">
                                {{ $laporan['total']['izin'] }}
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-amber-600">
                                {{ $laporan['total']['sakit'] }}
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-rose-600">
                                {{ $laporan['total']['alpa'] }}
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-gray-800">
                                {{ $laporan['total']['total'] }}
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-800">
                                {{ $laporan['total']['persentase'] }}%
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- rincian per siswa --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-6 py-4">
            <div class="flex items-center gap-3">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-blue-50 text-blue-600">
                    <i class="fas fa-list-check"></i>
                </span>
                <div>
                    <h3 class="font-semibold text-gray-800">Rincian per Siswa</h3>
                    <p class="text-xs text-gray-500">
                        {{ $kelasAktif ?? 'Semua kelas yang diampu' }} &middot;
                        {{ count($laporan['perSiswa']) }} siswa
                    </p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="px-6 py-3 font-semibold">No</th>
                        <th class="px-4 py-3 font-semibold">Siswa</th>
                        <th class="px-4 py-3 font-semibold">NIS</th>
                        <th class="px-4 py-3 font-semibold">Kelas</th>
                        <th class="px-4 py-3 text-center font-semibold">Hadir</th>
                        <th class="px-4 py-3 text-center font-semibold">Izin</th>
                        <th class="px-4 py-3 text-center font-semibold">Sakit</th>
                        <th class="px-4 py-3 text-center font-semibold">Alpa</th>
                        <th class="px-4 py-3 text-center font-semibold">Hari Tercatat</th>
                        <th class="px-6 py-3 font-semibold">Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($laporan['perSiswa'] as $index => $baris)
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="px-6 py-3 text-gray-400">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-600">
                                        {{ mb_strtoupper(mb_substr($baris['nama'] ?: '?', 0, 1)) }}
                                    </span>
                                    <span class="max-w-[14rem] truncate font-medium text-gray-800">
                                        {{ $baris['nama'] }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $baris['nis'] }}</td>
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
                            <td class="px-4 py-3 text-center text-rose-600">{{ $baris['alpa'] }}</td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ $baris['hari'] }}</td>
                            <td class="px-6 py-3">
                                @if ($baris['total'] > 0)
                                    <div class="flex items-center gap-3">
                                        <div class="progress-bar w-20">
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
                            <td colspan="10" class="px-6 py-10 text-center text-gray-500">
                                Belum ada siswa di kelas yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
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
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @include('guru.partials.data-guru')

    <script>
        // ============================================================
        // LAPORAN BULANAN
        // ============================================================
        // Seluruh angka di halaman ini sudah dihitung di server dari tabel
        // `absensis`, jadi halaman ini tidak perlu JS untuk merangkum apa pun.
        // Yang dikerjakan di sini hanya grafik tren harian.

        const DATA_HARIAN = @json($laporan['perHari']);

        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('grafikHarian');

            if (!canvas || DATA_HARIAN.length === 0 || typeof Chart === 'undefined') return;

            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: DATA_HARIAN.map(function (baris) { return baris.label; }),
                    datasets: [
                        {
                            label: 'Hadir',
                            data: DATA_HARIAN.map(function (baris) { return baris.hadir; }),
                            backgroundColor: '#10b981',
                            borderRadius: { topLeft: 0, topRight: 0, bottomLeft: 4, bottomRight: 4 },
                            borderSkipped: false
                        },
                        {
                            label: 'Izin',
                            data: DATA_HARIAN.map(function (baris) { return baris.izin; }),
                            backgroundColor: '#3b82f6',
                            borderRadius: 4,
                            borderSkipped: false
                        },
                        {
                            label: 'Sakit',
                            data: DATA_HARIAN.map(function (baris) { return baris.sakit; }),
                            backgroundColor: '#f59e0b',
                            borderRadius: 4,
                            borderSkipped: false
                        },
                        {
                            label: 'Alpa',
                            data: DATA_HARIAN.map(function (baris) { return baris.alpa; }),
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
        });
    </script>
@endpush
