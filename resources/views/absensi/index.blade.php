@extends('layouts.absensi')

@section('title', 'Absensi Siswa · Hadirin.web')

@php
    // Peta status dari kolom enum `absensis.status` ke tampilan. Nilai `null`
    // berarti siswa belum melakukan absensi hari ini.
    $petakanStatus = [
        'hadir' => [
            'label' => 'Hadir',
            'ringkasan' => 'Kehadiran tercatat',
            'ikon' => 'fa-circle-check',
            'kartu' => 'from-emerald-500 to-teal-500',
            'lencana' => 'bg-emerald-100 text-emerald-700',
            'garis' => 'bg-emerald-500',
        ],
        'izin' => [
            'label' => 'Izin',
            'ringkasan' => 'Absensi izin sudah tercatat',
            'ikon' => 'fa-file-signature',
            'kartu' => 'from-blue-500 to-sky-500',
            'lencana' => 'bg-blue-100 text-blue-700',
            'garis' => 'bg-blue-500',
        ],
        'sakit' => [
            'label' => 'Sakit',
            'ringkasan' => 'Absensi sakit sudah tercatat',
            'ikon' => 'fa-heart-pulse',
            'kartu' => 'from-amber-500 to-orange-500',
            'lencana' => 'bg-amber-100 text-amber-700',
            'garis' => 'bg-amber-500',
        ],
        'alpha' => [
            'label' => 'Alpa',
            'ringkasan' => 'Terdcatat tidak hadir',
            'ikon' => 'fa-circle-xmark',
            'kartu' => 'from-rose-500 to-red-500',
            'lencana' => 'bg-rose-100 text-rose-700',
            'garis' => 'bg-rose-500',
        ],
        null => [
            'label' => 'Belum Absen',
            'ringkasan' => 'Pilih salah satu cara untuk absen hari ini',
            'ikon' => 'fa-hourglass-half',
            'kartu' => 'from-indigo-700 to-purple-500',
            'lencana' => 'bg-slate-100 text-slate-700',
            'garis' => 'bg-slate-400',
        ],
    ];

    $status = $petakanStatus[$hariIni?->status] ?? $petakanStatus[null];

    $menu = [
        [
            'label' => 'Scan QR Code',
            'deskripsi' => 'Arahkan kamera ke QR code yang terpasang di kelas.',
            'ikon' => 'fa-qrcode',
            'warna' => 'bg-indigo-50 text-indigo-600',
            'rute' => route('absensi.scan-qr'),
        ],
        [
            'label' => 'ID Unik',
            'deskripsi' => 'Ketik kode unik pribadi yang tertera di kartu.',
            'ikon' => 'fa-keyboard',
            'warna' => 'bg-emerald-50 text-emerald-600',
            'rute' => route('absensi.id-unik'),
        ],
        [
            'label' => 'Izin / Sakit',
            'deskripsi' => 'Kirim keterangan kalau tidak bisa hadir di sekolah.',
            'ikon' => 'fa-envelope-open-text',
            'warna' => 'bg-amber-50 text-amber-600',
            'rute' => route('absensi.izin-sakit'),
        ],
        [
            'label' => 'Notifikasi',
            'deskripsi' => 'Lihat riwayat dan hasil absensi yang sudah dikirim.',
            'ikon' => 'fa-bell',
            'warna' => 'bg-sky-50 text-sky-600',
            'rute' => route('absensi.notifikasi'),
        ],
    ];
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
                        <p class="text-sm font-semibold text-slate-800">{{ auth()->user()?->name }}</p>
                        <p class="text-xs text-slate-500">{{ $siswa->kelas }}</p>
                    </div>
                    <span
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'S', 0, 1)) }}
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
            {{-- Sapaan --}}
            <section>
                <h1 class="text-2xl font-bold tracking-tight text-slate-800 sm:text-3xl">
                    Halo, {{ auth()->user()?->name }} 👋
                </h1>
                <p class="mt-1 text-slate-600">
                    {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                </p>
            </section>

            {{-- Status kehadiran hari ini --}}
            <section class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                <div
                    class="relative overflow-hidden rounded-2xl bg-gradient-to-br p-6 text-white shadow-lg lg:col-span-2 {{ $status['kartu'] }}">
                    <div class="pointer-events-none absolute -right-10 -top-12 h-48 w-48 rounded-full bg-white/10"></div>
                    <div class="pointer-events-none absolute -bottom-16 right-32 h-40 w-40 rounded-full bg-white/5"></div>

                    <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-white/80">Status kehadiran hari ini</p>
                            <div class="mt-2 flex items-center gap-3">
                                <i class="fas {{ $status['ikon'] }} text-3xl"></i>
                                <h2 class="text-3xl font-bold tracking-tight">{{ $status['label'] }}</h2>
                            </div>
                            <p class="mt-2 text-sm text-white/80">{{ $status['ringkasan'] }}</p>

                            @if ($hariIni)
                                <dl class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-xs text-white/90">
                                    <div>
                                        <dt class="inline opacity-75">Waktu: </dt>
                                        <dd class="inline font-semibold">
                                            {{ $hariIni->waktu_absen->format('H:i') }} WIB
                                        </dd>
                                    </div>
                                    @if ($hariIni->sesiAbsensi?->kode_sesi)
                                        <div>
                                            <dt class="inline opacity-75">Sesi: </dt>
                                            <dd class="inline font-semibold">{{ $hariIni->sesiAbsensi->kode_sesi }}</dd>
                                        </div>
                                    @endif
                                    @if ($hariIni->keterangan)
                                        <div>
                                            <dt class="inline opacity-75">Keterangan: </dt>
                                            <dd class="inline font-semibold">{{ $hariIni->keterangan }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            @endif
                        </div>

                        @unless ($hariIni)
                            <a href="{{ route('absensi.metode') }}"
                                class="inline-flex shrink-0 items-center justify-center gap-2 self-start rounded-xl bg-white/20 px-5 py-3 text-sm font-semibold text-white ring-1 ring-white/25 transition-colors hover:bg-white/30 sm:self-auto">
                                Absen Sekarang
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        @endunless
                    </div>
                </div>

                {{-- Rekap bulan berjalan --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                            <i class="fas fa-chart-simple"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-slate-800">Bulan Ini</h3>
                            <p class="text-xs text-slate-500">
                                {{ now()->locale('id')->translatedFormat('F Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex items-end justify-between">
                        <p class="text-3xl font-bold tracking-tight text-slate-800">
                            {{ $rekap['persentase'] }}<span class="text-lg text-slate-400">%</span>
                        </p>
                        <p class="text-xs text-slate-500">{{ $rekap['total'] }} hari tercatat</p>
                    </div>

                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-indigo-500" style="width: {{ $rekap['persentase'] }}%"></div>
                    </div>

                    <dl class="mt-5 grid grid-cols-2 gap-3 text-sm">
                        @foreach (['hadir' => ['Hadir', 'text-emerald-600'], 'izin' => ['Izin', 'text-blue-600'], 'sakit' => ['Sakit', 'text-amber-600'], 'alpha' => ['Alpa', 'text-rose-600']] as $kunci => [$label, $warna])
                            <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                                <dt class="text-slate-600">{{ $label }}</dt>
                                <dd class="font-bold {{ $warna }}">{{ $rekap[$kunci] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </section>

            {{-- Pilihan cara absen --}}
            <section>
                <div class="flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Pilih Cara Absen</h2>
                        <p class="mt-0.5 text-sm text-slate-500">
                            @if ($hariIni)
                                Kehadiran hari ini sudah tercatat. Kamu masih bisa mengganti lewat izin atau sakit.
                            @else
                                Gunakan salah satu cara di bawah ini untuk mencatat kehadiranmu.
                            @endif
                        </p>
                    </div>

                    <a href="{{ route('absensi.metode') }}"
                        class="text-sm font-medium text-indigo-600 transition-colors hover:text-indigo-800">
                        Lihat semua metode
                        <i class="fas fa-arrow-right ml-1 text-xs"></i>
                    </a>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($menu as $item)
                        <a href="{{ $item['rute'] }}"
                            class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                            <span
                                class="grid h-11 w-11 place-items-center rounded-xl text-lg transition-transform duration-200 group-hover:scale-110 {{ $item['warna'] }}">
                                <i class="fas {{ $item['ikon'] }}"></i>
                            </span>
                            <h3 class="mt-4 font-semibold text-slate-800">{{ $item['label'] }}</h3>
                            <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $item['deskripsi'] }}</p>
                            <span
                                class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                Buka
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- Identitas singkat --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-800">Identitas Siswa</h2>

                <dl class="mt-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-400">NISN</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $siswa->nisn }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-400">Kelas</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $siswa->kelas }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-400">Mata Pelajaran</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $siswa->mata_pelajaran ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-400">Jenis Kelamin</dt>
                        <dd class="mt-1 font-semibold text-slate-800">
                            {{ $siswa->jenis_kelamin === 'P' ? 'Perempuan' : ($siswa->jenis_kelamin === 'L' ? 'Laki-laki' : '-') }}
                        </dd>
                    </div>
                </dl>

                <a href="{{ route('absensi.identitas') }}"
                    class="mt-5 inline-flex items-center gap-2 text-sm font-medium text-indigo-600 transition-colors hover:text-indigo-800">
                    <i class="fas fa-user-pen text-xs"></i>
                    Perbarui identitas
                </a>
            </section>
        </main>
    </div>
@endsection
