{{--
    Field "Kode Mata Pelajaran" dan "Nama Mata Pelajaran" untuk form Mata Pelajaran.

    Dipakai lewat:
        @include('cms.mata-pelajaran.partials.form', ['mataPelajaran' => null])   // tambah
        @include('cms.mata-pelajaran.partials.form', ['mataPelajaran' => $mataPelajaran]) // ubah

    Nama field di form sengaja memakai bentuk pendek (`code`, `name`) supaya
    sama dengan field lain di CMS ini. `MataPelajaranController` yang memetakannya ke
    kolom `kode_mata_pelajaran` dan `nama_mata_pelajaran`.
--}}

<div class="grid gap-5 p-6 sm:grid-cols-2">
    <div>
        <label for="code" class="mb-2 block text-sm font-semibold text-gray-700">
            Kode Mata Pelajaran <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <input id="code" name="code" type="text" value="{{ old('code', $mataPelajaran?->kode_mata_pelajaran) }}" required
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
            Nama Mata Pelajaran <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <input id="name" name="name" type="text" value="{{ old('name', $mataPelajaran?->nama_mata_pelajaran) }}" required
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
</div>
