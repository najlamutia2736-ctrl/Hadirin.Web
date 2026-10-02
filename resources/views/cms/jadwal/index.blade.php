@extends('layouts.app')

@section('konten')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Jadwal Mengajar</h2>
            <p class="mt-1 text-gray-600">Atur mata pelajaran, kelas, guru, dan jam mengajar setiap hari.</p>
        </div>
        <a href="{{ route('cms.jadwal.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <i class="fas fa-plus"></i>
            Tambah Jadwal
        </a>
    </div>

    @if (session('success'))
        <div role="status"
            class="mb-6 flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <div class="flex gap-3">
                <i class="fas fa-exclamation-circle mt-0.5"></i>
                <div>
                    <p class="font-semibold">Periksa kembali data jadwal.</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <form method="GET" action="{{ route('cms.jadwal') }}"
            class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:max-w-xs">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari mapel, kelas, atau guru..."
                    class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <select name="kelas" aria-label="Filter kelas"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Kelas</option>
                    @foreach ($kelasList as $kelas)
                        <option value="{{ $kelas->id }}" @selected($filterKelas === $kelas->id)>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
                <select name="hari" aria-label="Filter hari"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Hari</option>
                    @foreach ($daftarHari as $hari)
                        <option value="{{ $hari }}" @selected($filterHari === $hari)>{{ $hari }}</option>
                    @endforeach
                </select>
                <select name="status" aria-label="Filter status"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    @foreach ($daftarStatus as $status)
                        <option value="{{ $status }}" @selected($filterStatus === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <button type="submit"
                    class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Terapkan
                </button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Hari &amp; Jam</th>
                        <th class="px-6 py-3">Mata Pelajaran</th>
                        <th class="px-6 py-3">Kelas</th>
                        <th class="px-6 py-3">Guru Pengajar</th>
                        <th class="px-6 py-3">Ruang</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($jadwal as $baris)
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-800">{{ $baris->hari }}</p>
                                        <p class="text-xs text-gray-500">{{ $baris->rentangJam() }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-block rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-600">
                                    {{ $baris->mata_pelajaran }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ $baris->kelas?->nama_kelas ?? 'Kelas terhapus' }}
                                <p class="text-xs text-gray-400">TA {{ $baris->tahun_ajaran }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $baris->guru?->user?->name ?? 'Guru tanpa akun' }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $baris->ruang ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if ($baris->status === 'Aktif')
                                    <span
                                        class="inline-block rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-600">
                                        Aktif
                                    </span>
                                @else
                                    <span
                                        class="inline-block rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('cms.jadwal.edit', $baris) }}" title="Ubah"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 transition-colors hover:bg-blue-50">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <form method="POST" action="{{ route('cms.jadwal.destroy', $baris) }}"
                                        onsubmit="return konfirmasiHapusJadwal(event)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition-colors hover:bg-red-50">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                                @if (request('q') || $filterKelas || $filterHari || $filterStatus)
                                    Tidak ada jadwal yang cocok dengan filter.
                                    <a href="{{ route('cms.jadwal') }}"
                                        class="ml-1 font-semibold text-indigo-600 hover:text-indigo-700">
                                        Reset filter
                                    </a>
                                @else
                                    Belum ada jadwal. Klik "Tambah Jadwal" untuk memulai.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($jadwal->hasPages())
            <div class="border-t border-gray-100 px-6 py-4">
                {{ $jadwal->links() }}
            </div>
        @endif
    </div>

    <script>
        /**
         * Konfirmasi sebelum jadwal dihapus.
         *
         * Jadwal dihapus permanen, jadi tidak diam-diam hilang saat admin salah
         * klik. `confirm` bawaan dipakai supaya tidak perlu modal tambahan.
         */
        function konfirmasiHapusJadwal(event) {
            if (! window.confirm('Hapus jadwal ini? Tindakan ini tidak bisa dibatalkan.')) {
                event.preventDefault();
            }

            return true;
        }
    </script>
@endsection
