{{--
    Field "Mata Pelajaran" untuk form tambah & ubah guru.

    Dipakai lewat:
        @include('cms.teachers.partials.mata-pelajaran', ['guru' => null])   // tambah
        @include('cms.teachers.partials.mata-pelajaran', ['guru' => $guru])   // ubah

    Pilihannya berasal dari tabel `mata_pelajaran`, sama seperti field mapel di
    halaman Classes. Jadi mapel yang dipakai di form ini persis baris yang
    sama dengan yang muncul di Subjects dan di dashboard guru.
--}}

<div>
    <label for="subject" class="mb-2 block text-sm font-semibold text-gray-700">
        Mata Pelajaran <span class="text-red-500" aria-hidden="true">*</span>
    </label>

    @if ($mataPelajaran->isEmpty())
        <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm text-amber-800">
            Belum ada data mata pelajaran. Tambahkan mata pelajaran terlebih dahulu di menu
            <a href="{{ route('cms.mata-pelajaran') }}" class="font-semibold underline">Mata Pelajaran</a>
            sebelum menentukan mata pelajaran guru.
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
            @foreach ($mataPelajaran as $item)
                <option value="{{ $item->id }}"
                    @selected((string) old('subject', $guru?->mata_pelajaran_id) === (string) $item->id)>
                    {{ $item->nama_mata_pelajaran }} ({{ $item->kode_mata_pelajaran }})
                </option>
            @endforeach
        </select>
    @endif

    @error('subject')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>