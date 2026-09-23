@extends('layouts.app')

@section('konten')
    {{-- pesan sukses --}}
    @if (session('success'))
        <div class="mb-6 flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- header halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Siswa</h2>
            <p class="mt-1 text-gray-600">Kelola data siswa, kelas, dan status keaktifan.</p>
        </div>
        <button type="button" data-modal-open="modal-tambah"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
            <i class="fas fa-user-plus"></i>
            Tambah Siswa
        </button>
    </div>

    {{-- kartu statistik --}}
    <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="flex items-center rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-xl text-blue-600">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Siswa</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats->total) }}</p>
            </div>
        </div>
        <div class="flex items-center rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 text-xl text-indigo-600">
                <i class="fas fa-mars"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Laki-laki</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats->laki) }}</p>
            </div>
        </div>
        <div class="flex items-center rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-pink-50 text-xl text-pink-600">
                <i class="fas fa-venus"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Perempuan</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats->perempuan) }}</p>
            </div>
        </div>
        <div class="flex items-center rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-50 text-xl text-purple-600">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Jumlah Kelas</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats->kelas) }}</p>
            </div>
        </div>
    </div>

    {{-- tabel siswa --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        {{-- toolbar: pencarian & filter --}}
        <form method="GET" action="{{ route('cms.students') }}"
            class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:max-w-xs">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau NIS..."
                    class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <select name="kelas"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Kelas</option>
                    <option value="X-A" @selected(request('kelas') === 'X-A')>X-A</option>
                    <option value="X-B" @selected(request('kelas') === 'X-B')>X-B</option>
                    <option value="XI-A" @selected(request('kelas') === 'XI-A')>XI-A</option>
                    <option value="XI-B" @selected(request('kelas') === 'XI-B')>XI-B</option>
                    <option value="XII-A" @selected(request('kelas') === 'XII-A')>XII-A</option>
                </select>
                <select name="status"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="Aktif" @selected(request('status') === 'Aktif')>Aktif</option>
                    <option value="Nonaktif" @selected(request('status') === 'Nonaktif')>Nonaktif</option>
                    <option value="Pindah" @selected(request('status') === 'Pindah')>Pindah</option>
                </select>
                <button type="submit"
                    class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Terapkan
                </button>
            </div>
        </form>

        {{-- tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Siswa</th>
                        <th class="px-6 py-3">NIS</th>
                        <th class="px-6 py-3">Kelas</th>
                        <th class="px-6 py-3">Jenis Kelamin</th>
                        <th class="px-6 py-3">Wali / Orang Tua</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($students as $student)
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-green-100 text-sm font-semibold text-green-600">
                                        {{ strtoupper(substr($student->user->name, 0, 1)) }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-800">{{ $student->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $student->telepon_wali }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $student->nisn }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-block rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-600">
                                    {{ $student->kelas }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $student->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $student->wali }}</td>
                            <td class="px-6 py-4">
                                @if ($student->status === 'Aktif')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-600">
                                        <span class="h-2 w-2 rounded-full bg-green-500"></span> Aktif
                                    </span>
                                @elseif ($student->status === 'Pindah')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-600">
                                        <span class="h-2 w-2 rounded-full bg-amber-500"></span> Pindah
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
                                    <button type="button" title="Lihat Detail"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-purple-600 transition-colors hover:bg-purple-50">
                                        <i class="fas fa-eye text-xs"></i>
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
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                Belum ada data siswa.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- footer tabel (pagination) --}}
        <div class="flex flex-col gap-3 border-t border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-gray-500">
                Menampilkan {{ $students->count() }} dari <span class="font-medium text-gray-700">{{ $students->total() }}</span> siswa
            </p>
            {{ $students->links() }}
        </div>
    </div>

    {{-- modal: tambah siswa --}}
    <div id="modal-tambah"
        class="fixed inset-0 z-50 {{ $errors->any() ? 'flex' : 'hidden' }} items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h3 class="font-semibold text-gray-800">Tambah Siswa</h3>
                <button type="button" data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form class="px-6 py-4" method="POST" action="{{ route('cms.students.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="name">Nama Lengkap</label>
                    <input id="name" name="name" type="text" required placeholder="Nama siswa" value="{{ old('name') }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700" for="nis">NIS</label>
                        <input id="nis" name="nis" type="text" required placeholder="8 digit NIS" value="{{ old('nis') }}"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @error('nis')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700" for="class">Kelas</label>
                        <select id="class" name="class"
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="X-A" @selected(old('class') === 'X-A')>X-A</option>
                            <option value="X-B" @selected(old('class') === 'X-B')>X-B</option>
                            <option value="XI-A" @selected(old('class') === 'XI-A')>XI-A</option>
                            <option value="XI-B" @selected(old('class') === 'XI-B')>XI-B</option>
                            <option value="XII-A" @selected(old('class') === 'XII-A')>XII-A</option>
                        </select>
                        @error('class')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="gender">Jenis Kelamin</label>
                    <select id="gender" name="gender"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="L" @selected(old('gender') === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('gender') === 'P')>Perempuan</option>
                    </select>
                    @error('gender')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="parent">Wali / Orang Tua</label>
                    <input id="parent" name="parent" type="text" placeholder="Nama wali atau orang tua"
                        value="{{ old('parent') }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('parent')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="phone">No. Telepon Wali</label>
                    <input id="phone" name="phone" type="text" placeholder="08xx-xxxx-xxxx" value="{{ old('phone') }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
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
                <h3 class="font-semibold text-gray-800">Hapus Siswa?</h3>
                <p class="mt-1 text-sm text-gray-500">Data siswa yang dihapus tidak dapat dikembalikan.</p>
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
