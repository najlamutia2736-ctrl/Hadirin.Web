{{--
    Tombol aksi pada banner halaman Guru.

    Banner (`guru.partials.banner`) hanya menerima string HTML lewat variabel
    `$actions`, jadi file ini dirender lebih dulu lalu dikirim sebagai
    HtmlString supaya tidak di-escape.

    Dua tombol yang pernah ada di sini sudah dihapus:

    - "Buka QR Absen" menunjuk ke halaman real-time monitoring, yang sekarang
      sudah jadi bagian dari dashboard guru sehingga tidak ada lagi tujuan
      terpisah untuk ditautkan.
    - "Mulai Sesi Absen" belum pernah punya aksi apa pun, hanya menampilkan
      `alert()` yang menyatakan belum diimplementasikan. Sesi absensi sendiri
      sekarang dibuat otomatis oleh `php artisan sesi:absensi` setiap hari,
      jadi tombol seperti ini sudah tidak berguna.
--}}
<a href="{{ route('guru.laporan') }}"
    class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 shadow-sm transition-all hover:bg-indigo-50 hover:shadow-md">
    <i class="fas fa-file-alt"></i>
    Lihat Laporan
</a>
