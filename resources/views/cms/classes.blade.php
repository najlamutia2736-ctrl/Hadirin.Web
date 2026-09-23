@extends('layouts.app')

@section('konten')
    {{-- data contoh, nanti diganti dari controller: return view('cms.classes', ['classes' => $classes]) --}}
    @php
        $classes = $classes ?? [
            ['name' => 'X-A', 'level' => 'X', 'homeroom' => 'Budi Santoso, M.Pd.', 'students' => 36, 'room' => 'R. 101', 'status' => 'Aktif'],
            ['name' => 'X-B', 'level' => 'X', 'homeroom' => 'Joko Prasetyo, S.Pd.', 'students' => 35, 'room' => 'R. 102', 'status' => 'Aktif'],
            ['name' => 'X-C', 'level' => 'X', 'homeroom' => 'Maya Sari, S.Pd.', 'students' => 34, 'room' => 'R. 103', 'status' => 'Aktif'],
            ['name' => 'XI-A', 'level' => 'XI', 'homeroom' => 'Siti Nurhaliza, S.Pd.', 'students' => 33, 'room' => 'R. 201', 'status' => 'Aktif'],
            ['name' => 'XI-B', 'level' => 'XI', 'homeroom' => 'Agus Wijaya, S.Kom.', 'students' => 34, 'room' => 'R. 202', 'status' => 'Aktif'],
            ['name' => 'XII-A', 'level' => 'XII', 'homeroom' => 'Dewi Anggraini, S.Pd.', 'students' => 32, 'room' => 'R. 301', 'status' => 'Aktif'],
            ['name' => 'XII-B', 'level' => 'XII', 'homeroom' => 'Ratna Dewi, S.Pd.', 'students' => 31, 'room' => 'R. 302', 'status' => 'Arsip'],
        ];
    @endphp

    {{-- header halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Kelas</h2>
            <p class="mt-1 text-gray-600">Kelola daftar kelas, wali kelas, dan rombongan belajar.</p>
        </div>
        <button type="button" data-modal-open="modal-tambah"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
            <i class="fas fa-plus"></i>
            Tambah Kelas
        </button>
    </div>

    {{-- kartu statistik --}}
    <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="flex items-center rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-50 text-xl text-purple-600">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Kelas</p>
                <p class="text-2xl font-bold text-gray-800">32</p>
            </div>
        </div>
        <div class="flex items-center rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-xl text-blue-600">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Tingkat X</p>
                <p class="text-2xl font-bold text-gray-800">11</p>
            </div>
        </div>
        <div class="flex items-center rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-50 text-xl text-green-600">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Tingkat XI</p>
                <p class="text-2xl font-bold text-gray-800">11</p>
            </div>
        </div>
        <div class="flex items-center rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 text-xl text-amber-600">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Tingkat XII</p>
                <p class="text-2xl font-bold text-gray-800">10</p>
            </div>
        </div>
    </div>

    {{-- tabel kelas --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        {{-- toolbar: pencarian & filter --}}
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:max-w-xs">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input type="search" placeholder="Cari kelas atau wali kelas..."
                    class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <select
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Tingkat</option>
                    <option>X</option>
                    <option>XI</option>
                    <option>XII</option>
                </select>
                <select
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option>Aktif</option>
                    <option>Arsip</option>
                </select>
            </div>
        </div>

        {{-- tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Kelas</th>
                        <th class="px-6 py-3">Tingkat</th>
                        <th class="px-6 py-3">Wali Kelas</th>
                        <th class="px-6 py-3">Jumlah Siswa</th>
                        <th class="px-6 py-3">Ruang</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($classes as $class)
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-purple-100 text-sm font-semibold text-purple-600">
                                        <i class="fas fa-chalkboard"></i>
                                    </div>
                                    <p class="ml-3 font-medium text-gray-800">{{ $class['name'] }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-600">
                                    Tingkat {{ $class['level'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $class['homeroom'] }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-700">{{ $class['students'] }}</span>
                                    <div class="h-1.5 w-20 overflow-hidden rounded-full bg-gray-100">
                                        <div class="h-full rounded-full bg-indigo-500"
                                            style="width: {{ min(round($class['students'] / 40 * 100), 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $class['room'] }}</td>
                            <td class="px-6 py-4">
                                @if ($class['status'] === 'Aktif')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-600">
                                        <span class="h-2 w-2 rounded-full bg-green-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-400">
                                        <span class="h-2 w-2 rounded-full bg-gray-300"></span> Arsip
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" title="Ubah"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 transition-colors hover:bg-blue-50">
                                        <i class="fas fa-pen text-xs"></i>
                                    </button>
                                    <button type="button" title="Lihat Siswa"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-purple-600 transition-colors hover:bg-purple-50">
                                        <i class="fas fa-users text-xs"></i>
                                    </button>
                                    <button type="button" title="Hapus"
                                        data-modal-open="modal-hapus"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition-colors hover:bg-red-50">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                Belum ada data kelas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- footer tabel (pagination) --}}
        <div class="flex flex-col gap-3 border-t border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-gray-500">
                Menampilkan {{ count($classes) }} dari <span class="font-medium text-gray-700">32</span> kelas
            </p>
            <div class="flex items-center gap-1">
                <button type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:bg-gray-50">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>
                <button type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-xs font-semibold text-white">1</button>
                <button type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-xs text-gray-600 hover:bg-gray-50">2</button>
                <button type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-xs text-gray-600 hover:bg-gray-50">3</button>
                <button type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-chevron-right text-xs"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- modal: tambah kelas --}}
    <div id="modal-tambah" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h3 class="font-semibold text-gray-800">Tambah Kelas</h3>
                <button type="button" data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form class="px-6 py-4" method="POST" action="#">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700" for="name">Nama Kelas</label>
                        <input id="name" name="name" type="text" required placeholder="Contoh: X-D"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700" for="level">Tingkat</label>
                        <select id="level" name="level"
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option>X</option>
                            <option>XI</option>
                            <option>XII</option>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="homeroom">Wali Kelas</label>
                    <input id="homeroom" name="homeroom" type="text" placeholder="Nama wali kelas"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="room">Ruang Kelas</label>
                    <input id="room" name="room" type="text" placeholder="Contoh: R. 104"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" data-modal-close
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- modal: konfirmasi hapus --}}
    <div id="modal-hapus" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-sm rounded-xl bg-white shadow-xl">
            <div class="px-6 py-5 text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600">
                    <i class="fas fa-trash"></i>
                </div>
                <h3 class="font-semibold text-gray-800">Hapus Kelas?</h3>
                <p class="mt-1 text-sm text-gray-500">Kelas yang dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="flex gap-3 border-t border-gray-200 px-6 py-4">
                <button type="button" data-modal-close
                    class="flex-1 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="button" data-modal-close
                    class="flex-1 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Hapus</button>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-modal-open]').forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                var modal = document.getElementById(trigger.dataset.modalOpen);
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach(function(button) {
            button.addEventListener('click', function() {
                var modal = button.closest('.fixed');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        });

        document.querySelectorAll('.fixed').forEach(function(modal) {
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });
        });
    </script>
@endsection
