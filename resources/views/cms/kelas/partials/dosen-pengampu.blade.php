{{--
    Field "Dosen Pengampu" untuk form tambah & ubah kelas.

    Dipakai lewat:
        @include('cms.kelas.partials.dosen-pengampu', ['kelas' => null])   // tambah
        @include('cms.kelas.partials.dosen-pengampu', ['kelas' => $kelas]) // ubah

    Nilai terpilih disimpan sebagai array `dosens[]` di tabel penghubung
    `dosen_kelas`. Inilah sumber yang dipakai dashboard dosen untuk menampilkan
    kelas milik seorang dosen, jadi kelas yang ditambahkan di sini langsung
    menentukan apa yang muncul di dashboard dosen tersebut.
--}}
@php
    $terpilih = collect(old('dosens', $kelas?->dosen->pluck('id')->all() ?? []))
        ->reject(fn ($id) => $id === '' || $id === null)
        ->map(fn ($id) => (string) $id)
        ->all();
@endphp

<div class="sm:col-span-2">
    <label for="dosens" class="mb-2 block text-sm font-semibold text-gray-700">
        Dosen Pengampu
    </label>

    @if ($dosens->isEmpty())
        <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm text-amber-800">
            Belum ada data dosen. Tambahkan dosen terlebih dahulu di menu
            <a href="{{ route('cms.dosens') }}" class="font-semibold underline">Teachers</a>
            sebelum menentukan pengampu kelas.
        </p>
    @else
        <select id="dosens" name="dosens[]" multiple size="6"
            @class([
                'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('dosens'),
                'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('dosens'),
            ])>
            <option value="" @selected($terpilih === [])>Belum ada pengampu</option>
            @foreach ($dosens as $dosen)
                <option value="{{ $dosen->id }}" @selected(in_array((string) $dosen->id, $terpilih, true))>
                    {{ $dosen->user?->name ?? 'Dosen tanpa akun' }}
                    @if ($dosen->mata_kuliah)
                        &mdash; {{ $dosen->mata_kuliah }}
                    @endif
                </option>
            @endforeach
        </select>
    @endif

    <p class="mt-1.5 text-xs text-gray-500">
        Tahan <kbd class="rounded border border-gray-200 bg-gray-50 px-1">Ctrl</kbd>
        (atau <kbd class="rounded border border-gray-200 bg-gray-50 px-1">Cmd</kbd>) untuk memilih lebih dari satu dosen.
        Pilih <span class="font-medium text-gray-600">Belum ada pengampu</span> bila kelas ini belum ditugaskan ke dosen mana pun.
    </p>

    @error('dosens')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
    @error('dosens.*')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
