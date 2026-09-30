{{--
    Tombol aksi pada banner halaman Guru.

    Banner (`guru.partials.banner`) hanya menerima string HTML lewat variabel
    `$actions`, jadi file ini dirender lebih dulu lalu dikirim sebagai
    HtmlString supaya tidak di-escape.
--}}
<a href="{{ route('guru.realtime') }}"
    class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 shadow-sm transition-all hover:bg-indigo-50 hover:shadow-md">
    <i class="fas fa-qrcode"></i>
    Buka QR Absen
</a>

<button type="button" id="tombolMulaiSesi"
    class="inline-flex items-center gap-2 rounded-lg bg-white/15 px-4 py-2.5 text-sm font-semibold text-white ring-1 ring-white/20 transition-colors hover:bg-white/25">
    <i class="fas fa-circle-play"></i>
    <span id="labelMulaiSesi">Mulai Sesi Absen</span>
</button>
