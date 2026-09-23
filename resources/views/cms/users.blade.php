@extends('layouts.app')

@section('konten')
    @php
        $roleClasses = [
            'Admin' => 'bg-amber-50 text-amber-600',
            'Guru' => 'bg-blue-50 text-blue-600',
            'Siswa' => 'bg-green-50 text-green-600',
            'Operator' => 'bg-purple-50 text-purple-600',
        ];
    @endphp

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
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h2>
            <p class="mt-1 text-gray-600">Kelola akun, peran, dan status pengguna sekolah.</p>
        </div>
        <button type="button" data-modal-open="modal-tambah"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
            <i class="fas fa-user-plus"></i>
            Tambah Pengguna
        </button>
    </div>

    {{-- tabel pengguna --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        {{-- toolbar: pencarian & filter --}}
        <form method="GET" action="{{ route('cms.users') }}"
            class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:max-w-xs">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau email..."
                    class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <select name="role"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Peran</option>
                    @foreach (['Admin', 'Guru', 'Siswa', 'Operator'] as $roleOption)
                        <option value="{{ $roleOption }}" @selected(request('role') === $roleOption)>{{ $roleOption }}</option>
                    @endforeach
                </select>
                <select name="status"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    @foreach (['Aktif', 'Nonaktif'] as $statusOption)
                        <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>{{ $statusOption }}</option>
                    @endforeach
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
                        <th class="px-6 py-3">Pengguna</th>
                        <th class="px-6 py-3">Peran</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Terakhir Masuk</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-600">
                                        {{ strtoupper(substr($user['name'], 0, 1)) }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-800">{{ $user['name'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $user['email'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-block rounded-full px-2.5 py-1 text-xs font-medium {{ $roleClasses[$user['role']] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $user['role'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($user['status'] === 'Aktif')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-600">
                                        <span class="h-2 w-2 rounded-full bg-green-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-400">
                                        <span class="h-2 w-2 rounded-full bg-gray-300"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $user->last_login_at?->format('d M Y, H:i') ?? 'Belum pernah masuk' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" title="Ubah"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 transition-colors hover:bg-blue-50">
                                        <i class="fas fa-pen text-xs"></i>
                                    </button>
                                    <button type="button" title="Nonaktifkan"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-amber-600 transition-colors hover:bg-amber-50">
                                        <i class="fas fa-ban text-xs"></i>
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
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                Belum ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- footer tabel (pagination) --}}
        <div class="flex flex-col gap-3 border-t border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-gray-500">
                Menampilkan {{ $users->count() }} dari <span class="font-medium text-gray-700">{{ $users->total() }}</span> pengguna
            </p>
            {{ $users->links() }}
        </div>
    </div>

    {{-- modal: tambah pengguna --}}
    <div id="modal-tambah"
        class="fixed inset-0 z-50 {{ $errors->any() ? 'flex' : 'hidden' }} items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h3 class="font-semibold text-gray-800">Tambah Pengguna</h3>
                <button type="button" data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form class="px-6 py-4" method="POST" action="{{ route('cms.users.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="name">Nama Lengkap</label>
                    <input id="name" name="name" type="text" required placeholder="Nama pengguna"
                        value="{{ old('name') }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('name')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="email">Email</label>
                    <input id="email" name="email" type="email" required placeholder="nama@sekolah.sch.id"
                        value="{{ old('email') }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('email')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="role">Peran</label>
                    <select id="role" name="role"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">-- Pilih peran --</option>
                        <option value="Admin" @selected(old('role') === 'Admin')>Admin</option>
                        <option value="Guru" @selected(old('role') === 'Guru')>Guru</option>
                        <option value="Siswa" @selected(old('role') === 'Siswa')>Siswa</option>
                        <option value="Operator" @selected(old('role') === 'Operator')>Operator</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="password">Password</label>
                    <input id="password" name="password" type="password" required placeholder="Minimal 8 karakter"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('password')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
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
                <h3 class="font-semibold text-gray-800">Hapus Pengguna?</h3>
                <p class="mt-1 text-sm text-gray-500">Akun yang dihapus tidak dapat dikembalikan.</p>
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
