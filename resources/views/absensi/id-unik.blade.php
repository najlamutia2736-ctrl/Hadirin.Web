@extends('layouts.absensi')

@section('title', 'Kode NIM · Hadirin.web')

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
                        <p class="text-sm font-semibold text-slate-800">{{ $mahasiswa->user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $mahasiswa->kelas }}</p>
                    </div>
                    <span
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700">
                        {{ strtoupper(substr($mahasiswa->user->name, 0, 1)) }}
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
                    Kembali ke presensi
                </a>
                <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-800 sm:text-3xl">
                    Presen Kode NIM
                </h1>
                <p class="mt-1 max-w-2xl text-slate-600">
                    Ketik kode yang tertera di kartu presensi. Pakai cara ini kalau kamera HP sedang tidak bisa
                    dipakai.
                </p>
            </section>

            {{-- Pesan hasil pengiriman --}}
            @if (session('sukses'))
                <div role="alert"
                    class="flex gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    <i class="fas fa-circle-check mt-0.5 shrink-0"></i>
                    <p>{{ session('sukses') }}</p>
                </div>
            @endif

            @if (session('gagal'))
                <div role="alert" class="flex gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <i class="fas fa-exclamation-circle mt-0.5 shrink-0"></i>
                    <p>{{ session('gagal') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {{-- Formulir --}}
                <section class="lg:col-span-2">
                    @if ($daftarSesi->isEmpty())
                        {{-- Belum ada jadwal kuliah hari ini, jadi belum ada sesi yang bisa dipilih --}}
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-start gap-4">
                                <span
                                    class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-slate-100 text-xl text-slate-400">
                                    <i class="fas fa-calendar-xmark"></i>
                                </span>
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-800">
                                        Belum ada jadwal kuliah hari ini
                                    </h2>
                                    <p class="mt-1 text-sm text-slate-600">
                                        Presensi dibuka otomatis mengikuti jadwal mata kuliah kelasmu, jadi
                                        tidak ada sesi yang bisa dipilih sampai jadwal mengajarnya diisi.
                                    </p>

                                    <div class="mt-5 flex flex-wrap gap-3">
                                        <a href="{{ route('absensi.index') }}"
                                            class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-900">
                                            Kembali ke presensi
                                            <i class="fas fa-arrow-right text-xs"></i>
                                        </a>
                                        <a href="{{ route('absensi.metode') }}"
                                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50">
                                            <i class="fas fa-list-check text-xs"></i>
                                            Lihat metode
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif ($absensiSesi !== null)
                        {{-- Sudah absen untuk sesi terpilih: form disembunyikan supaya tidak terkirim dua kali --}}
                        <div class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm">
                            <div class="flex items-start gap-4">
                                <span
                                    class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-emerald-50 text-xl text-emerald-600">
                                    <i class="fas fa-circle-check"></i>
                                </span>
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-800">
                                        @if ($pilihanSesi->isEmpty())
                                            Kehadiran hari ini sudah tercatat
                                        @else
                                            Presensi {{ $sesiTerpilih->judul() }} sudah tercatat
                                        @endif
                                    </h2>
                                    <p class="mt-1 text-sm text-slate-600">
                                        Dicatat pada {{ $absensiSesi->waktu_absen->format('H:i') }} WIB
                                        untuk {{ $sesiTerpilih->judul() }}
                                        @if ($absensiSesi->keterangan)
                                            · {{ $absensiSesi->keterangan }}
                                        @endif
                                    </p>

                                    <div class="mt-5 flex flex-wrap gap-3">
                                        @if ($pilihanSesi->isNotEmpty())
                                            <a href="{{ route('absensi.index') }}"
                                                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-indigo-700">
                                                Presensi mata kuliah lain
                                                <i class="fas fa-arrow-right text-xs"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('absensi.index') }}"
                                            class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-900">
                                            Kembali ke presensi
                                            <i class="fas fa-arrow-right text-xs"></i>
                                        </a>
                                        <a href="{{ route('absensi.notifikasi') }}"
                                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50">
                                            <i class="fas fa-bell text-xs"></i>
                                            Lihat riwayat
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif ($sesiTerpilih === null || $sesiTerpilih->kondisiSekarang() !== 'berjalan')
                        {{-- Sesi yang dipilih belum berjalan atau sudah berakhir, jadi form disembunyikan --}}
                        <div class="rounded-2xl border border-amber-200 bg-white p-6 shadow-sm">
                            <div class="flex items-start gap-4">
                                <span
                                    class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-amber-50 text-xl text-amber-600">
                                    <i class="fas fa-clock"></i>
                                </span>
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-800">
                                        Belum ada sesi yang sedang berlangsung
                                    </h2>
                                    <p class="mt-1 text-sm text-slate-600">
                                        @if ($sesiTerpilih?->kondisiSekarang() === 'belum')
                                            Sesi {{ $sesiTerpilih->judul() }} dimulai pukul
                                            {{ $sesiTerpilih->waktu_mulai->format('H:i') }} WIB. Kembali lagi
                                            saat jam pelajarannya sudah berjalan.
                                        @elseif ($sesiTerpilih !== null)
                                            Sesi {{ $sesiTerpilih->judul() }} sudah berakhir pukul
                                            {{ $sesiTerpilih->waktu_selesai->format('H:i') }} WIB, jadi
                                            kehadiran belum bisa dicatat lewat halaman ini.
                                        @else
                                            Presensi dibuka otomatis mengikuti jadwal mata kuliah kelasmu.
                                        @endif
                                    </p>

                                    <div class="mt-5 flex flex-wrap gap-3">
                                        <a href="{{ route('absensi.index') }}"
                                            class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-900">
                                            Lihat jadwal hari ini
                                            <i class="fas fa-arrow-right text-xs"></i>
                                        </a>
                                        <a href="{{ route('absensi.izin-sakit') }}"
                                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50">
                                            <i class="fas fa-file-signature text-xs"></i>
                                            Izin / Sakit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('absensi.id-unik.store') }}"
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            @csrf

                            <div class="border-b border-slate-100 px-6 py-5">
                                <h2 class="text-lg font-semibold text-slate-800">Masukkan Kode</h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    Kode ada di kartu presensi yang kamu terima dari kampus.
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

                            <div class="space-y-6 px-6 py-6">
                                {{-- Pilih mata kuliah yang sedang berlangsung --}}
                                <div>
                                    <label for="sesi" class="block text-sm font-medium text-slate-700">
                                        Mata kuliah
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select id="sesi" name="sesi" required
                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200">
                                        @foreach ($pilihanSesi as $opsi)
                                            <option value="{{ $opsi->id }}"
                                                @selected((string) old('sesi', $sesiTerpilih?->id) === (string) $opsi->id)>
                                                {{ $opsi->judul() }} ·
                                                {{ $opsi->waktu_mulai->format('H:i') }} -
                                                {{ $opsi->waktu_selesai->format('H:i') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1.5 text-xs text-slate-500">
                                        Presensi berlaku per mata kuliah, jadi pilih jam pelajaran yang sedang berjalan.
                                    </p>
                                    @error('sesi')
                                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Sesi yang dipilih --}}
                                <div class="flex items-center gap-3 rounded-xl bg-emerald-50 p-3.5">
                                    <i class="fas fa-clock text-emerald-600"></i>
                                    <p class="text-xs leading-relaxed text-emerald-800">
                                        Sesi {{ $sesiTerpilih->judul() }} sedang berjalan
                                        ({{ $sesiTerpilih->waktu_mulai->format('H:i') }} -
                                        {{ $sesiTerpilih->waktu_selesai->format('H:i') }}).
                                        Kehadiranmu akan masuk ke mata kuliah ini.
                                    </p>
                                </div>

                                {{-- Input kode --}}
                                <div>
                                    <label for="kode" class="block text-sm font-medium text-slate-700">
                                        Kode NIM
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="kode" name="kode" value="{{ old('kode') }}"
                                        inputmode="numeric" autocomplete="off" autocapitalize="characters"
                                        placeholder="Contoh: 20240101" required maxlength="32"
                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-center font-mono text-lg font-bold tracking-[0.3em] text-slate-800 outline-none transition placeholder:font-sans placeholder:text-sm placeholder:font-normal placeholder:tracking-normal placeholder:text-slate-400 focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200">
                                    <p class="mt-1.5 text-xs text-slate-500">
                                        Spasi dan tanda hubung tidak masalah, tetap dikenali sebagai kode yang sama.
                                    </p>
                                    @error('kode')
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
                                    Catat Kehadiran
                                </button>
                            </div>
                        </form>
                    @endif
                </section>

                {{-- Panel samping --}}
                <section class="space-y-4">
                    {{-- Kartu-absensi mini, supaya mahasiswa bisa cek kode sendiri --}}
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 to-blue-500"></div>
                        <div class="p-5">
                            <h2 class="text-sm font-semibold text-slate-800">Kartu Presensi</h2>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Kode yang dipakai untuk presen hari ini.
                            </p>

                            <p class="mt-4 rounded-xl bg-slate-50 py-4 text-center font-mono text-2xl font-bold tracking-[0.2em] text-slate-800">
                                {{ $mahasiswa->nim }}
                            </p>

                            <dl class="mt-4 space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <dt class="text-slate-500">Nama</dt>
                                    <dd class="font-semibold text-slate-800">{{ $mahasiswa->user->name }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-slate-500">Kelas</dt>
                                    <dd class="font-semibold text-slate-800">{{ $mahasiswa->kelas }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-slate-500">NIM</dt>
                                    <dd class="font-semibold text-slate-800">{{ $mahasiswa->nim }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- Cara alternatif --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-sm font-semibold text-slate-800">Cara lain</h2>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500">
                            Kalau kameramu sebenarnya bisa dipakai, scan QR code jauh lebih cepat daripada mengetik.
                        </p>

                        <a href="{{ route('absensi.scan-qr') }}"
                            class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-900">
                            <i class="fas fa-qrcode text-xs"></i>
                            Pakai Scan QR
                        </a>
                        <a href="{{ route('absensi.metode') }}"
                            class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50">
                            <i class="fas fa-list-check text-xs"></i>
                            Lihat semua metode
                        </a>
                    </div>

                    {{-- Catatan --}}
                    <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-5">
                        <h2 class="text-sm font-semibold text-indigo-900">
                            <i class="fas fa-circle-info mr-1.5 text-indigo-500"></i>
                            Perlu diingat
                        </h2>
                        <ul class="mt-2.5 space-y-1.5 text-xs leading-relaxed text-indigo-800/80">
                            <li class="flex gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-[10px] text-indigo-500"></i>
                                Kode hanya bisa dipakai oleh akun yang sedang login.
                            </li>
                            <li class="flex gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-[10px] text-indigo-500"></i>
                                Satu kali presen untuk tiap mata kuliah yang diikuti.
                            </li>
                            <li class="flex gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-[10px] text-indigo-500"></i>
                                Kehadiran langsung terlihat oleh dosen.
                            </li>
                        </ul>
                    </div>
                </section>
            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        // ============================================================
        // ABSEN KODE NIM
        // ============================================================

        const inputKode = document.getElementById('kode');

        // Formulir ini muncul hanya kalau belum absen, jadi null aman di sini.
        if (inputKode !== null) {
            inputKode.focus();

            // Spasi yang ditekan di tengah kode membuat form terkirim lebih
            // dulu, padahal kode belum lengkap. Dimakan supaya mahasiswa punya
            // waktu selesai mengetik.
            inputKode.addEventListener('keydown', (event) => {
                if (event.key === ' ') {
                    event.preventDefault();
                }
            });
        }
    </script>
@endpush