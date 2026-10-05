{{--
    Field "Jurusan" untuk form tambah & ubah kelas.

    Dipakai lewat:
        @include('cms.classes.partials.jurusan', ['kelas' => null])   // tambah
        @include('cms.classes.partials.jurusan', ['kelas' => $kelas]) // ubah

    Pilihannya berasal dari tabel `jurusan` lewat `JurusanController`, jadi
    jurusan yang baru ditambahkan langsung muncul di sini tanpa perlu
    restart server.
--}}

<div>
    <label for="jurusan" class="mb-2 block text-sm font-semibold text-gray-700">Jurusan</label>

    @if ($jurusan->isEmpty())
        <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm text-amber-800">
            Belum ada data jurusan. Tambahkan jurusan terlebih dahulu di menu
            <a href="{{ route('cms.jurusan') }}" class="font-semibold underline">Departments</a>
            sebelum menentukan jurusan kelas.
        </p>
    @else
        <select id="jurusan" name="jurusan"
            aria-invalid="{{ $errors->has('jurusan') ? 'true' : 'false' }}"
            @class([
                'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('jurusan'),
                'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('jurusan'),
            ])>
            <option value="">Belum ditentukan</option>
            @foreach ($jurusan as $item)
                <option value="{{ $item->id }}"
                    @selected((string) old('jurusan', $kelas?->jurusan_id) === (string) $item->id)>
                    {{ $item->nama_jurusan }} ({{ $item->kode_jurusan }})
                </option>
            @endforeach
        </select>
    @endif

    @error('jurusan')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>