{{--
    Field "Mata Pelajaran" untuk form tambah & ubah kelas.

    Dipakai lewat:
        @include('cms.classes.partials.mata-pelajaran', ['kelas' => null])   // tambah
        @include('cms.classes.partials.mata-pelajaran', ['kelas' => $kelas]) // ubah

    Pilihannya berasal dari tabel `mata_pelajaran` lewat `MataPelajaranController`, jadi
    mata pelajaran yang baru ditambahkan langsung muncul di sini tanpa perlu
    restart server.
--}}

<div>
    <label for="mata_pelajaran" class="mb-2 block text-sm font-semibold text-gray-700">Mata Pelajaran</label>

    @if ($mataPelajaran->isEmpty())
        <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm text-amber-800">
            Belum ada data mata pelajaran. Tambahkan mata pelajaran terlebih dahulu di menu
            <a href="{{ route('cms.mata-pelajaran') }}" class="font-semibold underline">Mata Pelajaran</a>
            sebelum menentukan mata pelajaran kelas.
        </p>
    @else
        <select id="mata_pelajaran" name="mata_pelajaran"
            aria-invalid="{{ $errors->has('mata_pelajaran') ? 'true' : 'false' }}"
            @class([
                'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('mata_pelajaran'),
                'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('mata_pelajaran'),
            ])>
            <option value="">Belum ditentukan</option>
            @foreach ($mataPelajaran as $item)
                <option value="{{ $item->id }}"
                    @selected((string) old('mata_pelajaran', $kelas?->mata_pelajaran_id) === (string) $item->id)>
                    {{ $item->nama_mata_pelajaran }} ({{ $item->kode_mata_pelajaran }})
                </option>
            @endforeach
        </select>
    @endif

    @error('mata_pelajaran')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
