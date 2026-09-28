@extends('layouts.app')

@section('konten')
    {{-- data contoh, nanti diganti dari controller: return view('cms.rekap', ['recap' => $recap]) --}}
    @php
        $recap = $recap ?? [
            ['class' => 'X-A', 'students' => 36, 'hadir' => 34, 'izin' => 1, 'sakit' => 1, 'alpa' => 0],
            ['class' => 'X-B', 'students' => 35, 'hadir' => 32, 'izin' => 2, 'sakit' => 1, 'alpa' => 0],
            ['class' => 'X-C', 'students' => 34, 'hadir' => 30, 'izin' => 1, 'sakit' => 2, 'alpa' => 1],
            ['class' => 'XI-A', 'students' => 33, 'hadir' => 32, 'izin' => 0, 'sakit' => 1, 'alpa' => 0],
            ['class' => 'XI-B', 'students' => 34, 'hadir' => 29, 'izin' => 2, 'sakit' => 2, 'alpa' => 1],
            ['class' => 'XII-A', 'students' => 32, 'hadir' => 31, 'izin' => 1, 'sakit' => 0, 'alpa' => 0],
            ['class' => 'XII-B', 'students' => 31, 'hadir' => 28, 'izin' => 1, 'sakit' => 1, 'alpa' => 1],
        ];

        $percent = fn (array $row): int => (int) round($row['hadir'] / max($row['students'], 1) * 100);
    @endphp

    {{-- header halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Rekap Kehadiran</h2>
            <p class="mt-1 text-gray-600">Ringkasan kehadiran siswa per kelas dan per bulan.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <button type="button"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50">
                <i class="fas fa-file-pdf text-red-500"></i>
                Export PDF
            </button>
            <button type="button"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
                <i class="fas fa-file-excel text-green-300"></i>
                Export Excel
            </button>
        </div>
    </div>

    {{-- filter periode --}}
    <div class="mb-8 flex flex-col gap-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
        <div class="flex-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700" for="bulan">Bulan</label>
            <select id="bulan"
                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option>September 2026</option>
                <option>Agustus 2026</option>
                <option>Juli 2026</option>
                <option>Juni 2026</option>
            </select>
        </div>
        <div class="flex-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700" for="kelas">Kelas</label>
            <select id="kelas"
                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="">Semua Kelas</option>
                <option>X-A</option>
                <option>X-B</option>
                <option>X-C</option>
                <option>XI-A</option>
                <option>XI-B</option>
                <option>XII-A</option>
                <option>XII-B</option>
            </select>
        </div>
        <div class="flex-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700" for="dari">Dari Tanggal</label>
            <input id="dari" type="date" value="2026-09-01"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>
        <div class="flex-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700" for="sampai">Sampai Tanggal</label>
            <input id="sampai" type="date" value="2026-09-22"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>
        <button type="button"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-700">
            <i class="fas fa-filter"></i>
            Terapkan
        </button>
    </div>

    {{-- tabel rekap per kelas --}}
    <div class="mt-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <h3 class="font-semibold text-gray-800">Rekap per Kelas</h3>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input type="search" placeholder="Cari kelas..."
                    class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:w-56">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Kelas</th>
                        <th class="px-6 py-3">Jumlah Siswa</th>
                        <th class="px-6 py-3">Hadir</th>
                        <th class="px-6 py-3">Izin</th>
                        <th class="px-6 py-3">Sakit</th>
                        <th class="px-6 py-3">Alpa</th>
                        <th class="px-6 py-3">Persentase Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recap as $row)
                        @php
                            $pct = $percent($row);
                        @endphp
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <span class="inline-block rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-600">
                                    {{ $row['class'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $row['students'] }}</td>
                            <td class="px-6 py-4 font-medium text-green-600">{{ $row['hadir'] }}</td>
                            <td class="px-6 py-4 text-blue-600">{{ $row['izin'] }}</td>
                            <td class="px-6 py-4 text-amber-600">{{ $row['sakit'] }}</td>
                            <td class="px-6 py-4 text-red-600">{{ $row['alpa'] }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-2 w-40 overflow-hidden rounded-full bg-gray-100">
                                        <div class="h-full rounded-full {{ $pct >= 90 ? 'bg-green-500' : ($pct >= 75 ? 'bg-amber-500' : 'bg-red-500') }}"
                                            style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-700">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                Belum ada data rekap.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-6 py-4">
            <p class="text-xs text-gray-500">
                Data periode <span class="font-medium text-gray-700">1 – 22 September 2026</span>
            </p>
        </div>
    </div>
@endsection
