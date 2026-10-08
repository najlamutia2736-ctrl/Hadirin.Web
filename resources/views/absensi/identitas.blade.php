@extends('layouts.absensi')

@section('title', 'Identitas · Hadirin.web')

@section('konten')
    <div class="min-h-screen">
        {{-- Topbar --}}
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6">
                <a href="{{ route('beranda') }}" class="text-xl font-bold tracking-tight text-indigo-700">
                    Hadirin.<span class="text-slate-700">web</span>
                </a>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-slate-800">{{ $siswa->user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $siswa->kelas }}</p>
                    </div>
                    <span
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700">
                        {{ strtoupper(substr($siswa->user->name, 0, 1)) }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6">
            {{-- Judul --}}
            <section>
                <a href="{{ route('absensi.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition-colors hover:text-indigo-600">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Kembali ke absensi
                </a>
                <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-800 sm:text-3xl">
                    Identitas Siswa
                </h1>
                <p class="mt-1 max-w-2xl text-slate-600">
                    Data yang dipakai untuk absensi harian. Pastikan nama dan kontak wali selalu benar.
                </p>
            </section>

            @if (session('sukses'))
                <div role="alert"
                    class="flex gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    <i class="fas fa-circle-check mt-0.5 shrink-0"></i>
                    <p>{{ session('sukses') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    {{-- Kartu absensi --}}
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 to-blue-500"></div>
                        <div class="p-5 sm:p-6">
                            <div class="flex flex-wrap items-center gap-5">
                                <span
                                    class="grid h-20 w-20 shrink-0 place-items-center rounded-2xl bg-indigo-100 text-3xl font-bold text-indigo-700">
                                    {{ strtoupper(substr($siswa->user->name, 0, 1)) }}
                                </span>

                                <div class="min-w-0 flex-1">
                                    <h2 class="truncate text-xl font-bold text-slate-800">
                                        {{ $siswa->user->name }}
                                    </h2>
                                    <p class="mt-0.5 text-sm text-slate-500">
                                        {{ $siswa->kelas }}
                                        @if ($siswa->mata_pelajaran)
                                            &middot; {{ $siswa->mata_pelajaran }}
                                        @endif
                                    </p>
                                    <span
                                        class="mt-2 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $siswa->status === 'Aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                        <i class="fas fa-circle text-[6px]"></i>
                                        {{ $siswa->status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Formulir yang boleh diubah siswa sendiri --}}
                    <form method="POST" action="{{ route('absensi.identitas.update') }}"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        @csrf

                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-semibold text-slate-800">Data yang Bisa Diubah</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Kolom bertanda <span class="text-red-500">*</span> wajib diisi.
                            </p>
                        </div>

                        @if ($errors->any())
                            <div role="alert" class="border-b border-red-100 bg-red-50 px-6 py-4">
                                <div class="flex gap-3 text-sm text-red-700">
                                    <i class="fas fa-exclamation-circle mt-0.5 shrink-0"></i>
                                    <div>
                                        <p class="font-semibold">Periksa kembali data yang diisi.</p>
                                        <ul class="mt-2 list-inside list-disc space-y-1">
                                            @foreach ($errors->all() as $message)
                                                <li>{{ $message }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="space-y-5 px-6 py-6">
                            {{-- Nama --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-slate-700">
                                    Nama Lengkap
                                    <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="name" name="name" value="{{ old('name', $siswa->user->name) }}"
                                    required maxlength="255"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200">
                                @error('name')
                                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Jenis kelamin --}}
                            <div>
                                <label for="gender" class="block text-sm font-medium text-slate-700">
                                    Jenis Kelamin
                                    <span class="text-red-500">*</span>
                                </label>
                                <select id="gender" name="gender" required
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="L" @selected(old('gender', $siswa->jenis_kelamin) === 'L')>
                                        Laki-laki
                                    </option>
                                    <option value="P" @selected(old('gender', $siswa->jenis_kelamin) === 'P')>
                                        Perempuan
                                    </option>
                                </select>
                                @error('gender')
                                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Nama wali --}}
                            <div>
                                <label for="parent" class="block text-sm font-medium text-slate-700">
                                    Nama Wali
                                </label>
                                <input type="text" id="parent" name="parent" value="{{ old('parent', $siswa->wali) }}"
                                    maxlength="255" placeholder="Contoh: Budi Santoso"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200">
                                <p class="mt-1.5 text-xs text-slate-500">
                                    Dipakai guru saat ada hal yang perlu dikonfirmasi.
                                </p>
                                @error('parent')
                                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Telepon wali --}}
                            <div>
                                <label for="phone" class="block text-sm font-medium text-slate-700">
                                    Telepon / WhatsApp Wali
                                </label>
                                <input type="text" id="phone" name="phone" inputmode="tel"
                                    value="{{ old('phone', $siswa->telepon_wali) }}" maxlength="20"
                                    placeholder="Contoh: 08123456789"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200">
                                @error('phone')
                                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div
                            class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
                            <a href="{{ route('absensi.index') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100">
                                Batal
                            </a>
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
                                <i class="fas fa-check text-xs"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Panel samping --}}
                <section class="space-y-4">
                    {{-- Data yang tidak bisa diubah sendiri --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-sm font-semibold text-slate-800">Data Permanen</h2>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500">
                            Dua data di bawah tidak bisa diubah sendiri karena menentukan absensimu.
                        </p>

                        <dl class="mt-4 space-y-3">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <dt class="text-xs text-slate-500">NISN</dt>
                                <dd class="mt-0.5 font-mono text-lg font-bold tracking-wide text-slate-800">
                                    {{ $siswa->nisn }}
                                </dd>
                                <p class="mt-1 text-[11px] leading-relaxed text-slate-500">
                                    Selalu dipakai saat memilih Kode NISN. Salah ketik berarti absen manualmu gagal.
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-3">
                                <dt class="text-xs text-slate-500">Kelas</dt>
                                <dd class="mt-0.5 text-sm font-semibold text-slate-800">{{ $siswa->kelas }}</dd>
                                <p class="mt-1 text-[11px] leading-relaxed text-slate-500">
                                    Dipakai guru untuk memfilter siswa yang dia ampu.
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-3">
                                <dt class="text-xs text-slate-500">Email Akun</dt>
                                <dd class="mt-0.5 break-all text-xs font-medium text-slate-700">
                                    {{ $siswa->user->email }}
                                </dd>
                            </div>
                        </dl>

                        <p class="mt-3 flex gap-2 text-[11px] leading-relaxed text-slate-500">
                            <i class="fas fa-circle-info mt-0.5 text-[10px] text-slate-400"></i>
                            NISN, kelas, dan email sudah terdaftar di sekolah. Salah satu salah, hubungi wali kelas
                            supaya diperbaiki dari sisi admin.
                        </p>
                    </div>

                    {{-- Rekap singkat --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-sm font-semibold text-slate-800">Kehadiran Bulan Ini</h2>
                        <p class="text-xs text-slate-500">{{ now()->locale('id')->translatedFormat('F Y') }}</p>

                        <div class="mt-3 flex items-end justify-between">
                            <p class="text-3xl font-bold tracking-tight text-slate-800">
                                {{ $rekap['persentase'] }}<span class="text-lg text-slate-400">%</span>
                            </p>
                            <p class="text-xs text-slate-500">{{ $rekap['total'] }} hari tercatat</p>
                        </div>

                        <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-indigo-500" style="width: {{ $rekap['persentase'] }}%"></div>
                        </div>

                        <a href="{{ route('absensi.notifikasi') }}"
                            class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-900">
                            <i class="fas fa-bell text-xs"></i>
                            Lihat riwayat lengkap
                        </a>
                    </div>

                    {{-- Kontak wali --}}
                    <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-5">
                        <h2 class="text-sm font-semibold text-indigo-900">
                            <i class="fas fa-circle-info mr-1.5 text-indigo-500"></i>
                            Kenapa dibatasi
                        </h2>
                        <p class="mt-2 text-xs leading-relaxed text-indigo-800/80">
                            Kalau semua data bisa diubah sendiri, absensinya bisa dimanipulasi. NISN
                            menentukan keabsenan, dan kelas menentukan siapa yang melihat absensimu.
                        </p>
                    </div>
                </section>
            </div>
        </main>
    </div>
@endsection