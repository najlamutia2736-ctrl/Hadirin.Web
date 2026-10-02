{{--
    Field form jadwal, dipakai bersama oleh halaman tambah & ubah.

    Dipanggil dengan:
      - $jadwal : model yang sedang diedit, atau `null` saat tambah
      - $kelasList, $guruList, $daftarHari, $daftarStatus : pilihan dropdown

    Nilai `old()` diutamakan supaya isian yang gagal validasi tidak hilang,
    dan nilai model dipakai sebagai cadangannya.
--}}
@php
    $item = $jadwal ?? null;

    $nilai = fn (string $kolom, $cadangan = null) => old($kolom, $cadangan);

    $kelasError = $errors->has('kelas_id');
    $guruError = $errors->has('guru_id');
    $hariError = $errors->has('hari');
    $jamMulaiError = $errors->has('jam_mulai');
    $jamSelesaiError = $errors->has('jam_selesai');
    $tahunError = $errors->has('tahun_ajaran');
    $statusError = $errors->has('status');

    // Kelas bawaan untuk form tambah: kelas aktif pertama.
    $kelasDefault = $nilai('kelas_id', $item?->kelas_id ?? $kelasList->first()?->id);
    $hariDefault = $nilai('hari', $item?->hari ?? $daftarHari[0]);
    $statusDefault = $nilai('status', $item?->status ?? $daftarStatus[0]);
    $tahunDefault = $nilai('tahun_ajaran', $item?->tahun_ajaran ?? now()->year);
@endphp

<div class="grid gap-5 p-6 sm:grid-cols-2">
    <div>
        <label for="kelas_id" class="mb-2 block text-sm font-semibold text-gray-700">
            Kelas <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <select id="kelas_id" name="kelas_id" required @class([
            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $kelasError,
            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $kelasError,
        ])>
            <option value="" disabled @selected((string) $kelasDefault === '')>Pilih kelas</option>
            @foreach ($kelasList as $kelas)
                <option value="{{ $kelas->id }}" @selected((string) $kelasDefault === (string) $kelas->id)>
                    {{ $kelas->nama_kelas }}
                </option>
            @endforeach
        </select>
        @error('kelas_id')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="guru_id" class="mb-2 block text-sm font-semibold text-gray-700">
            Guru Pengajar <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <select id="guru_id" name="guru_id" required @class([
            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $guruError,
            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $guruError,
        ])>
            <option value="" disabled @selected((string) $nilai('guru_id', $item?->guru_id) === '')>Pilih guru</option>
            @foreach ($guruList as $guru)
                <option value="{{ $guru->id }}" @selected((string) $nilai('guru_id', $item?->guru_id) === (string) $guru->id)>
                    {{ $guru->user?->name ?? 'Guru tanpa akun' }}
                </option>
            @endforeach
        </select>
        @error('guru_id')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="mata_pelajaran" class="mb-2 block text-sm font-semibold text-gray-700">
            Mata Pelajaran <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <input id="mata_pelajaran" name="mata_pelajaran" type="text" required maxlength="100"
            value="{{ $nilai('mata_pelajaran', $item?->mata_pelajaran) }}"
            placeholder="Contoh: Informatika" autocomplete="off"
            @class([
                'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2',
                'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has(
                    'mata_pelajaran'),
                'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has(
                    'mata_pelajaran'),
            ])>
        @error('mata_pelajaran')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="hari" class="mb-2 block text-sm font-semibold text-gray-700">
            Hari <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <select id="hari" name="hari" required @class([
            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $hariError,
            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $hariError,
        ])>
            @foreach ($daftarHari as $hari)
                <option value="{{ $hari }}" @selected($hariDefault === $hari)>{{ $hari }}</option>
            @endforeach
        </select>
        @error('hari')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tahun_ajaran" class="mb-2 block text-sm font-semibold text-gray-700">
            Tahun Ajaran <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <input id="tahun_ajaran" name="tahun_ajaran" type="number" required
            min="2000" max="{{ now()->year + 2 }}" value="{{ $tahunDefault }}"
            @class([
                'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $tahunError,
                'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $tahunError,
            ])>
        @error('tahun_ajaran')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="jam_mulai" class="mb-2 block text-sm font-semibold text-gray-700">
            Jam Mulai <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <input id="jam_mulai" name="jam_mulai" type="time" required
            value="{{ $nilai('jam_mulai', $item?->jamMulaiSingkat()) }}"
            @class([
                'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $jamMulaiError,
                'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $jamMulaiError,
            ])>
        @error('jam_mulai')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="jam_selesai" class="mb-2 block text-sm font-semibold text-gray-700">
            Jam Selesai <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <input id="jam_selesai" name="jam_selesai" type="time" required
            value="{{ $nilai('jam_selesai', $item?->jamSelesaiSingkat()) }}"
            @class([
                'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $jamSelesaiError,
                'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $jamSelesaiError,
            ])>
        @error('jam_selesai')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="ruang" class="mb-2 block text-sm font-semibold text-gray-700">Ruang</label>
        <input id="ruang" name="ruang" type="text" maxlength="50"
            value="{{ $nilai('ruang', $item?->ruang) }}" placeholder="Contoh: R. 101" autocomplete="off"
            @class([
                'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2',
                'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500',
            ])>
        @error('ruang')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="mb-2 block text-sm font-semibold text-gray-700">
            Status <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <select id="status" name="status" required @class([
            'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
            'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $statusError,
            'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $statusError,
        ])>
            @foreach ($daftarStatus as $status)
                <option value="{{ $status }}" @selected($statusDefault === $status)>{{ $status }}</option>
            @endforeach
        </select>
        <p class="mt-1.5 text-xs text-gray-500">Jadwal Nonaktif tidak dihitung saat mengecek bentrok.</p>
        @error('status')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
