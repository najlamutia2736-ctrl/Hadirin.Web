@extends('layouts.app')

@section('konten')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Guru</h2>
            <p class="mt-1 text-gray-600">Kelola data guru, mata pelajaran, dan status mengajar.</p>
        </div>
        <a href="{{ route('cms.teachers.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
            <i class="fas fa-user-plus"></i>
            Tambah Guru
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
                    <p class="font-semibold">Periksa kembali data guru.</p>
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
        <form method="GET" action="{{ route('cms.teachers') }}"
            class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:max-w-xs">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, NIP, atau mapel..."
                    class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <select name="subject"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Mapel</option>
                    <option value="Matematika" @selected(request('subject') === 'Matematika')>Matematika</option>
                    <option value="Bahasa Indonesia" @selected(request('subject') === 'Bahasa Indonesia')>Bahasa Indonesia</option>
                    <option value="Bahasa Inggris" @selected(request('subject') === 'Bahasa Inggris')>Bahasa Inggris</option>
                    <option value="Informatika" @selected(request('subject') === 'Informatika')>Informatika</option>
                    <option value="IPA" @selected(request('subject') === 'IPA')>IPA</option>
                    <option value="Sejarah" @selected(request('subject') === 'Sejarah')>Sejarah</option>
                </select>
                <select name="status"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="Aktif" @selected(request('status') === 'Aktif')>Aktif</option>
                    <option value="Cuti" @selected(request('status') === 'Cuti')>Cuti</option>
                    <option value="Nonaktif" @selected(request('status') === 'Nonaktif')>Nonaktif</option>
                </select>
                <button type="submit"
                    class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Terapkan</button>
            </div>
        </form>

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
                                        {{ strtoupper(substr($teacher->user?->name ?? 'G', 0, 1)) }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-800">{{ $teacher->user?->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $teacher->telepon ?: 'Telepon belum diisi' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $teacher->nip }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-block rounded-full bg-purple-50 px-2.5 py-1 text-xs font-medium text-purple-600">
                                    {{ $teacher->mata_pelajaran }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $teacher->kelas->pluck('nama_kelas')->join(', ') ?: 'Belum ada kelas' }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($teacher->status === 'Aktif')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-600">
                                        <span class="h-2 w-2 rounded-full bg-green-500"></span> Aktif
                                    </span>
                                @elseif ($teacher->status === 'Cuti')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-600">
                                        <span class="h-2 w-2 rounded-full bg-amber-500"></span> Cuti
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-400">
                                        <span class="h-2 w-2 rounded-full bg-gray-300"></span> {{ $teacher->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('cms.teachers.edit', ['guru' => $teacher->id]) }}" title="Ubah"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 transition-colors hover:bg-blue-50">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <button type="button" title="Hapus" data-delete-guru
                                        data-delete-url="{{ route('cms.teachers.destroy', ['guru' => $teacher->id]) }}"
                                        data-teacher-name="{{ $teacher->user?->name }}" data-modal-open="modal-hapus-guru"
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

        <div class="flex flex-col gap-3 border-t border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-gray-500">
                Menampilkan {{ $teachers->count() }} dari <span
                    class="font-medium text-gray-700">{{ $teachers->total() }}</span> guru
            </p>
            {{ $teachers->links() }}
        </div>
    </div>

    <div id="modal-tambah"
        class="fixed inset-0 z-50 {{ $errors->any() ? 'flex' : 'hidden' }} items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h3 class="font-semibold text-gray-800">Tambah Guru</h3>
                <button type="button" data-modal-close class="text-gray-400 hover:text-gray-600" aria-label="Tutup modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form class="px-6 py-4" method="POST" action="{{ route('cms.teachers.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="name">Nama Lengkap &amp;
                        Gelar</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                        placeholder="Contoh: Siti Nurhaliza, S.Pd." @class([
                            'w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-1',
                            'border-red-500 focus:border-red-500 focus:ring-red-500' => $errors->has(
                                'name'),
                            'border-gray-200 focus:border-indigo-500 focus:ring-indigo-500' => !$errors->has(
                                'name'),
                        ])>
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="nip">NIP</label>
                    <input id="nip" name="nip" type="text" value="{{ old('nip') }}" required
                        maxlength="30" placeholder="18 digit NIP" @class([
                            'w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-1',
                            'border-red-500 focus:border-red-500 focus:ring-red-500' => $errors->has(
                                'nip'),
                            'border-gray-200 focus:border-indigo-500 focus:ring-indigo-500' => !$errors->has(
                                'nip'),
                        ])>
                    @error('nip')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="subject">Mata Pelajaran</label>
                    <input id="subject" name="subject" type="text" value="{{ old('subject') }}" required
                        maxlength="100" placeholder="Contoh: Matematika" @class([
                            'w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-1',
                            'border-red-500 focus:border-red-500 focus:ring-red-500' => $errors->has(
                                'subject'),
                            'border-gray-200 focus:border-indigo-500 focus:ring-indigo-500' => !$errors->has(
                                'subject'),
                        ])>
                    @error('subject')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="phone">No. Telepon</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone') }}" maxlength="20"
                        placeholder="08xx-xxxx-xxxx" @class([
                            'w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-1',
                            'border-red-500 focus:border-red-500 focus:ring-red-500' => $errors->has(
                                'phone'),
                            'border-gray-200 focus:border-indigo-500 focus:ring-indigo-500' => !$errors->has(
                                'phone'),
                        ])>
                    @error('phone')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="status">Status</label>
                    <select id="status" name="status" required @class([
                        'w-full rounded-lg border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-1',
                        'border-red-500 focus:border-red-500 focus:ring-red-500' => $errors->has(
                            'status'),
                        'border-gray-200 focus:border-indigo-500 focus:ring-indigo-500' => !$errors->has(
                            'status'),
                    ])>
                        <option value="Aktif" @selected(old('status', 'Aktif') === 'Aktif')>Aktif</option>
                        <option value="Cuti" @selected(old('status') === 'Cuti')>Cuti</option>
                        <option value="Nonaktif" @selected(old('status') === 'Nonaktif')>Nonaktif</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <p class="mb-4 text-xs text-gray-500">Akun guru dibuat otomatis. Email dan password awal dibuat dari NIP
                    tanpa spasi.</p>
                <div class="flex justify-end gap-3">
                    <button type="button" data-modal-close
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        <i class="fas fa-check"></i>
                        Simpan Guru
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-hapus-guru"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-sm rounded-xl bg-white shadow-xl">
            <div class="px-6 py-5 text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600">
                    <i class="fas fa-trash"></i>
                </div>
                <h3 class="font-semibold text-gray-800">Hapus Guru?</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Data <span id="delete-teacher-name" class="font-medium text-gray-700"></span> akan dihapus permanen.
                </p>
            </div>
            <form id="delete-teacher-form" method="POST" action="#" class="flex gap-3 border-t border-gray-200 px-6 py-4">
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
        var deleteTeacherForm = document.getElementById('delete-teacher-form');
        var deleteTeacherName = document.getElementById('delete-teacher-name');

        document.querySelectorAll('[data-delete-guru]').forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                deleteTeacherForm.action = trigger.dataset.deleteUrl;
                deleteTeacherName.textContent = trigger.dataset.teacherName;
            });
        });

        document.querySelectorAll('[data-modal-open]').forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                var modal = document.getElementById(trigger.dataset.modalOpen);
                if (!modal) {
                    return;
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach(function(button) {
            button.addEventListener('click', function() {
                var modal = button.closest('.fixed');
                if (!modal) {
                    return;
                }

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
