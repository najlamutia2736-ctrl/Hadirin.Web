@extends('layouts.app')

@section('title', 'Tambah Mahasiswa · Hadirin.Web')

@section('konten')
    <div class="mx-auto max-w-3xl">
        <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('dosen.kelola') }}"
                class="inline-flex items-center gap-1.5 transition-colors hover:text-indigo-600">
                <i class="fas fa-arrow-left text-xs"></i>
                Kelola Data Presensi
            </a>
            <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
            <span class="font-medium text-gray-700">Tambah Mahasiswa</span>
        </nav>

        <div class="mb-6 flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                <i class="fas fa-user-plus text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Tambah Mahasiswa</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Tambahkan mahasiswa baru ke salah satu kelas yang Anda ampu.
                </p>
            </div>
        </div>

        @if ($pilihanKelas === [])
            {{-- Tanpa kelas yang diampu memang belum ada tujuan yang sah untuk menambah mahasiswa. --}}
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                <div class="flex items-start gap-3">
                    <i class="fas fa-circle-info mt-0.5 shrink-0"></i>
                    <div>
                        <p class="font-semibold">Belum ada kelas yang Anda ampu.</p>
                        <p class="mt-0.5">
                            Hubungkan kelas dengan akun Anda lewat menu
                            <span class="font-semibold">Kelas</span> di dashboard admin, kolom
                            <span class="font-semibold">Dosen Pengampu</span>. Setelah itu Anda bisa
                            menambahkan mahasiswa ke kelas tersebut.
                        </p>
                        <a href="{{ route('dosen.kelola') }}"
                            class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3.5 py-2 text-xs font-semibold text-white transition-colors hover:bg-amber-700">
                            <i class="fas fa-arrow-left text-[10px]"></i>
                            Kembali ke Kelola Data Presensi
                        </a>
                    </div>
                </div>
            </div>
        @else
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
                            <p class="font-semibold">Periksa kembali data mahasiswa.</p>
                            <ul class="mt-2 list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('dosen.kelola.mahasiswa.store') }}"
                class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                @csrf

                <div class="border-b border-gray-200 px-6 py-5">
                    <h2 class="text-lg font-semibold text-gray-800">Informasi Mahasiswa</h2>
                    <p class="mt-1 text-sm text-gray-500">Kolom yang ditandai <span class="text-red-500">*</span> wajib diisi.</p>
                </div>

                <div class="grid gap-5 p-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="mb-2 block text-sm font-semibold text-gray-700">
                            Nama Lengkap <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255"
                            placeholder="Contoh: Andi Pratama"
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
                        <label for="nim" class="mb-2 block text-sm font-semibold text-gray-700">
                            NIM <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input id="nim" name="nim" type="text" value="{{ old('nim') }}" required inputmode="numeric"
                            maxlength="8" pattern="[0-9]{8}" placeholder="Contoh: 20260001"
                            aria-invalid="{{ $errors->has('nim') ? 'true' : 'false' }}"
                            @class([
                                'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2',
                                'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('nim'),
                                'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('nim'),
                            ])>
                        <p class="mt-1.5 text-xs text-gray-500">8 angka, sekaligus jadi password awal mahasiswa.</p>
                        @error('nim')
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
                            @foreach ($pilihanKelas as $kelas)
                                <option value="{{ $kelas }}" @selected(old('class') === $kelas)>{{ $kelas }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-xs text-gray-500">Hanya kelas yang Anda ampu yang bisa dipilih.</p>
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
                            <option value="L" @selected(old('gender') === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('gender') === 'P')>Perempuan</option>
                        </select>
                        @error('gender')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="parent" class="mb-2 block text-sm font-semibold text-gray-700">Nama Orang Tua / Wali</label>
                        <input id="parent" name="parent" type="text" value="{{ old('parent') }}" maxlength="255"
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

                    <div>
                        <label for="phone" class="mb-2 block text-sm font-semibold text-gray-700">Nomor Telepon Orang Tua</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="20"
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
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end">
                    <a href="{{ route('dosen.kelola') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <i class="fas fa-check"></i>
                        Simpan Mahasiswa
                    </button>
                </div>
            </form>

            <div class="mt-5 flex gap-3 rounded-xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-800">
                <i class="fas fa-info-circle mt-0.5 shrink-0"></i>
                <p>Akun mahasiswa dibuat otomatis dengan status aktif. Email dan kata sandi awal dibuat dari NIM.</p>
            </div>
        @endif
    </div>
@endsection
