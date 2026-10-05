@extends('layouts.app')

@section('konten')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Jurusan</h2>
            <p class="mt-1 text-gray-600">Kelola daftar jurusan yang dipakai kelas di sekolah ini.</p>
        </div>
        <a href="{{ route('cms.jurusan.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <i class="fas fa-plus"></i>
            Tambah Jurusan
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
                    <p class="font-semibold">Periksa kembali data jurusan.</p>
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
        <form method="GET" action="{{ route('cms.jurusan') }}"
            class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:max-w-xs">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau kode jurusan..."
                    class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <button type="submit"
                class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                Terapkan
            </button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Jurusan</th>
                        <th class="px-6 py-3">Kode</th>
                        <th class="px-6 py-3">Jumlah Kelas</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($jurusan as $item)
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                                        <i class="fas fa-layer-group text-xs"></i>
                                    </div>
                                    <p class="ml-3 font-medium text-gray-800">{{ $item->nama_jurusan }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-block rounded-full bg-gray-100 px-2.5 py-1 font-mono text-xs font-medium text-gray-600">
                                    {{ $item->kode_jurusan }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($item->kelas_count > 0)
                                    <span class="inline-flex items-center gap-2 text-gray-700">
                                        <span class="font-medium">{{ $item->kelas_count }}</span>
                                        <span class="text-xs text-gray-500">kelas</span>
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Belum ada kelas</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('cms.jurusan.edit', ['jurusan' => $item->id]) }}" title="Ubah"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 transition-colors hover:bg-blue-50">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <button type="button" title="Hapus" data-delete-jurusan
                                        data-delete-url="{{ route('cms.jurusan.destroy', ['jurusan' => $item->id]) }}"
                                        data-jurusan-name="{{ $item->nama_jurusan }}"
                                        data-kelas-count="{{ $item->kelas_count }}" data-modal-open="modal-hapus-jurusan"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition-colors hover:bg-red-50">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                Belum ada data jurusan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-gray-500">
                Menampilkan {{ $jurusan->count() }} dari <span
                    class="font-medium text-gray-700">{{ $jurusan->total() }}</span> jurusan
            </p>
            {{ $jurusan->links() }}
        </div>
    </div>

    <div class="mt-5 flex gap-3 rounded-xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-800">
        <i class="fas fa-info-circle mt-0.5 shrink-0"></i>
        <p>
            Jurusan dipakai saat menambah atau mengubah kelas. Menghapus jurusan tidak menghapus kelas yang
            memakainya, hanya membuat kolom jurusannya kosong.
        </p>
    </div>

    <div id="modal-hapus-jurusan" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-sm rounded-xl bg-white shadow-xl">
            <div class="px-6 py-5 text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600">
                    <i class="fas fa-trash"></i>
                </div>
                <h3 class="font-semibold text-gray-800">Hapus Jurusan?</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Jurusan <span id="delete-jurusan-name" class="font-medium text-gray-700"></span> akan dihapus
                    permanen.
                </p>
                <p id="delete-jurusan-peringatan"
                    class="mt-2 hidden rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800"></p>
            </div>
            <form id="delete-jurusan-form" method="POST" action="#"
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
        var deleteJurusanModal = document.getElementById('modal-hapus-jurusan');
        var deleteJurusanForm = document.getElementById('delete-jurusan-form');
        var deleteJurusanName = document.getElementById('delete-jurusan-name');
        var deleteJurusanWarning = document.getElementById('delete-jurusan-peringatan');

        document.querySelectorAll('[data-delete-jurusan]').forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                var jumlahKelas = parseInt(trigger.dataset.kelasCount, 10) || 0;

                deleteJurusanForm.action = trigger.dataset.deleteUrl;
                deleteJurusanName.textContent = trigger.dataset.jurusanName;

                // Kelas yang memakai jurusan ini tidak ikut terhapus, tapi kolom
                // jurusannya jadi kosong, jadi admin diberi tahu lebih dulu.
                if (jumlahKelas > 0) {
                    deleteJurusanWarning.textContent = jumlahKelas + ' kelas memakai jurusan ini dan akan kehilangan jurusannya.';
                    deleteJurusanWarning.classList.remove('hidden');
                } else {
                    deleteJurusanWarning.classList.add('hidden');
                }

                deleteJurusanModal.classList.remove('hidden');
                deleteJurusanModal.classList.add('flex');
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach(function(button) {
            button.addEventListener('click', function() {
                deleteJurusanModal.classList.add('hidden');
                deleteJurusanModal.classList.remove('flex');
            });
        });

        deleteJurusanModal.addEventListener('click', function(event) {
            if (event.target === deleteJurusanModal) {
                deleteJurusanModal.classList.add('hidden');
                deleteJurusanModal.classList.remove('flex');
            }
        });
    </script>
@endsection