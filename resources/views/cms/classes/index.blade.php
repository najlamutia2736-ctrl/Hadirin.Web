@extends('layouts.app')

@section('konten')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Kelas</h2>
            <p class="mt-1 text-gray-600">Kelola daftar kelas, wali kelas, dan rombongan belajar.</p>
        </div>
        <a href="{{ route('cms.classes.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <i class="fas fa-plus"></i>
            Tambah Kelas
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
                    <p class="font-semibold">Periksa kembali data kelas.</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <form method="GET" action="{{ route('cms.classes') }}"
            class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:max-w-xs">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari kelas atau wali kelas..."
                    class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <select name="level"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Tingkat</option>
                    <option value="X" @selected(request('level') === 'X')>X</option>
                    <option value="XI" @selected(request('level') === 'XI')>XI</option>
                    <option value="XII" @selected(request('level') === 'XII')>XII</option>
                </select>
                <select name="jurusan"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Jurusan</option>
                    @foreach ($jurusan as $item)
                        <option value="{{ $item->kode_jurusan }}" @selected(request('jurusan') === $item->kode_jurusan)>
                            {{ $item->nama_jurusan }}
                        </option>
                    @endforeach
                </select>
                <select name="status"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="Aktif" @selected(request('status') === 'Aktif')>Aktif</option>
                    <option value="Arsip" @selected(request('status') === 'Arsip')>Arsip</option>
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
                        <th class="px-6 py-3">Kelas</th>
                        <th class="px-6 py-3">Tingkat</th>
                        <th class="px-6 py-3">Jurusan</th>
                        <th class="px-6 py-3">Wali Kelas</th>
                        <th class="px-6 py-3">Guru Pengampu</th>
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
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-purple-100 text-purple-600">
                                        <i class="fas fa-chalkboard"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-800">{{ $class->nama_kelas }}</p>
                                        <p class="text-xs text-gray-500">Tahun {{ $class->tahun_ajaran }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-block rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-600">
                                    Tingkat {{ $class->tingkat }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($class->jurusan)
                                    <span
                                        class="inline-block rounded-full bg-purple-50 px-2.5 py-1 text-xs font-medium text-purple-600">
                                        {{ $class->jurusan->nama_jurusan }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Belum ditentukan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $class->waliKelas?->user?->name ?? 'Belum ditentukan' }}
                            </td>
                            <td class="px-6 py-4">
                                @forelse ($class->guru as $pengampu)
                                    <span
                                        class="mb-1 mr-1 inline-block rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-600">
                                        {{ $pengampu->user?->name ?? 'Guru tanpa akun' }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400">Belum ada pengampu</span>
                                @endforelse
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-700">{{ $class->siswa_count }}</span>
                                    <progress value="{{ min($class->siswa_count, 40) }}" max="40"
                                        class="h-1.5 w-20 overflow-hidden rounded-full bg-gray-100"></progress>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $class->ruang ?: '-' }}</td>
                            <td class="px-6 py-4">
                                @if ($class->status === 'Aktif')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-600">
                                        <span class="h-2 w-2 rounded-full bg-green-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-400">
                                        <span class="h-2 w-2 rounded-full bg-gray-300"></span> {{ $class->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('cms.classes.edit', ['kelas' => $class->id]) }}" title="Ubah"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 transition-colors hover:bg-blue-50">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <button type="button" title="Hapus" data-delete-class
                                        data-delete-url="{{ route('cms.classes.destroy', ['kelas' => $class->id]) }}"
                                        data-class-name="{{ $class->nama_kelas }}" data-modal-open="modal-hapus-kelas"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition-colors hover:bg-red-50">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-gray-500">
                                Belum ada data kelas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-gray-500">
                Menampilkan {{ $classes->count() }} dari <span
                    class="font-medium text-gray-700">{{ $classes->total() }}</span> kelas
            </p>
            {{ $classes->links() }}
        </div>
    </div>

    <div id="modal-hapus-kelas" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-sm rounded-xl bg-white shadow-xl">
            <div class="px-6 py-5 text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600">
                    <i class="fas fa-trash"></i>
                </div>
                <h3 class="font-semibold text-gray-800">Hapus Kelas?</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Kelas <span id="delete-class-name" class="font-medium text-gray-700"></span> akan dihapus permanen.
                </p>
            </div>
            <form id="delete-class-form" method="POST" action="#"
                class="flex gap-3 border-t border-gray-200 px-6 py-4">
                @csrf
                @method('DELETE')
                <button type="button" data-modal-close
                    class="flex-1 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit"
                    class="flex-1 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Hapus</button>
            </form>
        </div>
    </div>

    <script>
        var deleteClassModal = document.getElementById('modal-hapus-kelas');
        var deleteClassForm = document.getElementById('delete-class-form');
        var deleteClassName = document.getElementById('delete-class-name');

        document.querySelectorAll('[data-delete-class]').forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                deleteClassForm.action = trigger.dataset.deleteUrl;
                deleteClassName.textContent = trigger.dataset.className;
                deleteClassModal.classList.remove('hidden');
                deleteClassModal.classList.add('flex');
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach(function(button) {
            button.addEventListener('click', function() {
                deleteClassModal.classList.add('hidden');
                deleteClassModal.classList.remove('flex');
            });
        });

        deleteClassModal.addEventListener('click', function(event) {
            if (event.target === deleteClassModal) {
                deleteClassModal.classList.add('hidden');
                deleteClassModal.classList.remove('flex');
            }
        });
    </script>
@endsection
