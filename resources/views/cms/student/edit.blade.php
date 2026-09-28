@extends('layouts.app')

@section('konten')
    <div class="mx-auto max-w-3xl">
        <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('cms.student') }}"
                class="inline-flex items-center gap-1.5 transition-colors hover:text-indigo-600">
                <i class="fas fa-arrow-left text-xs"></i>
                Manajemen Siswa
            </a>
            <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
            <span class="font-medium text-gray-700">Edit Siswa</span>
        </nav>

        <div class="mb-6 flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                <i class="fas fa-pen text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Siswa</h1>
                <p class="mt-1 text-sm text-gray-600">Perbarui informasi siswa yang dipilih.</p>
            </div>
        </div>

        @if ($errors->any())
            <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <div class="flex gap-3">
                    <i class="fas fa-exclamation-circle mt-0.5"></i>
                    <div>
                        <p class="font-semibold">Periksa kembali data siswa.</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('cms.student.update', $siswa) }}"
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @csrf
            @method('PUT')

            <div class="border-b border-gray-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-gray-800">Informasi Siswa</h2>
                <p class="mt-1 text-sm text-gray-500">Kolom yang ditandai <span class="text-red-500">*</span> wajib diisi.</p>
            </div>

            <div class="grid gap-5 p-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="mb-2 block text-sm font-semibold text-gray-700">
                        Nama Lengkap <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input id="name" name="name" type="text" value="{{ old('name', $siswa->user?->name) }}" required
                        maxlength="255" placeholder="Contoh: Andi Pratama"
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
                    <label for="nis" class="mb-2 block text-sm font-semibold text-gray-700">
                        NIS <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input id="nis" name="nis" type="text" value="{{ old('nis', $siswa->nisn) }}" required
                        inputmode="numeric" maxlength="8" pattern="[0-9]{8}" placeholder="Contoh: 12345678"
                        aria-invalid="{{ $errors->has('nis') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('nis'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('nis'),
                        ])>
                    @error('nis')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="class" class="mb-2 block text-sm font-semibold text-gray-700">
                        Kelas <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <select id="class" name="class" required
                        aria-invalid="{{ $errors->has('class') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('class'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('class'),
                        ])>
                        <option value="">Pilih Kelas</option>
                        <option value="X-A" @selected(old('class', $siswa->kelas) === 'X-A')>X-A</option>
                        <option value="X-B" @selected(old('class', $siswa->kelas) === 'X-B')>X-B</option>
                        <option value="XI-A" @selected(old('class', $siswa->kelas) === 'XI-A')>XI-A</option>
                        <option value="XI-B" @selected(old('class', $siswa->kelas) === 'XI-B')>XI-B</option>
                        <option value="XII-A" @selected(old('class', $siswa->kelas) === 'XII-A')>XII-A</option>
                    </select>
                    @error('class')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="gender" class="mb-2 block text-sm font-semibold text-gray-700">
                        Jenis Kelamin <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <select id="gender" name="gender" required
                        aria-invalid="{{ $errors->has('gender') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('gender'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('gender'),
                        ])>
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="L" @selected(old('gender', $siswa->jenis_kelamin) === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('gender', $siswa->jenis_kelamin) === 'P')>Perempuan</option>
                    </select>
                    @error('gender')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
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
                        <option value="Aktif" @selected(old('status', $siswa->status) === 'Aktif')>Aktif</option>
                        <option value="Nonaktif" @selected(old('status', $siswa->status) === 'Nonaktif')>Nonaktif</option>
                        <option value="Pindah" @selected(old('status', $siswa->status) === 'Pindah')>Pindah</option>
                    </select>
                    @error('status')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="parent" class="mb-2 block text-sm font-semibold text-gray-700">Wali / Orang Tua</label>
                    <input id="parent" name="parent" type="text" value="{{ old('parent', $siswa->wali) }}" maxlength="255"
                        placeholder="Contoh: Budi Santoso"
                        aria-invalid="{{ $errors->has('parent') ? 'true' : 'false' }}"
                        @class([
                            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2',
                            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('parent'),
                            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('parent'),
                        ])>
                    @error('parent')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="phone" class="mb-2 block text-sm font-semibold text-gray-700">Nomor Telepon Wali</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $siswa->telepon_wali) }}"
                        maxlength="20" placeholder="Contoh: 081234567890"
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
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end">
                <a href="{{ route('cms.student') }}"
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

        <div class="mt-5 flex gap-3 rounded-xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-800">
            <i class="fas fa-info-circle mt-0.5 shrink-0"></i>
            <p>Email akun siswa akan diperbarui otomatis mengikuti NIS. Status akun mengikuti status kelangsungan siswa.</p>
        </div>
    </div>
@endsection
