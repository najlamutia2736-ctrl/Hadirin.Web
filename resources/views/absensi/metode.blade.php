@extends('layouts.absensi')

@section('title', 'Metode Absensi · Hadirin.web')

@php
    /*
    | Metode absensi yang bisa dipilih siswa, dirangkai dari route yang sama
    | dengan menu di halaman absensi utama supaya tidak ada cara absen yang
    | muncul di satu halaman tapi tidak di halaman lain.
    */
    $metode = [
        [
            'label' => 'Scan QR Code',
            'ringkasan' => 'Arahkan kamera ke QR code yang terpasang di kelas.',
            'penjelasan' =>
                'Cara paling cepat. Kamera HP membaca QR code di dinding, lalu kehadiranmu langsung tercatat tanpa perlu mengetik apa pun.',
            'ikon' => 'fa-qrcode',
            'warna' => 'bg-indigo-50 text-indigo-600',
            'accent' => 'from-indigo-500 to-blue-500',
            'rute' => route('absensi.scan-qr'),
            'butuh' => 'Kamera + izin akses kamera',
            'langkah' => [
                'Buka halaman Scan QR Code dari daftar di atas.',
                'Izinkan browser memakai kamera saat diminta.',
                'Arahkan bingkai ke QR code sampai terbaca otomatis.',
            ],
        ],
        [
            'label' => 'ID Unik',
            'ringkasan' => 'Ketik kode unik pribadi yang tertera di kartu.',
            'penjelasan' =>
                'Dipakai kalau kamera tidak bisa dipakai, misalnya HP tidak punya kamera atau sedang dipakai aplikasi lain.',
            'ikon' => 'fa-keyboard',
            'warna' => 'bg-emerald-50 text-emerald-600',
            'accent' => 'from-emerald-500 to-teal-500',
            'rute' => route('absensi.id-unik'),
            'butuh' => 'Kode unik dari kartu siswa',
            'langkah' => [
                'Buka halaman ID Unik dari daftar di atas.',
                'Ketik kode yang tertera di kartu absensi.',
                'Kirim dan tunggu konfirmasi masuk.',
            ],
        ],
        [
            'label' => 'Izin / Sakit',
            'ringkasan' => 'Kirim keterangan kalau tidak bisa hadir di sekolah.',
            'penjelasan' =>
                'Berbeda dari dua cara di atas, ini bukan kehadiran. Izin dan sakit dicatat supaya tidak dihitung sebagai alpa.',
            'ikon' => 'fa-envelope-open-text',
            'warna' => 'bg-amber-50 text-amber-600',
            'accent' => 'from-amber-500 to-orange-500',
            'rute' => route('absensi.izin-sakit'),
            'butuh' => 'Alasan yang jelas',
            'langkah' => [
                'Buka halaman Izin / Sakit dari daftar di atas.',
                'Pilih jenis pengajuan dan tulis alasannya.',
                'Kirim pengajuan ke wali kelas.',
            ],
        ],
        [
            'label' => 'Notifikasi',
            'ringkasan' => 'Lihat riwayat dan hasil absensi yang sudah dikirim.',
            'penjelasan' =>
                'Hanya untuk melihat. Di sini kamu bisa cek apakah absensi hari ini sudah tercatat atau masih diproses.',
            'ikon' => 'fa-bell',
            'warna' => 'bg-sky-50 text-sky-600',
            'accent' => 'from-sky-500 to-cyan-500',
            'rute' => route('absensi.notifikasi'),
            'butuh' => 'Tidak ada',
            'langkah' => [
                'Buka halaman Notifikasi dari daftar di atas.',
                'Lihat daftar kehadiran terbaru.',
                'Cek status absensi hari ini.',
            ],
        ],
    ];

    /*
    | Status hari ini menentukan banner di bawah. Nilainya sama dengan peta
    | status di halaman absensi utama, supaya dua halaman tidak pernah
    | berbeda pendapat tentang kehadiran siswa yang sama.
    */
    $statusHariIni = match ($hariIni?->status) {
        'hadir' => [
            'label' => 'Hadir',
            'warna' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'ikon' => 'fa-circle-check',
        ],
        'izin' => [
            'label' => 'Izin',
            'warna' => 'bg-blue-50 text-blue-700 border-blue-200',
            'ikon' => 'fa-file-signature',
        ],
        'sakit' => [
            'label' => 'Sakit',
            'warna' => 'bg-amber-50 text-amber-700 border-amber-200',
            'ikon' => 'fa-heart-pulse',
        ],
        'alpha' => [
            'label' => 'Alpa',
            'warna' => 'bg-rose-50 text-rose-700 border-rose-200',
            'ikon' => 'fa-circle-xmark',
        ],
        default => [
            'label' => 'Belum Absen',
            'warna' => 'bg-slate-50 text-slate-700 border-slate-200',
            'ikon' => 'fa-hourglass-half',
        ],
    };
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
                    Metode Absensi
                </h1>
                <p class="mt-1 max-w-2xl text-slate-600">
                    Ada beberapa cara untuk mencatat kehadiran. Pilih yang paling sesuai dengan situasimu
                    di sekolah.
                </p>
            </section>

            {{-- Status hari ini --}}
            <section class="rounded-2xl border {{ $statusHariIni['warna'] }} p-5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <i class="fas {{ $statusHariIni['ikon'] }} text-xl"></i>
                        <div>
                            <p class="text-xs uppercase tracking-wide opacity-70">Status hari ini</p>
                            <p class="text-lg font-bold">{{ $statusHariIni['label'] }}</p>
                        </div>
                    </div>

                    @if ($hariIni)
                        <div class="text-sm">
                            <p>
                                <span class="opacity-70">Waktu: </span>
                                <span class="font-semibold">{{ $hariIni->waktu_absen->format('H:i') }} WIB</span>
                            </p>
                            @if ($hariIni->sesiAbsensi?->kode_sesi)
                                <p class="mt-0.5">
                                    <span class="opacity-70">Sesi: </span>
                                    <span class="font-semibold">{{ $hariIni->sesiAbsensi->kode_sesi }}</span>
                                </p>
                            @endif
                        </div>

                        <a href="{{ route('absensi.index') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-white/70 px-4 py-2.5 text-sm font-semibold transition-colors hover:bg-white">
                            Kembali
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    @else
                        <a href="{{ route('absensi.scan-qr') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-indigo-700">
                            Absen Sekarang
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    @endif
                </div>
            </section>

            {{-- Kartu tiap metode --}}
            <section>
                <h2 class="text-lg font-bold text-slate-800">Pilihan Cara Absen</h2>
                <p class="mt-0.5 text-sm text-slate-500">
                    Scan QR Code dan ID Unik sama-sama mencatat kehadiran. Izin / Sakit untuk pengajuan, dan
                    Notifikasi hanya untuk melihat riwayat.
                </p>

                <div class="mt-4 space-y-4">
                    @foreach ($metode as $nomor => $item)
                        <div
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-shadow duration-200 hover:shadow-md">
                            {{-- Sorotan warna tiap metode --}}
                            <div class="h-1.5 w-full bg-gradient-to-r {{ $item['accent'] }}"></div>

                            <div class="p-5 sm:p-6">
                                <div class="flex flex-col gap-5 sm:flex-row">
                                    {{-- Identitas metode --}}
                                    <div class="sm:w-52 sm:shrink-0">
                                        <div class="flex items-start gap-3">
                                            <span
                                                class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-lg {{ $item['warna'] }}">
                                                <i class="fas {{ $item['ikon'] }}"></i>
                                            </span>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="grid h-5 w-5 place-items-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-500">
                                                        {{ $nomor + 1 }}
                                                    </span>
                                                    <h3 class="font-semibold text-slate-800">{{ $item['label'] }}</h3>
                                                </div>
                                                <p class="mt-0.5 text-xs text-slate-500">{{ $item['ringkasan'] }}</p>
                                            </div>
                                        </div>

                                        <p
                                            class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-slate-50 px-2.5 py-1.5 text-[11px] text-slate-600">
                                            <i class="fas fa-circle-info text-[10px] text-slate-400"></i>
                                            {{ $item['butuh'] }}
                                        </p>
                                    </div>

                                    {{-- Penjelasan dan langkah --}}
                                    <div
                                        class="flex-1 border-t border-slate-100 pt-5 sm:border-l sm:border-t-0 sm:pl-6 sm:pt-0">
                                        <p class="text-sm leading-relaxed text-slate-600">
                                            {{ $item['penjelasan'] }}
                                        </p>

                                        <ol class="mt-4 space-y-2">
                                            @foreach ($item['langkah'] as $urutan => $langkah)
                                                <li class="flex gap-3 text-sm text-slate-600">
                                                    <span
                                                        class="grid h-5 w-5 shrink-0 place-items-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-500">
                                                        {{ $urutan + 1 }}
                                                    </span>
                                                    <span>{{ $langkah }}</span>
                                                </li>
                                            @endforeach
                                        </ol>

                                        <a href="{{ $item['rute'] }}"
                                            class="mt-5 inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-900">
                                            Buka {{ $item['label'] }}
                                            <i class="fas fa-arrow-right text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Catatan penting --}}
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-5">
                    <h2 class="text-sm font-semibold text-indigo-900">
                        <i class="fas fa-lightbulb mr-1.5 text-indigo-500"></i>
                        Tips memilih
                    </h2>
                    <ul class="mt-2.5 space-y-1.5 text-xs leading-relaxed text-indigo-800/80">
                        <li class="flex gap-2">
                            <i class="fas fa-circle-check mt-0.5 text-[10px] text-indigo-500"></i>
                            Di dalam kelas dan kamera lancar, pilih Scan QR Code.
                        </li>
                        <li class="flex gap-2">
                            <i class="fas fa-circle-check mt-0.5 text-[10px] text-indigo-500"></i>
                            Kamera bermasalah, pilih ID Unik.
                        </li>
                        <li class="flex gap-2">
                            <i class="fas fa-circle-check mt-0.5 text-[10px] text-indigo-500"></i>
                            Tidak bisa hadir sama sekali, pilih Izin / Sakit.
                        </li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h2 class="text-sm font-semibold text-slate-800">
                        <i class="fas fa-circle-info mr-1.5 text-slate-400"></i>
                        Perlu diingat
                    </h2>
                    <ul class="mt-2.5 space-y-1.5 text-xs leading-relaxed text-slate-600">
                        <li class="flex gap-2">
                            <i class="fas fa-circle-check mt-0.5 text-[10px] text-slate-400"></i>
                            Hanya perlu absen sekali perpelajaran, tidak perlu diulang.
                        </li>
                        <li class="flex gap-2">
                            <i class="fas fa-circle-check mt-0.5 text-[10px] text-slate-400"></i>
                            Kehadiran yang tercatat langsung terlihat oleh guru.
                        </li>
                        <li class="flex gap-2">
                            <i class="fas fa-circle-check mt-0.5 text-[10px] text-slate-400"></i>
                            Salah absen? Hubungi wali kelas supaya bisa diperbaiki.
                        </li>
                    </ul>
                </div>
            </section>
        </main>
    </div>
@endsection
