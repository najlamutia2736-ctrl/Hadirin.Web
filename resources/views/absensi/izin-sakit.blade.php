@extends('layouts.absensi')

@section('title', 'Izin / Sakit · Hadirin.web')

@php
    /*
    | Dua jenis pengajuan yang bisa dipilih. Bedanya cuma di warna dan
    | penjelasan, tapi dipisah supaya siswa tidak salah pilih di tengah
    | pengisian formulir.
    */
    $jenis = [
        'izin' => [
            'label' => 'Izin',
            'deskripsi' => 'Ada keperluan yang tidak bisa ditinggalkan, misalnya acara keluarga atau urusan resmi.',
            'ikon' => 'fa-file-signature',
            'warna' => 'border-blue-200 bg-blue-50',
            'teks' => 'text-blue-700',
            'ikonWarna' => 'text-blue-500',
            'placeholder' => 'Contoh: Ada acara nikah vina di luar kota, izin tidak masuk sekolah hari ini.',
        ],
        'sakit' => [
            'label' => 'Sakit',
            'deskripsi' => 'Kamu sedang tidak sehat sehingga tidak bisa mengikuti pelajaran.',
            'ikon' => 'fa-heart-pulse',
            'warna' => 'border-amber-200 bg-amber-50',
            'teks' => 'text-amber-700',
            'ikonWarna' => 'text-amber-500',
            'placeholder' => 'Contoh: Demam sejak semalam, sudah minum obat dan beristirahat di rumah.',
        ],
    ];

    $terpilih = old('jenis', 'izin');
@endphp

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
                    Izin / Sakit
                </h1>
                <p class="mt-1 max-w-2xl text-slate-600">
                    Kirim pengajuan kalau kamu tidak bisa hadir. Pengajuan yang tercatat tidak dihitung sebagai
                    alpa.
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
                    <form method="POST" action="{{ route('absensi.izin-sakit.store') }}"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        @csrf

                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-semibold text-slate-800">Formulir Pengajuan</h2>
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

                        <div class="space-y-6 px-6 py-6">
                            {{-- Pilih jenis --}}
                            <fieldset>
                                <legend class="block text-sm font-medium text-slate-700">
                                    Jenis pengajuan
                                    <span class="text-red-500">*</span>
                                </legend>

                                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    @foreach ($jenis as $kunci => $item)
                                        <label
                                            class="relative flex cursor-pointer gap-3 rounded-xl border-2 p-4 transition-colors has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/60 {{ $terpilih === $kunci ? 'border-indigo-500 bg-indigo-50/60' : $item['warna'] }}">
                                            <input type="radio" name="jenis" value="{{ $kunci }}"
                                                @checked($terpilih === $kunci)
                                                class="mt-1 h-4 w-4 shrink-0 accent-indigo-600">
                                            <span>
                                                <span class="flex items-center gap-2 font-semibold {{ $item['teks'] }}">
                                                    <i class="fas {{ $item['ikon'] }} {{ $item['ikonWarna'] }}"></i>
                                                    {{ $item['label'] }}
                                                </span>
                                                <span class="mt-1 block text-xs leading-relaxed text-slate-600">
                                                    {{ $item['deskripsi'] }}
                                                </span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                @error('jenis')
                                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </fieldset>

                            {{-- Tanggal --}}
                            <div>
                                <label for="tanggal" class="block text-sm font-medium text-slate-700">
                                    Tanggal tidak hadir
                                    <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="tanggal" name="tanggal"
                                    value="{{ old('tanggal', now()->toDateString()) }}" min="{{ now()->toDateString() }}"
                                    required
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200">
                                <p class="mt-1.5 text-xs text-slate-500">
                                    Tidak boleh diisi tanggal yang sudah lewat.
                                </p>
                                @error('tanggal')
                                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Alasan --}}
                            <div>
                                <label for="alasan" class="block text-sm font-medium text-slate-700">
                                    Alasan
                                    <span class="text-red-500">*</span>
                                </label>
                                <textarea id="alasan" name="alasan" rows="4" required minlength="10" maxlength="500"
                                    placeholder="{{ $jenis[$terpilih]['placeholder'] }}"
                                    class="mt-1.5 w-full resize-y rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm leading-relaxed text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200">{{ old('alasan') }}</textarea>
                                <div class="mt-1.5 flex items-center justify-between text-xs text-slate-500">
                                    <span>Minimal 10 karakter, supaya alasannya jelas.</span>
                                    <span id="hitungAlasan">0 / 500</span>
                                </div>
                                @error('alasan')
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
                                <i class="fas fa-paper-plane text-xs"></i>
                                Kirim Pengajuan
                            </button>
                        </div>
                    </form>
                </section>

                {{-- Panel samping --}}
                <section class="space-y-4">
                    {{-- Status hari ini --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-sm font-semibold text-slate-800">Status hari ini</h2>

                        @if ($hariIni)
                            <div class="mt-3 flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                                <i
                                    class="fas {{ $hariIni->status === 'izin' ? 'fa-file-signature text-blue-500' : 'fa-heart-pulse text-amber-500' }} text-lg"></i>
                                <div>
                                    <p class="text-sm font-semibold capitalize text-slate-800">{{ $hariIni->status }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $hariIni->waktu_absen->format('H:i') }} WIB
                                    </p>
                                </div>
                            </div>
                            <p class="mt-3 text-xs leading-relaxed text-slate-500">
                                Kehadiran hari ini sudah tercatat, jadi formulir di samping tidak bisa dikirim
                                lagi.
                            </p>
                        @else
                            <div class="mt-3 flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                                <i class="fas fa-hourglass-half text-lg text-slate-400"></i>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">Belum absen</p>
                                    <p class="text-xs text-slate-500">Formulir masih bisa diisi</p>
                                </div>
                            </div>
                            <p class="mt-3 text-xs leading-relaxed text-slate-500">
                                Kalau kamu sebenarnya bisa hadir, lebih cepat memakai scan QR atau ID Unik.
                            </p>
                        @endif
                    </div>

                    {{-- Riwayat pengajuan --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-sm font-semibold text-slate-800">Pengajuan Terakhir</h2>

                        @if ($riwayat->isEmpty())
                            <p class="mt-3 text-xs leading-relaxed text-slate-500">
                                Belum pernah ada pengajuan izin atau sakit.
                            </p>
                        @else
                            <ul class="mt-3 space-y-3">
                                @foreach ($riwayat as $item)
                                    <li class="border-l-2 border-slate-200 pl-3">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-xs font-semibold capitalize text-slate-800">
                                                {{ $item->status }}
                                            </span>
                                            <span class="shrink-0 text-[11px] text-slate-400">
                                                {{ $item->waktu_absen->translatedFormat('d M Y') }}
                                            </span>
                                        </div>
                                        @if ($item->keterangan)
                                            <p class="mt-0.5 text-xs leading-relaxed text-slate-500">
                                                {{ $item->keterangan }}
                                            </p>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- Catatan --}}
                    <div class="rounded-2xl border border-amber-100 bg-amber-50/70 p-5">
                        <h2 class="text-sm font-semibold text-amber-900">
                            <i class="fas fa-circle-info mr-1.5 text-amber-500"></i>
                            Perlu diingat
                        </h2>
                        <ul class="mt-2.5 space-y-1.5 text-xs leading-relaxed text-amber-800/80">
                            <li class="flex gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-[10px] text-amber-500"></i>
                                Pengajuan hanya bisa dikirim satu kali per hari.
                            </li>
                            <li class="flex gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-[10px] text-amber-500"></i>
                                Izin dan sakit tidak dihitung sebagai alpa.
                            </li>
                            <li class="flex gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-[10px] text-amber-500"></i>
                                Salah kirim? Hubungi wali kelas supaya bisa diperbaiki.
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
        // FORMULIR IZIN / SAKIT
        // ============================================================

        const alasan = document.getElementById('alasan');
        const hitungAlasan = document.getElementById('hitungAlasan');

        // Placeholder ikut berubah mengikuti jenis yang dipilih, supaya siswa
        // langsung tahu contoh yang diharapkan.
        const placeholderIzin = @json($jenis['izin']['placeholder']);
        const placeholderSakit = @json($jenis['sakit']['placeholder']);

        function perbaruiPenghitung() {
            hitungAlasan.textContent = `${alasan.value.length} / 500`;
        }

        document.querySelectorAll('input[name="jenis"]').forEach((radio) => {
            radio.addEventListener('change', () => {
                alasan.placeholder = radio.value === 'sakit' ? placeholderSakit : placeholderIzin;
            });
        });

        alasan.addEventListener('input', perbaruiPenghitung);
        perbaruiPenghitung();
    </script>
@endpush
