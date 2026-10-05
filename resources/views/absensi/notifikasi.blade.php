@extends('layouts.absensi')

@section('title', 'Notifikasi · Hadirin.web')

@php
    /*
    | Peta status ke tampilan. Dipakai di tiga tempat pada halaman ini: kartu
    | hari ini, strip 7 hari, dan tabel riwayat. Satu sumber supaya warna dan
    | ikon tidak pernah beda antarbagian.
    */
    $petakanStatus = [
        'hadir' => [
            'label' => 'Hadir',
            'ikon' => 'fa-circle-check',
            'lencana' => 'bg-emerald-100 text-emerald-700',
            'titik' => 'bg-emerald-500',
        ],
        'izin' => [
            'label' => 'Izin',
            'ikon' => 'fa-file-signature',
            'lencana' => 'bg-blue-100 text-blue-700',
            'titik' => 'bg-blue-500',
        ],
        'sakit' => [
            'label' => 'Sakit',
            'ikon' => 'fa-heart-pulse',
            'lencana' => 'bg-amber-100 text-amber-700',
            'titik' => 'bg-amber-500',
        ],
        'alpha' => [
            'label' => 'Alpa',
            'ikon' => 'fa-circle-xmark',
            'lencana' => 'bg-rose-100 text-rose-700',
            'titik' => 'bg-rose-500',
        ],
        null => [
            'label' => 'Belum Absen',
            'ikon' => 'fa-hourglass-half',
            'lencana' => 'bg-slate-100 text-slate-700',
            'titik' => 'bg-slate-400',
        ],
    ];

    $statusHariIni = $petakanStatus[$hariIni?->status] ?? $petakanStatus[null];

    /*
    | Keterangan singkat untuk tiap status di bawah rekap. Ditulis di sini
    | supaya Rules absensi tidak tersebar di beberapa tempat blade.
    */
    $ringkasanStatus = [
        'hadir' => 'Tercatat tepat waktu sesuai sesi yang berlaku.',
        'izin' => 'Sudah disetujui, tidak dihitung sebagai alpa.',
        'sakit' => 'Sudah tercatat, tidak dihitung sebagai alpa.',
        'alpha' => 'Belum ada catatan sampai batas waktu sesi berakhir.',
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
                    Notifikasi Absensi
                </h1>
                <p class="mt-1 max-w-2xl text-slate-600">
                    Riwayat kehadiran yang sudah tercatat, lengkap dengan rekap bulan berjalan.
                </p>
            </section>

            {{-- Status hari ini --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-lg {{ $statusHariIni['lencana'] }}">
                            <i class="fas {{ $statusHariIni['ikon'] }}"></i>
                        </span>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Status hari ini</p>
                            <p class="text-lg font-bold text-slate-800">{{ $statusHariIni['label'] }}</p>
                        </div>
                    </div>

                    @if ($hariIni)
                        <div class="text-sm">
                            <p>
                                <span class="text-slate-500">Waktu: </span>
                                <span class="font-semibold text-slate-800">
                                    {{ $hariIni->waktu_absen->format('H:i') }} WIB
                                </span>
                            </p>
                            @if ($hariIni->sesiAbsensi?->kode_sesi)
                                <p class="mt-0.5">
                                    <span class="text-slate-500">Sesi: </span>
                                    <span class="font-semibold text-slate-800">
                                        {{ $hariIni->sesiAbsensi->kode_sesi }}
                                    </span>
                                </p>
                            @endif
                            @if ($hariIni->keterangan)
                                <p class="mt-0.5 text-xs text-slate-500">{{ $hariIni->keterangan }}</p>
                            @endif
                        </div>
                    @else
                        <a href="{{ route('absensi.scan-qr') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-indigo-700">
                            Absen Sekarang
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    @endif
                </div>
            </section>

            {{-- Strip 7 hari terakhir --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-800">7 Hari Terakhir</h2>
                    <span class="text-xs text-slate-500">
                        {{ $mingguan[0]['tanggal']->translatedFormat('d M') }} -
                        {{ $mingguan[6]['tanggal']->translatedFormat('d M Y') }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-7 gap-1.5 sm:gap-2">
                    @foreach ($mingguan as $hari)
                        @php $status = $petakanStatus[$hari['status']] ?? $petakanStatus[null]; @endphp
                        <div class="text-center">
                            <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                                {{ $hari['tanggal']->translatedFormat('D') }}
                            </p>
                            <p class="text-[10px] text-slate-400">
                                {{ $hari['tanggal']->format('d') }}
                            </p>

                            {{-- Titik diisi status hari itu; hari tanpa catatan
                                 sengaja dibiarkan kosong, bukan ditandai hadir. --}}
                            <div
                                class="mx-auto mt-1.5 grid h-9 w-full max-w-[44px] place-items-center rounded-xl text-xs {{ $status['lencana'] }}"
                                title="{{ $status['label'] }}{{ $hari['waktu'] ? ' pukul '.$hari['waktu'] : '' }}">
                                @if ($hari['status'])
                                    <i class="fas {{ $status['ikon'] }} text-sm"></i>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </div>

                            <p class="mt-1 text-[10px] text-slate-400">
                                {{ $hari['waktu'] ?? '-' }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1.5 border-t border-slate-100 pt-3 text-[11px] text-slate-500">
                    @foreach (['hadir', 'izin', 'sakit', null] as $kunci)
                        @php $item = $petakanStatus[$kunci]; @endphp
                        <span class="flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full {{ $item['titik'] }}"></span>
                            {{ $item['label'] }}
                        </span>
                    @endforeach
                </div>
            </section>

            {{-- Rekap bulan berjalan --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-800">Rekap Bulan Ini</h2>
                    <span class="text-xs text-slate-500">{{ now()->locale('id')->translatedFormat('F Y') }}</span>
                </div>

                <div class="mt-4 flex items-end justify-between">
                    <p class="text-3xl font-bold tracking-tight text-slate-800">
                        {{ $rekap['persentase'] }}<span class="text-lg text-slate-400">%</span>
                    </p>
                    <p class="text-xs text-slate-500">{{ $rekap['total'] }} hari tercatat</p>
                </div>

                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-indigo-500" style="width: {{ $rekap['persentase'] }}%"></div>
                </div>

                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                    @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpha' => 'Alpa'] as $kunci => $label)
                        @php $item = $petakanStatus[$kunci]; @endphp
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                            <dt class="flex items-center gap-1.5 text-xs text-slate-500">
                                <span class="h-2 w-2 rounded-full {{ $item['titik'] }}"></span>
                                {{ $label }}
                            </dt>
                            <dd class="mt-1 text-xl font-bold text-slate-800">{{ $rekap[$kunci] }}</dd>
                        </div>
                    @endforeach
                </dl>

                <dl class="mt-4 space-y-2 border-t border-slate-100 pt-4">
                    @foreach ($ringkasanStatus as $kunci => $ringkasan)
                        @php $item = $petakanStatus[$kunci]; @endphp
                        <div class="flex gap-2.5 text-xs">
                            <i class="fas {{ $item['ikon'] }} mt-0.5 text-[10px] text-slate-400"></i>
                            <span class="font-semibold text-slate-700">{{ $item['label'] }}:</span>
                            <span class="text-slate-500">{{ $ringkasan }}</span>
                        </div>
                    @endforeach
                </dl>
            </section>

            {{-- Riwayat lengkap --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-800">Riwayat Absensi</h2>
                    <span class="text-xs text-slate-500">{{ $riwayat->total() }} catatan</span>
                </div>

                @if ($riwayat->isEmpty())
                    <div class="px-5 py-12 text-center">
                        <span
                            class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-lg text-slate-400">
                            <i class="fas fa-bell"></i>
                        </span>
                        <p class="mt-3 text-sm font-semibold text-slate-800">Belum ada riwayat</p>
                        <p class="mt-1 text-xs text-slate-500">
                            Riwayat muncul setelah absensi pertama tercatat.
                        </p>
                        <a href="{{ route('absensi.index') }}"
                            class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-indigo-700">
                            Mulai absen
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                @else
                    {{-- Tabel disembunyikan di layar kecil, replaced by daftar
                         kartu supaya tidak perlu scroll horizontal. --}}
                    <div class="hidden overflow-x-auto md:block">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 text-left text-xs text-slate-400">
                                    <th class="px-5 py-3 font-medium">Tanggal</th>
                                    <th class="px-5 py-3 font-medium">Hari</th>
                                    <th class="px-5 py-3 font-medium">Waktu</th>
                                    <th class="px-5 py-3 font-medium">Sesi</th>
                                    <th class="px-5 py-3 font-medium">Status</th>
                                    <th class="px-5 py-3 font-medium">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($riwayat as $item)
                                    @php $status = $petakanStatus[$item->status] ?? $petakanStatus[null]; @endphp
                                    <tr class="border-b border-slate-50 last:border-0">
                                        <td class="whitespace-nowrap px-5 py-3 font-medium text-slate-800">
                                            {{ $item->waktu_absen->format('d/m/Y') }}
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3 text-slate-500">
                                            {{ $item->waktu_absen->translatedFormat('l') }}
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3 font-semibold text-slate-700">
                                            {{ $item->waktu_absen->format('H:i') }}
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3 text-slate-500">
                                            {{ $item->sesiAbsensi?->kode_sesi ?? '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3">
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $status['lencana'] }}">
                                                <i class="fas {{ $status['ikon'] }} text-[10px]"></i>
                                                {{ $status['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-xs text-slate-500">
                                            {{ $item->keterangan ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Versi kartu untuk layar kecil --}}
                    <ul class="divide-y divide-slate-100 md:hidden">
                        @foreach ($riwayat as $item)
                            @php $status = $petakanStatus[$item->status] ?? $petakanStatus[null]; @endphp
                            <li class="px-5 py-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">
                                            {{ $item->waktu_absen->translatedFormat('l, d M Y') }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            {{ $item->waktu_absen->format('H:i') }} WIB
                                            @if ($item->sesiAbsensi?->kode_sesi)
                                                &middot; {{ $item->sesiAbsensi->kode_sesi }}
                                            @endif
                                        </p>
                                    </div>
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $status['lencana'] }}">
                                        <i class="fas {{ $status['ikon'] }} text-[10px]"></i>
                                        {{ $status['label'] }}
                                    </span>
                                </div>
                                @if ($item->keterangan)
                                    <p class="mt-2 text-xs leading-relaxed text-slate-500">{{ $item->keterangan }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    @if ($riwayat->hasPages())
                        <div class="border-t border-slate-100 px-5 py-3">
                            {{ $riwayat->links() }}
                        </div>
                    @endif
                @endif
            </section>
        </main>
    </div>
@endsection