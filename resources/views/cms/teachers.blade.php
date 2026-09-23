@extends('layouts.app')

@section('konten')
    {{-- data contoh, nanti diganti dari controller: return view('cms.teachers', ['teachers' => $teachers]) --}}
    @php
        $teachers = $teachers ?? [
            [
                'name' => 'Siti Nurhaliza, S.Pd.',
                'nip' => '19870312 201101 2 004',
                'subject' => 'Matematika',
                'classes' => 'XI-A, XI-B, XII-A',
                'status' => 'Aktif',
                'phone' => '0812-3456-7890',
            ],
            [
                'name' => 'Budi Santoso, M.Pd.',
                'nip' => '19820510 200801 1 006',
                'subject' => 'Bahasa Indonesia',
                'classes' => 'X-A, X-B, X-C',
                'status' => 'Aktif',
                'phone' => '0813-9876-5432',
            ],
            [
                'name' => 'Maya Sari, S.Pd.',
                'nip' => '19901125 201503 2 002',
                'subject' => 'Bahasa Inggris',
                'classes' => 'X-A, XI-A',
                'status' => 'Cuti',
                'phone' => '0857-1122-3344',
            ],
            [
                'name' => 'Agus Wijaya, S.Kom.',
                'nip' => '19881203 201402 1 005',
                'subject' => 'Informatika',
                'classes' => 'XI-A, XI-B, XII-A',
                'status' => 'Aktif',
                'phone' => '0821-5566-7788',
            ],
            [
                'name' => 'Dewi Anggraini, S.Pd.',
                'nip' => '19920718 201701 2 003',
                'subject' => 'IPA / Fisika',
                'classes' => 'X-B, XII-A, XII-B',
                'status' => 'Aktif',
                'phone' => '0819-4433-2211',
            ],
            [
                'name' => 'Hendra Kusuma, S.Pd.',
                'nip' => '19790422 200604 1 001',
                'subject' => 'Sejarah',
                'classes' => 'X-C, XI-B',
                'status' => 'Nonaktif',
                'phone' => '0813-2211-9900',
            ],
            [
                'name' => 'Ratna Dewi, S.Pd.',
                'nip' => '19941009 201903 2 007',
                'subject' => 'Kimia',
                'classes' => 'XII-A, XII-B',
                'status' => 'Aktif',
                'phone' => '0856-7788-9911',
            ],
            [
                'name' => 'Joko Prasetyo, S.Pd.',
                'nip' => '19850614 201001 1 009',
                'subject' => 'PJOK',
                'classes' => 'X-A, X-B, XI-A',
                'status' => 'Aktif',
                'phone' => '0812-6655-4433',
            ],
        ];
    @endphp

    {{-- header halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Guru</h2>
            <p class="mt-1 text-gray-600">Kelola data guru, mata pelajaran, dan status mengajar.</p>
        </div>
        <button type="button" data-modal-open="modal-tambah"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
            <i class="fas fa-chalkboard-teacher"></i>
            Tambah Guru
        </button>
    </div>

    {{-- kartu statistik --}}
    <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="flex items-center rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-xl text-blue-600">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Guru</p>
                <p class="text-2xl font-bold text-gray-800">86</p>
            </div>
        </div>
        <div class="flex items-center rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-50 text-xl text-green-600">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Aktif Mengajar</p>
                <p class="text-2xl font-bold text-gray-800">81</p>
            </div>
        </div>
        <div class="flex items-center rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 text-xl text-amber-600">
                <i class="fas fa-plane-departure"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Cuti / Izin</p>
                <p class="text-2xl font-bold text-gray-800">3</p>
            </div>
        </div>
        <div class="flex items-center rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-50 text-xl text-purple-600">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Mata Pelajaran</p>
                <p class="text-2xl font-bold text-gray-800">14</p>
            </div>
        </div>
    </div>

    {{-- tabel guru --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        {{-- toolbar: pencarian & filter --}}
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:max-w-xs">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input type="search" placeholder="Cari nama, NIP, atau mapel..."
                    class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <select
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Mapel</option>
                    <option>Matematika</option>
                    <option>Bahasa Indonesia</option>
                    <option>Bahasa Inggris</option>
                    <option>Informatika</option>
                    <option>IPA / Fisika</option>
                    <option>Kimia</option>
                    <option>Sejarah</option>
                    <option>PJOK</option>
                </select>
                <select
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option>Aktif</option>
                    <option>Cuti</option>
                    <option>Nonaktif</option>
                </select>
            </div>
        </div>

        {{-- tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Guru</th>
                        <th class="px-6 py-3">NIP</th>
                        <th class="px-6 py-3">Mata Pelajaran</th>
                        <th class="px-6 py-3">Kelas Diampu</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($teachers as $teacher)
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600">
                                        {{ strtoupper(substr($teacher['name'], 0, 1)) }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-800">{{ $teacher['name'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $teacher['phone'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $teacher['nip'] }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-block rounded-full bg-purple-50 px-2.5 py-1 text-xs font-medium text-purple-600">
                                    {{ $teacher['subject'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $teacher['classes'] }}</td>
                            <td class="px-6 py-4">
                                @if ($teacher['status'] === 'Aktif')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-600">
                                        <span class="h-2 w-2 rounded-full bg-green-500"></span> Aktif
                                    </span>
                                @elseif ($teacher['status'] === 'Cuti')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-600">
                                        <span class="h-2 w-2 rounded-full bg-amber-500"></span> Cuti
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-400">
                                        <span class="h-2 w-2 rounded-full bg-gray-300"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" title="Ubah"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 transition-colors hover:bg-blue-50">
                                        <i class="fas fa-pen text-xs"></i>
                                    </button>
                                    <button type="button" title="Lihat Jadwal"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-purple-600 transition-colors hover:bg-purple-50">
                                        <i class="fas fa-calendar-alt text-xs"></i>
                                    </button>
                                    <button type="button" title="Hapus" data-modal-open="modal-hapus"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition-colors hover:bg-red-50">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                Belum ada data guru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- footer tabel (pagination) --}}
        <div class="flex flex-col gap-3 border-t border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-gray-500">
                Menampilkan {{ count($teachers) }} dari <span class="font-medium text-gray-700">86</span> guru
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

    {{-- modal: tambah guru --}}
    <div id="modal-tambah" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h3 class="font-semibold text-gray-800">Tambah Guru</h3>
                <button type="button" data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form class="px-6 py-4" method="POST" action="#">
                @csrf
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="name">Nama Lengkap &
                        Gelar</label>
                    <input id="name" name="name" type="text" required
                        placeholder="Contoh: Siti Nurhaliza, S.Pd."
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="nip">NIP</label>
                    <input id="nip" name="nip" type="text" required placeholder="18 digit NIP"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="subject">Mata Pelajaran</label>
                    <input id="subject" name="subject" type="text" required placeholder="Contoh: Matematika"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="phone">No. Telepon</label>
                    <input id="phone" name="phone" type="text" placeholder="08xx-xxxx-xxxx"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="status">Status</label>
                    <select id="status" name="status"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option>Aktif</option>
                        <option>Cuti</option>
                        <option>Nonaktif</option>
                    </select>
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
                <h3 class="font-semibold text-gray-800">Hapus Guru?</h3>
                <p class="mt-1 text-sm text-gray-500">Data guru yang dihapus tidak dapat dikembalikan.</p>
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
