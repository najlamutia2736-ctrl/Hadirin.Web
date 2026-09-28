@extends('layouts.app')

@section('konten')
    {{-- data contoh, nanti diganti dari controller: return view('cms.users', ['users' => $users]) --}}
    @php
        $users = $users ?? [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@sekolah.sch.id',
                'role' => 'Admin',
                'status' => 'Aktif',
                'last_login' => '22 Sep 2026, 07:45',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@sekolah.sch.id',
                'role' => 'Guru',
                'status' => 'Aktif',
                'last_login' => '22 Sep 2026, 07:12',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@sekolah.sch.id',
                'role' => 'Guru',
                'status' => 'Aktif',
                'last_login' => '21 Sep 2026, 15:30',
            ],
            [
                'name' => 'Rina Wijaya',
                'email' => 'rina.wijaya@sekolah.sch.id',
                'role' => 'Siswa',
                'status' => 'Aktif',
                'last_login' => '22 Sep 2026, 06:58',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@sekolah.sch.id',
                'role' => 'Siswa',
                'status' => 'Nonaktif',
                'last_login' => '10 Sep 2026, 09:20',
            ],
            [
                'name' => 'Andi Pratama',
                'email' => 'andi.pratama@sekolah.sch.id',
                'role' => 'Operator',
                'status' => 'Aktif',
                'last_login' => '21 Sep 2026, 16:05',
            ],
            [
                'name' => 'Maya Sari',
                'email' => 'maya.sari@sekolah.sch.id',
                'role' => 'Guru',
                'status' => 'Nonaktif',
                'last_login' => '02 Sep 2026, 10:44',
            ],
            [
                'name' => 'Rizky Ramadhan',
                'email' => 'rizky.ramadhan@sekolah.sch.id',
                'role' => 'Siswa',
                'status' => 'Aktif',
                'last_login' => '22 Sep 2026, 07:01',
            ],
        ];

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
        <a href="/tambahuser" data-modal-open="modal-tambah"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
            <i class="fas fa-user-plus"></i>
            Tambah Pengguna
        </a>
    </div>

    {{-- tabel pengguna --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        {{-- toolbar: pencarian & filter --}}
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:max-w-xs">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input type="search" placeholder="Cari nama atau email..."
                    class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <select
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Peran</option>
                    <option>Admin</option>
                    <option>Guru</option>
                    <option>Siswa</option>
                    <option>Operator</option>
                </select>
                <select
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option>Aktif</option>
                    <option>Nonaktif</option>
                </select>
            </div>
        </div>

        {{-- tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Pengguna</th>
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
                            {{-- <td class="px-6 py-4 text-gray-500">{{ $user['last_login'] }}</td> --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @if (data_get($user, 'id'))
                                        <a href="{{ route('cms.users.edit', ['user' => data_get($user, 'id')]) }}"
                                            title="Ubah" aria-label="Edit pengguna {{ data_get($user, 'name') }}"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 transition-colors hover:bg-blue-50">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                    @else
                                        <button type="button" title="Ubah" disabled
                                            class="flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg text-blue-600 opacity-50">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                    @endif
                                    @if (data_get($user, 'id'))
                                        <button type="button" title="Hapus" data-delete-user
                                            data-delete-url="{{ route('cms.users.destroy', ['user' => data_get($user, 'id')]) }}"
                                            data-user-name="{{ data_get($user, 'name') }}" data-modal-open="modal-hapus"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition-colors hover:bg-red-50">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    @else
                                        <button type="button" title="Hapus" disabled
                                            class="flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg text-red-600 opacity-50">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    @endif
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
                Menampilkan {{ count($users) }} dari <span class="font-medium text-gray-700">154</span> pengguna
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

    {{-- modal: tambah pengguna --}
    <div id="modal-tambah" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h3 class="font-semibold text-gray-800">Tambah Pengguna</h3>
                <button type="button" data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form class="px-6 py-4" method="POST" action="#">
                @csrf
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="name">Nama Lengkap</label>
                    <input id="name" name="name" type="text" required placeholder="Nama pengguna"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="email">Email</label>
                    <input id="email" name="email" type="email" required placeholder="nama@sekolah.sch.id"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="role">Peran</label>
                    <select id="role" name="role"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option>Admin</option>
                        <option>Guru</option>
                        <option>Siswa</option>
                        <option>Operator</option>
                    </select>
                </div>
                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="password">Password</label>
                    <input id="password" name="password" type="password" required placeholder="Minimal 8 karakter"
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
