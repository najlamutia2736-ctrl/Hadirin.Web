@extends('layouts.app')

@section('konten')
    {{-- $rekap, $total, $periode, $pilihanBulan, $pilihanKelas dikirim oleh RekapController --}}

    {{-- header halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Rekap Kehadiran</h2>
            <p class="mt-1 text-gray-600">Ringkasan kehadiran siswa per kelas dan per bulan.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('cms.rekap.export.pdf', request()->except('page')) }}" target="_blank" rel="noopener"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50">
                <i class="fas fa-file-pdf text-red-500"></i>
                Export PDF
            </a>
            <a href="{{ route('cms.rekap.export.excel', request()->except('page')) }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
                <i class="fas fa-file-excel text-green-300"></i>
                Export Excel
            </a>
        </div>
    </div>

    {{-- filter periode --}}
    <form method="GET" action="{{ route('cms.rekap') }}"
        class="mb-8 flex flex-col gap-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
        <div class="flex-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700" for="bulan">Bulan</label>
            <select id="bulan" name="bulan" data-filter-bulan
                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="">Semua Periode</option>
                @foreach ($pilihanBulan as $bulan)
                    <option value="{{ $bulan['nilai'] }}" @selected($periode['bulan'] === $bulan['nilai'])>
                        {{ $bulan['label'] }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700" for="kelas">Kelas</label>
            <select id="kelas" name="kelas"
                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="">Semua Kelas</option>
                @foreach ($pilihanKelas as $kelas)
                    <option value="{{ $kelas }}" @selected($periode['kelas'] === $kelas)>{{ $kelas }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700" for="dari">Dari Tanggal</label>
            <input id="dari" name="dari" type="date" value="{{ $periode['dari']->format('Y-m-d') }}"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>
        <div class="flex-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700" for="sampai">Sampai Tanggal</label>
            <input id="sampai" name="sampai" type="date" value="{{ $periode['sampai']->format('Y-m-d') }}"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>
        <input type="hidden" name="q" value="{{ $periode['q'] }}">
        <button type="submit"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-700">
            <i class="fas fa-filter"></i>
            Terapkan
        </button>
    </form>

    {{-- tabel rekap per kelas --}}
    <div class="mt-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <h3 class="font-semibold text-gray-800">Rekap per Kelas</h3>
            <form method="GET" action="{{ route('cms.rekap') }}" class="relative">
                @foreach (['bulan' => $periode['bulan'], 'kelas' => $periode['kelas'], 'dari' => $periode['dari']->format('Y-m-d'), 'sampai' => $periode['sampai']->format('Y-m-d')] as $nama => $nilai)
                    @if ($nilai)
                        <input type="hidden" name="{{ $nama }}" value="{{ $nilai }}">
                    @endif
                @endforeach
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input type="search" name="q" value="{{ $periode['q'] }}" placeholder="Cari kelas..."
                    class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:w-56">
            </form>
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
                    @forelse ($rekap as $row)
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <span class="inline-block rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-600">
                                    {{ $row['kelas'] }}
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
                                        <div class="h-full rounded-full {{ $row['persentase'] >= 90 ? 'bg-green-500' : ($row['persentase'] >= 75 ? 'bg-amber-500' : 'bg-red-500') }}"
                                            style="width: {{ $row['persentase'] }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-700">{{ $row['persentase'] }}%</span>
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
                @if (count($rekap) > 0)
                    <tfoot class="border-t-2 border-gray-200 bg-gray-50 text-sm font-semibold text-gray-800">
                        <tr>
                            <td class="px-6 py-3">Total ({{ count($rekap) }} kelas)</td>
                            <td class="px-6 py-3">{{ $total['students'] }}</td>
                            <td class="px-6 py-3 text-green-600">{{ $total['hadir'] }}</td>
                            <td class="px-6 py-3 text-blue-600">{{ $total['izin'] }}</td>
                            <td class="px-6 py-3 text-amber-600">{{ $total['sakit'] }}</td>
                            <td class="px-6 py-3 text-red-600">{{ $total['alpa'] }}</td>
                            <td class="px-6 py-3">{{ $total['persentase'] }}%</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <div class="border-t border-gray-200 px-6 py-4">
            <p class="text-xs text-gray-500">
                Data periode <span class="font-medium text-gray-700">{{ $periode['label'] }}</span>
                @if ($periode['kelas'])
                    · Kelas <span class="font-medium text-gray-700">{{ $periode['kelas'] }}</span>
                @endif
            </p>
            <p class="mt-1 text-xs text-gray-400">
                Persentase kehadiran dihitung dari hadir dibagi total catatan absensi pada periode tersebut.
            </p>
        </div>
    </div>

    <script>
        // Saat bulan dipilih, rentang tanggal ikut disesuaikan ke awal & akhir bulan tersebut.
        (function() {
            const selectBulan = document.querySelector('[data-filter-bulan]');
            const inputDari = document.getElementById('dari');
            const inputSampai = document.getElementById('sampai');

            if (!selectBulan || !inputDari || !inputSampai) {
                return;
            }

            const pad = (angka) => String(angka).padStart(2, '0');

            selectBulan.addEventListener('change', function() {
                if (!this.value) {
                    return;
                }

                const [tahun, bulan] = this.value.split('-').map(Number);
                const pertama = new Date(tahun, bulan - 1, 1);
                const terakhir = new Date(tahun, bulan, 0);

                inputDari.value = `${tahun}-${pad(bulan)}-01`;
                inputSampai.value = `${terakhir.getFullYear()}-${pad(terakhir.getMonth() + 1)}-${pad(terakhir.getDate())}`;
            });
        })();
    </script>
@endsection
