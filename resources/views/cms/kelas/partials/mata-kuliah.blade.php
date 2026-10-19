{{--
    Field "Mata Kuliah" untuk form tambah & ubah kelas.

    Dipakai lewat:
        @include('cms.kelas.partials.mata-kuliah', ['kelas' => null])   // tambah
        @include('cms.kelas.partials.mata-kuliah', ['kelas' => $kelas]) // ubah

    Pilihannya berasal dari tabel `mata_kuliah` lewat `MataKuliahController`, jadi
    mata kuliah yang baru ditambahkan langsung muncul di sini tanpa perlu
    restart server.
--}}

<div>
    <label for="mata_kuliah" class="mb-2 block text-sm font-semibold text-gray-700">Mata Kuliah</label>

    @if ($mataKuliah->isEmpty())
        <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm text-amber-800">
            Belum ada data mata kuliah. Tambahkan mata kuliah terlebih dahulu di menu
            <a href="{{ route('cms.mata-kuliah') }}" class="font-semibold underline">Mata Kuliah</a>
            sebelum menentukan mata kuliah kelas.
        </p>
    @else
        <select id="mata_kuliah" name="mata_kuliah"
            aria-invalid="{{ $errors->has('mata_kuliah') ? 'true' : 'false' }}"
            @class([
                'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('mata_kuliah'),
                'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('mata_kuliah'),
            ])>
            <option value="">Belum ditentukan</option>
            @foreach ($mataKuliah as $item)
                <option value="{{ $item->id }}"
                    @selected((string) old('mata_kuliah', $kelas?->mata_kuliah_id) === (string) $item->id)>
                    {{ $item->nama_mata_kuliah }} ({{ $item->kode_mata_kuliah }})
                </option>
            @endforeach
        </select>
    @endif

    @error('mata_kuliah')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
