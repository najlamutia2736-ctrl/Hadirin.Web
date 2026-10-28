{{--
    Field "Kode Mata Kuliah", "Nama Mata Kuliah", dan "Dosen Pengajar" untuk form
    Mata Kuliah.

    Dipakai lewat:
        @include('cms.mata-kuliah.partials.form', ['mataKuliah' => null])   // tambah
        @include('cms.mata-kuliah.partials.form', ['mataKuliah' => $mataKuliah]) // ubah

    Nama field di form sengaja memakai bentuk pendek (`code`, `name`, `dosen`)
    supaya sama dengan field lain di CMS ini. `MataKuliahController` yang
    memetakannya ke kolom `kode_mata_kuliah`, `nama_mata_kuliah`, dan
    `dosens.mata_kuliah_id`. Daftar `$dosens` dikirim dari controller; kalau
    kosong, pilihannya diganti pemberitahuan karena belum ada dosen yang bisa
    ditugaskan.
--}}

<div class="grid gap-5 p-6 sm:grid-cols-2">
    <div>
        <label for="code" class="mb-2 block text-sm font-semibold text-gray-700">
            Kode Mata Kuliah <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <input id="code" name="code" type="text" value="{{ old('code', $mataKuliah?->kode_mata_kuliah) }}" required
            maxlength="10" placeholder="Contoh: MTK" autocapitalize="characters"
            aria-invalid="{{ $errors->has('code') ? 'true' : 'false' }}"
            @class([
                'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition placeholder-gray-400 focus:outline-none focus:ring-2',
                'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('code'),
                'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('code'),
            ])>
        <p class="mt-1.5 text-xs text-gray-500">
            Dipakai sebagai kode singkat. Huruf besar dan spasi otomatis dibersihkan.
        </p>
        @error('code')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="mb-2 block text-sm font-semibold text-gray-700">
            Nama Mata Kuliah <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <input id="name" name="name" type="text" value="{{ old('name', $mataKuliah?->nama_mata_kuliah) }}" required
            maxlength="100" placeholder="Contoh: Matematika"
            aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
            @class([
                'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition placeholder-gray-400 focus:outline-none focus:ring-2',
                'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('name'),
                'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('name'),
            ])>
        @error('name')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="dosen" class="mb-2 block text-sm font-semibold text-gray-700">
            Dosen Pengajar
            @if ($dosens->isNotEmpty())
                <span class="text-red-500" aria-hidden="true">*</span>
            @endif
        </label>

        @if ($dosens->isEmpty())
            <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm text-amber-800">
                Belum ada data dosen. Tambahkan dosen terlebih dahulu di menu
                <a href="{{ route('cms.dosens.create') }}" class="font-semibold underline">Dosen</a>
                supaya setiap mata kuliah punya dosen pengajar.
            </p>
        @else
            <select id="dosen" name="dosen"
                aria-invalid="{{ $errors->has('dosen') ? 'true' : 'false' }}"
                @class([
                    'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2',
                    'border-red-500 bg-red-50/40 focus:border-red-500 focus:ring-red-500' => $errors->has('dosen'),
                    'border-gray-200 bg-white focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('dosen'),
                ])>
                <option value="">Belum ditentukan</option>
                @foreach ($dosens as $dosen)
                    <option value="{{ $dosen->id }}"
                        @selected((string) old('dosen', $mataKuliah?->dosens->first()?->id) === (string) $dosen->id)>
                        {{ $dosen->user?->name ?? 'Dosen tanpa akun' }} ({{ $dosen->namaMataKuliah() ?? 'belum ada mata kuliah' }})
                    </option>
                @endforeach
            </select>

            <p class="mt-1.5 text-xs text-gray-500">
                Memilih dosen akan memindahkannya dari mata kuliahnya yang sekarang.
            </p>
        @endif

        @error('dosen')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
