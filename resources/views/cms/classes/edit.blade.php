@extends('layouts.app')

@section('konten')
    <div class="mx-auto max-w-3xl">
        <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('cms.classes') }}"
                class="inline-flex items-center gap-1.5 transition-colors hover:text-indigo-600">
                <i class="fas fa-arrow-left text-xs"></i>
                Manajemen Kelas
            </a>
            <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
            <span class="font-medium text-gray-700">Edit Kelas</span>
        </nav>

        <div class="mb-6 flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                <i class="fas fa-pen text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Kelas</h1>
                <p class="mt-1 text-sm text-gray-600">Perbarui informasi kelas yang dipilih.</p>
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

        <form method="POST" action="{{ route('cms.classes.update', ['kelas' => $kelas->id]) }}"
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @csrf
            @method('PUT')

            <div class="border-b border-gray-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-gray-800">Informasi Kelas</h2>
                <p class="mt-1 text-sm text-gray-500">Kolom yang ditandai <span class="text-red-500">*</span> wajib diisi.</p>
            </div>

            <div class="grid gap-5 p-6 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-gray-700">
                        Nama Kelas <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input id="name" name="name" type="text" value="{{ old('name', $kelas->nama_kelas) }}" required maxlength="50"
                        placeholder="Contoh: X-D"
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
                    <label for="level" class="mb-2 block text-sm font-semibold text-gray-700">
                        Tingkat <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <select id="level" name="level" required
                        aria-invalid="{{ $errors->has('level') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('level'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('level'),
                        ])>
                        <option value="X" @selected(old('level', $kelas->tingkat) === 'X')>X</option>
                        <option value="XI" @selected(old('level', $kelas->tingkat) === 'XI')>XI</option>
                        <option value="XII" @selected(old('level', $kelas->tingkat) === 'XII')>XII</option>
                    </select>
                    @error('level')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="homeroom" class="mb-2 block text-sm font-semibold text-gray-700">Wali Kelas</label>
                    <select id="homeroom" name="homeroom"
                        aria-invalid="{{ $errors->has('homeroom') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('homeroom'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('homeroom'),
                        ])>
                        <option value="">Belum ditentukan</option>
                        @foreach ($gurus as $guru)
                            <option value="{{ $guru->id }}" @selected((string) old('homeroom', $kelas->wali_kelas_id) === (string) $guru->id)>
                                {{ $guru->user?->name ?? 'Guru tanpa akun' }}
                            </option>
                        @endforeach
                    </select>
                    @error('homeroom')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="room" class="mb-2 block text-sm font-semibold text-gray-700">Ruang Kelas</label>
                    <input id="room" name="room" type="text" value="{{ old('room', $kelas->ruang) }}" maxlength="50"
                        placeholder="Contoh: R. 104"
                        aria-invalid="{{ $errors->has('room') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('room'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('room'),
                        ])>
                    @error('room')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tahun_ajaran" class="mb-2 block text-sm font-semibold text-gray-700">
                        Tahun Ajaran <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input id="tahun_ajaran" name="tahun_ajaran" type="number"
                        value="{{ old('tahun_ajaran', $kelas->tahun_ajaran) }}" min="2000" max="{{ now()->year + 1 }}" required
                        aria-invalid="{{ $errors->has('tahun_ajaran') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('tahun_ajaran'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('tahun_ajaran'),
                        ])>
                    @error('tahun_ajaran')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="mb-2 block text-sm font-semibold text-gray-700">Status</label>
                    <select id="status" name="status"
                        aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('status'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('status'),
                        ])>
                        <option value="Aktif" @selected(old('status', $kelas->status) === 'Aktif')>Aktif</option>
                        <option value="Arsip" @selected(old('status', $kelas->status) === 'Arsip')>Arsip</option>
                    </select>
                    @error('status')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end">
                <a href="{{ route('cms.classes') }}"
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
    </div>
@endsection
