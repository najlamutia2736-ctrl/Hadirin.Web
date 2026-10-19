{{--
    Field "Mata Kuliah" untuk form tambah & ubah dosen.

    Dipakai lewat:
        @include('cms.dosens.partials.mata-kuliah', ['dosen' => null])   // tambah
        @include('cms.dosens.partials.mata-kuliah', ['dosen' => $dosen])   // ubah

    Pilihannya berasal dari tabel `mata_kuliah`, sama seperti field mata kuliah di
    halaman Classes. Jadi mata kuliah yang dipakai di form ini persis baris yang
    sama dengan yang muncul di Mata Kuliah dan di dashboard dosen.
--}}

<div>
    <label for="subject" class="mb-2 block text-sm font-semibold text-gray-700">
        Mata Kuliah <span class="text-red-500" aria-hidden="true">*</span>
    </label>

    @if ($mataKuliah->isEmpty())
        <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm text-amber-800">
            Belum ada data mata kuliah. Tambahkan mata kuliah terlebih dahulu di menu
            <a href="{{ route('cms.mata-kuliah') }}" class="font-semibold underline">Mata Kuliah</a>
            sebelum menentukan mata kuliah dosen.
        </p>
    @else
        <select id="subject" name="subject"
            aria-invalid="{{ $errors->has('subject') ? 'true' : 'false' }}"
            @class([
                'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('subject'),
                'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('subject'),
            ])>
            <option value="">Belum ditentukan</option>
            @foreach ($mataKuliah as $item)
                <option value="{{ $item->id }}"
                    @selected((string) old('subject', $dosen?->mata_kuliah_id) === (string) $item->id)>
                    {{ $item->nama_mata_kuliah }} ({{ $item->kode_mata_kuliah }})
                </option>
            @endforeach
        </select>
    @endif

    @error('subject')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>