@extends('layouts.app')

@section('konten')
    <div class="mx-auto max-w-3xl">
        <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('cms.users') }}"
                class="inline-flex items-center gap-1.5 transition-colors hover:text-indigo-600">
                <i class="fas fa-arrow-left text-xs"></i>
                Manajemen Pengguna
            </a>
            <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
            <span class="font-medium text-gray-700">Edit Pengguna</span>
        </nav>

        <div class="mb-6 flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                <i class="fas fa-user-edit text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Pengguna</h1>
                <p class="mt-1 text-sm text-gray-600">Perbarui informasi akun pengguna yang dipilih.</p>
            </div>
        </div>

        <div class="mb-6 flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-base font-semibold text-indigo-600">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-gray-800">{{ $user->name }}</p>
                <p class="truncate text-xs text-gray-500">{{ $user->email }}</p>
            </div>
            @if ($user->status === 'Aktif')
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-600">
                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                    Aktif
                </span>
            @else
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500">
                    <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                    Nonaktif
                </span>
            @endif
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
                        <p class="font-semibold">Periksa kembali data yang diubah.</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('cms.users.update', ['user' => $user->id]) }}"
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @csrf
            @method('PUT')

            <div class="border-b border-gray-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-gray-800">Informasi Akun</h2>
                <p class="mt-1 text-sm text-gray-500">Kolom yang ditandai <span class="text-red-500">*</span> wajib diisi.</p>
            </div>

            <div class="grid gap-5 p-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="mb-2 block text-sm font-semibold text-gray-700">
                        Nama Lengkap <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255"
                        autocomplete="name" placeholder="Contoh: Ahmad Fauzi"
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('name'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('name'),
                        ])>
                    @error('name')
                        <p id="name-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="email" class="mb-2 block text-sm font-semibold text-gray-700">
                        Email <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255"
                        autocomplete="email" placeholder="nama@sekolah.sch.id"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('email'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('email'),
                        ])>
                    @error('email')
                        <p id="email-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="role" class="mb-2 block text-sm font-semibold text-gray-700">
                        Peran <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <select id="role" name="role" required
                        aria-invalid="{{ $errors->has('role') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('role'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('role'),
                        ])>
                        <option value="Admin" @selected(old('role', $user->role) === 'Admin')>Admin</option>
                        <option value="Guru" @selected(old('role', $user->role) === 'Guru')>Guru</option>
                        <option value="Siswa" @selected(old('role', $user->role) === 'Siswa')>Siswa</option>
                        <option value="Operator" @selected(old('role', $user->role) === 'Operator')>Operator</option>
                    </select>
                    @error('role')
                        <p id="role-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="mb-2 block text-sm font-semibold text-gray-700">Status</label>
                    <select id="status" name="status"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('status'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('status'),
                        ])>
                        <option value="Aktif" @selected(old('status', $user->status) === 'Aktif')>Aktif</option>
                        <option value="Nonaktif" @selected(old('status', $user->status) === 'Nonaktif')>Nonaktif</option>
                    </select>
                    @error('status')
                        <p id="status-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="password" class="mb-2 block text-sm font-semibold text-gray-700">Password Baru</label>
                    <input id="password" name="password" type="password" minlength="8"
                        autocomplete="new-password" placeholder="Kosongkan jika tidak ingin mengubah password"
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('password'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('password'),
                        ])>
                    <p class="mt-1.5 text-xs text-gray-500">Password hanya diubah jika Anda mengisi kolom ini. Minimal 8 karakter.</p>
                    @error('password')
                        <p id="password-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-gray-700">
                        Ulangi Password Baru
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password" minlength="8"
                        autocomplete="new-password" placeholder="Kosongkan juga jika password tidak diubah"
                        aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('password_confirmation'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('password_confirmation'),
                        ])>
                    @error('password_confirmation')
                        <p id="password_confirmation-error" class="mt-1.5 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end">
                <a href="{{ route('cms.users') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <i class="fas fa-check"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>

        <div class="mt-5 flex gap-3 rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
            <i class="fas fa-info-circle mt-0.5 shrink-0"></i>
            <p>Perubahan nama, email, peran, dan status akan langsung tersimpan pada akun pengguna.</p>
        </div>
    </div>
@endsection
