{{--
    Field "Guru Pengampu" untuk form tambah & ubah kelas.

    Dipakai lewat:
        @include('cms.classes.partials.guru-pengampu', ['kelas' => null])   // tambah
        @include('cms.classes.partials.guru-pengampu', ['kelas' => $kelas]) // ubah

    Nilai terpilih disimpan sebagai array `teachers[]` di tabel penghubung
    `guru_kelas`. Inilah sumber yang dipakai dashboard guru untuk menampilkan
    kelas milik seorang guru, jadi kelas yang ditambahkan di sini langsung
    menentukan apa yang muncul di dashboard guru tersebut.
--}}
@php
    $terpilih = collect(old('teachers', $kelas?->guru->pluck('id')->all() ?? []))
        ->reject(fn ($id) => $id === '' || $id === null)
        ->map(fn ($id) => (string) $id)
        ->all();
@endphp

<div class="sm:col-span-2">
    <label for="teachers" class="mb-2 block text-sm font-semibold text-gray-700">
        Guru Pengampu
    </label>

    @if ($gurus->isEmpty())
        <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm text-amber-800">
            Belum ada data guru. Tambahkan guru terlebih dahulu di menu
            <a href="{{ route('cms.teachers') }}" class="font-semibold underline">Teachers</a>
            sebelum menentukan pengampu kelas.
        </p>
    @else
        <select id="teachers" name="teachers[]" multiple size="6"
            @class([
                'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('teachers'),
                'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('teachers'),
            ])>
            <option value="" @selected($terpilih === [])>Belum ada pengampu</option>
            @foreach ($gurus as $guru)
                <option value="{{ $guru->id }}" @selected(in_array((string) $guru->id, $terpilih, true))>
                    {{ $guru->user?->name ?? 'Guru tanpa akun' }}
                    @if ($guru->mata_pelajaran)
                        &mdash; {{ $guru->mata_pelajaran }}
                    @endif
                </option>
            @endforeach
        </select>
    @endif

    <p class="mt-1.5 text-xs text-gray-500">
        Tahan <kbd class="rounded border border-gray-200 bg-gray-50 px-1">Ctrl</kbd>
        (atau <kbd class="rounded border border-gray-200 bg-gray-50 px-1">Cmd</kbd>) untuk memilih lebih dari satu guru.
        Pilih <span class="font-medium text-gray-600">Belum ada pengampu</span> bila kelas ini belum ditugaskan ke guru mana pun.
    </p>

    @error('teachers')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
    @error('teachers.*')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
