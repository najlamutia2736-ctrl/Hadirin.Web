{{--
    Barcode QR milik satu mahasiswa, berisikan NIM-nya.

    Dipakai di halaman identitas supaya mahasiswa bisa menunjukkan barcode ini
    kepada petugas. Halaman CMS memakai wadah yang sama lewat JavaScript
    (`data-barcode-*`) supaya satu modal bisa menampilkan barcode mahasiswa
    mana pun tanpa merender puluhan gambar sekaligus.

    Pilihan yang didukung:
      - :mahasiswa  model Mahasiswa yang barcode-nya digambar (wajib)
      - :ukuran     sisi QR dalam piksel, default 240
--}}
@props(['mahasiswa', 'ukuran' => 240])

@include('components.qr-script')

<div {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-xl bg-white p-3 ring-1 ring-slate-200']) }}
    data-barcode-mahasiswa
    data-barcode-teks="{{ $mahasiswa->nim }}"
    data-barcode-ukuran="{{ $ukuran }}"></div>
