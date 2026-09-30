@extends('layouts.app')

@section('konten')
    {{-- $users, $filterPeran, $filterStatus, $daftarPeran, $daftarStatus dikirim oleh UserController --}}

    {{-- pesan sukses --}}
    @if (session('success'))
        <div role="status"
            class="mb-6 flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div role="alert"
            class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <div class="flex gap-3">
                <i class="fas fa-exclamation-circle mt-0.5"></i>
                <div>
                    <p class="font-semibold">Periksa kembali data pengguna.</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @php
        $roleClasses = [
            'Admin' => 'bg-amber-50 text-amber-600',
            'Guru' => 'bg-blue-50 text-blue-600',
            'Siswa' => 'bg-green-50 text-green-600',
            'Operator' => 'bg-purple-50 text-purple-600',
        ];
    @endphp

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
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <select name="role" aria-label="Filter peran"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Peran</option>
                    @foreach ($daftarPeran as $peran)
                        <option value="{{ $peran }}" @selected($filterPeran === $peran)>{{ $peran }}</option>
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
                    class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-50">
                    Terapkan
                </button>
                @if ($filterPeran !== null || $filterStatus !== null)
                    <a href="{{ route('cms.users', array_filter(['q' => request('q')])) }}"
                        class="rounded-lg px-2 py-2 text-sm font-medium text-gray-500 transition-colors hover:text-gray-700">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Pengguna</th>
                        <th class="px-6 py-3">Jenis Kelamin</th>
                        <th class="px-6 py-3">Peran</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-600">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-800">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $user->siswa?->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-block rounded-full px-2.5 py-1 text-xs font-medium {{ $roleClasses[$user->role] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($user->status === 'Aktif')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-600">
                                        <span class="h-2 w-2 rounded-full bg-green-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-400">
                                        <span class="h-2 w-2 rounded-full bg-gray-300"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('cms.users.edit', ['user' => $user->id]) }}" title="Ubah"
                                        aria-label="Edit pengguna {{ $user->name }}"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 transition-colors hover:bg-blue-50">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <button type="button" title="Hapus" data-delete-user
                                        data-delete-url="{{ route('cms.users.destroy', ['user' => $user->id]) }}"
                                        data-user-name="{{ $user->name }}" data-modal-open="modal-hapus"
                                        aria-label="Hapus pengguna {{ $user->name }}"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition-colors hover:bg-red-50">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                @if ($filterPeran !== null || $filterStatus !== null || request('q'))
                                    Tidak ada pengguna yang cocok dengan filter.
                                    <a href="{{ route('cms.users') }}"
                                        class="font-medium text-indigo-600 hover:underline">Reset filter</a>
                                @else
                                    Belum ada data pengguna.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- footer tabel (pagination) --}}
        <div class="flex flex-col gap-3 border-t border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs text-gray-500">
                    Menampilkan {{ $users->count() }} dari <span
                        class="font-medium text-gray-700">{{ $users->total() }}</span> pengguna
                </p>
                @if ($filterPeran !== null || $filterStatus !== null)
                    <p class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-gray-500">
                        <span>Filter aktif:</span>
                        @if ($filterPeran !== null)
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 font-medium text-indigo-700">
                                Peran {{ $filterPeran }}
                            </span>
                        @endif
                        @if ($filterStatus !== null)
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 font-medium text-green-700">
                                Status {{ $filterStatus }}
                            </span>
                        @endif
                    </p>
                @endif
            </div>
            {{ $users->links() }}
        </div>
    </div>

    {{-- modal: tambah pengguna --}
    <div id="modal-tambah" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
        role="dialog" aria-modal="true" aria-labelledby="tambah-user-title">
        <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h3 id="tambah-user-title" class="font-semibold text-gray-800">Tambah Pengguna</h3>
                <button type="button" data-modal-close aria-label="Tutup modal tambah"
                    class="text-gray-400 hover:text-gray-600">
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
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="email">Email</label>
                    <input id="email" name="email" type="email" required placeholder="nama@sekolah.sch.id"
                        value="{{ old('email') }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="role">Peran</label>
                    <select id="role" name="role"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @foreach ($daftarPeran as $peran)
                            <option value="{{ $peran }}" @selected(old('role') === $peran)>{{ $peran }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="password">Password</label>
                    <input id="password" name="password" type="password" required placeholder="Minimal 8 karakter"
                        value="{{ old('password') }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('password')
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
                <h3 class="font-semibold text-gray-800">Hapus Pengguna?</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Akun <span id="delete-user-name" class="font-medium text-gray-700"></span> akan dihapus permanen.
                </p>
            </div>
            <form id="delete-user-form" method="POST" action="#" class="flex gap-3 border-t border-gray-200 px-6 py-4">
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
        var deleteUserForm = document.getElementById('delete-user-form');
        var deleteUserName = document.getElementById('delete-user-name');

        document.querySelectorAll('[data-delete-user]').forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                deleteUserForm.action = trigger.dataset.deleteUrl;
                deleteUserName.textContent = trigger.dataset.userName;
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
