@extends('layouts.absensi')

@section('title', 'Izin / Sakit · Hadirin.web')

@php
    /*
    | Dua jenis pengajuan yang bisa dipilih. Bedanya cuma di warna dan
    | penjelasan, tapi dipisah supaya mahasiswa tidak salah pilih di tengah
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
            'placeholder' => 'Contoh: Ada acara nikah vina di luar kota, izin tidak masuk kampus hari ini.',
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
                    @if ($daftarSesi->isEmpty())
                        {{-- Belum ada jadwal, jadi tidak ada sesi yang bisa diajukan --}}
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
                                        Pengajuan melekat pada jadwal mata kuliahnya, jadi belum ada sesi
                                        yang bisa dipilih sampai jadwal mengajarnya diisi.
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
                    @elseif ($pilihanSesi->isEmpty())
                        {{-- Semua sesi hari ini sudah punya catatan, jadi tidak ada yang bisa diajukan --}}
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-start gap-4">
                                <span
                                    class="grid h-12 w-12 shrink-0 place-items-center rounded-xl {{ $semuaSudahTercatat ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                                    <i class="fas {{ $semuaSudahTercatat ? 'fa-circle-check' : 'fa-clock' }}"></i>
                                </span>
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-800">
                                        @if ($semuaSudahTercatat)
                                            Kehadiran hari ini sudah tercatat
                                        @else
                                            Semua jam pelajaran hari ini sudah selesai
                                        @endif
                                    </h2>
                                    <p class="mt-1 text-sm text-slate-600">
                                        @if ($semuaSudahTercatat)
                                            Setiap mata kuliah hari ini sudah punya catatan presensi, jadi
                                            formulir pengajuan tidak dikirim lagi.
                                        @else
                                            Sesi hari ini sudah berakhir, jadi pengajuan untuk jadwal itu
                                            tidak bisa diterima lagi.
                                        @endif
                                    </p>

                                    <div class="mt-5 flex flex-wrap gap-3">
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
                    @else
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

                            {{-- Mata kuliah (sesi hari ini) --}}
                            <div>
                                <label for="sesi" class="block text-sm font-medium text-slate-700">
                                    Mata kuliah
                                    <span class="text-red-500">*</span>
                                </label>
                                <select id="sesi" name="sesi" required
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200">
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
                                    Pengajuan melekat pada jadwalnya, jadi pilih jam pelajaran yang diajukan.
                                </p>
                                @error('sesi')
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
                    @endif
                </section>

                {{-- Panel samping --}}
                <section class="space-y-4">
                    {{-- Status hari ini --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-sm font-semibold text-slate-800">Status hari ini</h2>

                        @if ($daftarSesi->isEmpty())
                            <div class="mt-3 flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                                <i class="fas fa-calendar-xmark text-lg text-slate-400"></i>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">Tidak ada jadwal</p>
                                    <p class="text-xs text-slate-500">Belum ada jam pelajaran hari ini</p>
                                </div>
                            </div>
                        @else
                            <ul class="mt-3 space-y-2">
                                @foreach ($daftarSesi as $sesi)
                                    @php
                                        $absen = $sesi->absensis->first();
                                        $kondisi = $sesi->kondisiSekarang();
                                    @endphp
                                    <li class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2.5">
                                        <span class="min-w-0 text-xs font-semibold text-slate-800">
                                            {{ $sesi->judul() }}
                                            <span class="block font-normal text-slate-500">
                                                {{ $sesi->waktu_mulai->format('H:i') }} -
                                                {{ $sesi->waktu_selesai->format('H:i') }}
                                            </span>
                                        </span>
                                        <span
                                            class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $absen ? 'bg-emerald-100 text-emerald-700' : ($kondisi === 'berjalan' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-600') }}">
                                            @if ($absen)
                                                {{ ucfirst($absen->status) }} {{ $absen->waktu_absen->format('H:i') }}
                                            @elseif ($kondisi === 'berjalan')
                                                Sedang berlangsung
                                            @elseif ($kondisi === 'selesai')
                                                Belum presen
                                            @else
                                                Mulai {{ $sesi->waktu_mulai->format('H:i') }}
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <p class="mt-3 text-xs leading-relaxed text-slate-500">
                            @if ($pilihanSesi->isNotEmpty())
                                Kalau kamu sebenarnya bisa hadir, lebih cepat memakai scan QR atau Kode NIM.
                            @else
                                Semua mata kuliah hari ini sudah punya catatan presensi.
                            @endif
                        </p>
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
                                Pengajuan hanya bisa dikirim satu kali per mata kuliah.
                            </li>
                            <li class="flex gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-[10px] text-amber-500"></i>
                                Izin dan sakit tidak dihitung sebagai alpa.
                            </li>
                            <li class="flex gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-[10px] text-amber-500"></i>
                                Salah kirim? Hubungi pembimbing akademik supaya bisa diperbaiki.
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

        // Placeholder ikut berubah mengikuti jenis yang dipilih, supaya mahasiswa
        // langsung tahu contoh yang diharapkan. Elemen form tidak ada kalau
        // semua sesi hari ini sudah punya catatan, jadi skrip berhenti diam-diam.
        if (alasan !== null && hitungAlasan !== null) {
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
        }
    </script>
@endpush
