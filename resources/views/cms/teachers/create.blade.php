@extends('layouts.app')

@section('konten')
    <div class="mx-auto max-w-3xl">
        <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('cms.teachers') }}"
                class="inline-flex items-center gap-1.5 transition-colors hover:text-indigo-600">
                <i class="fas fa-arrow-left text-xs"></i>
                Manajemen Guru
            </a>
            <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
            <span class="font-medium text-gray-700">Tambah Guru</span>
        </nav>

        <div class="mb-6 flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                <i class="fas fa-chalkboard-teacher text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Tambah Guru</h1>
                <p class="mt-1 text-sm text-gray-600">Lengkapi informasi guru yang akan ditambahkan.</p>
            </div>
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

        <form method="POST" action="{{ route('cms.teachers.store') }}"
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @csrf

            <div class="border-b border-gray-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-gray-800">Informasi Guru</h2>
                <p class="mt-1 text-sm text-gray-500">Kolom yang ditandai <span class="text-red-500">*</span> wajib diisi.</p>
            </div>

            <div class="grid gap-5 p-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="mb-2 block text-sm font-semibold text-gray-700">
                        Nama Lengkap &amp; Gelar <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255"
                        placeholder="Contoh: Siti Nurhaliza, S.Pd."
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('name'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('name'),
                        ])>
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nip" class="mb-2 block text-sm font-semibold text-gray-700">
                        NIP <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input id="nip" name="nip" type="text" value="{{ old('nip') }}" required maxlength="30"
                        placeholder="Contoh: 198203122011012004"
                        aria-invalid="{{ $errors->has('nip') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('nip'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('nip'),
                        ])>
                    @error('nip')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="mb-2 block text-sm font-semibold text-gray-700">Nomor Telepon</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone') }}" maxlength="20"
                        placeholder="Contoh: 081234567890"
                        aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('phone'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('phone'),
                        ])>
                    @error('phone')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    @include('cms.teachers.partials.mata-pelajaran', ['guru' => null])
                </div>

                <div>
                    <label for="status" class="mb-2 block text-sm font-semibold text-gray-700">
                        Status <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <select id="status" name="status" required
                        aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('status'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('status'),
                        ])>
                        <option value="Aktif" @selected(old('status', 'Aktif') === 'Aktif')>Aktif</option>
                        <option value="Cuti" @selected(old('status') === 'Cuti')>Cuti</option>
                        <option value="Nonaktif" @selected(old('status') === 'Nonaktif')>Nonaktif</option>
                    </select>
                    @error('status')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end">
                <a href="{{ route('cms.teachers') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <i class="fas fa-check"></i>
                    Simpan Guru
                </button>
            </div>
        </form>

        <div class="mt-5 flex gap-3 rounded-xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-800">
            <i class="fas fa-info-circle mt-0.5 shrink-0"></i>
            <p>Akun guru dibuat otomatis. Email dan password awal dibuat dari NIP tanpa spasi.</p>
        </div>
    </div>
@endsection
