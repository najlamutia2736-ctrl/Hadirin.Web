<!DOCTYPE html>
<html lang="id">
@php
    // Menu & kartu "Absen Siswa" hanya untuk akun yang punya profil di tabel
    // `siswas`. Pemeriksaannya sama dengan AbsensiSiswaController.
    $bisaAbsen = auth()->user()?->siswa !== null;

    $user = auth()->user();

    /*
    | Kartu peran "Siswa" sengaja selalu tampil, bukan ikut disembunyikan
    | bersama menunya. Kartu ini adalah pintu masuk untuk memilih peran,
    | jadi orang yang belum login justru yang paling butuh melihatnya.
    | Yang menyesuaikan hanya tujuan kliknya, supaya halaman absensi tidak
    | pernah bocor ke akun yang tidak berhak (lihat `MenuAbsensiTest`).
    */
    $tujuanSiswa = $bisaAbsen
        ? route('absensi.index')
        : ($user === null ? route('login') : route('guru.dashboard'));

    $labelSiswa = match (true) {
        $bisaAbsen => 'Absen Sekarang',
        $user === null => 'Masuk sebagai Siswa',
        default => 'Bukan Akun Siswa',
    };
@endphp
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hadirin.web · Beranda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">

    <!-- ========== NAVBAR ========== -->
    <header class="w-full bg-white/80 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center justify-between h-16 md:h-20">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-indigo-700 tracking-tight">
                        Hadirin.<span class="text-slate-700">web</span>
                    </a>
                    <span class="hidden sm:inline-block text-[10px] font-medium bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full">beta</span>
                </div>

<!-- Menu Desktop -->
<div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600">
    <a href="{{ route('beranda') }}" class="hover:text-indigo-600 transition text-indigo-600 font-semibold">Beranda</a>
    @if ($bisaAbsen)
        <a href="{{ route('absensi.index') }}" class="hover:text-indigo-600 transition">Absen Siswa</a>
    @endif
    <a href="{{ route('guru.dashboard') }}" class="hover:text-indigo-600 transition">Dashboard Guru</a>
    <a href="{{ route('cms.dashboard') }}" class="hover:text-indigo-600 transition">Dashboard Admin</a>
    <a href="{{ route('cms.rekap') }}" class="hover:text-indigo-600 transition">Rekap</a>
</div>

                <!-- Tombol User (DINAMIS - NAMA DARI EMAIL) -->
                <div class="hidden md:block">
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-600 flex items-center gap-2">
                            <i class="fas fa-user-circle text-indigo-600 text-lg"></i>
                            <span id="userNavName">Najla Mutia</span>
                        </span>
                        <a href="{{ route('login') }}" class="text-sm text-red-500 hover:text-red-700 transition">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>

<!-- Mobile Menu -->
<div class="md:hidden flex items-center gap-3">
    <span class="text-sm font-medium text-slate-600 flex items-center gap-1">
        <i class="fas fa-user-circle text-indigo-600"></i>
        <span id="userNavNameMobile">Najla</span>
    </span>
    <button onclick="toggleMobileMenu()" class="text-slate-500 hover:text-indigo-600 transition">
        <i class="fas fa-bars text-xl" id="mobileMenuIcon"></i>
    </button>
</div>
            </nav>
            <!-- Mobile Dropdown Menu -->
<div id="mobileMenu" class="hidden md:hidden border-t border-slate-200/60 bg-white/95 backdrop-blur-sm">
    <div class="px-4 py-3 space-y-1">
        <a href="{{ route('beranda') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-indigo-600 bg-indigo-50">
            <i class="fas fa-home w-5"></i> Beranda
        </a>
        @if ($bisaAbsen)
            <a href="{{ route('absensi.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50">
                <i class="fas fa-user-graduate w-5"></i> Absen Siswa
            </a>
        @endif
        <a href="{{ route('guru.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50">
            <i class="fas fa-chalkboard-teacher w-5"></i> Dashboard Guru
        </a>
        <a href="{{ route('cms.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50">
            <i class="fas fa-user-shield w-5"></i> Dashboard Admin
        </a>
        <a href="{{ route('cms.rekap') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50">
            <i class="fas fa-file-alt w-5"></i> Rekap
        </a>
        <div class="border-t border-slate-200/60 my-2"></div>
        <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-red-500 hover:bg-red-50">
            <i class="fas fa-sign-out-alt w-5"></i> Logout
        </a>
    </div>
</div>
        </div>
    </header>

    <!-- ========== HERO / BANNER ========== -->
    <section class="gradient-bg text-white py-16 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-block bg-white/20 backdrop-blur-sm px-4 py-1.5 rounded-full text-xs font-semibold mb-4">
                    <i class="fas fa-star mr-1"></i> Selamat Datang di Platform Absensi Digital
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-4 tracking-tight">
                    ABSENSI SEKOLAH <span class="text-yellow-300">DIGITAL</span>
                </h1>
                <p class="text-lg md:text-xl text-white/90 max-w-3xl mx-auto">
                    Satu kartu, dua cara hadir: <span class="font-semibold text-yellow-200">scan</span> atau <span class="font-semibold text-yellow-200">ketik</span>.
                </p>
            </div>
        </div>
    </section>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Deskripsi -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6 md:p-8 mb-12">
            <div class="flex items-start gap-4">
                <div class="hidden sm:block text-4xl text-indigo-500">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 mb-2">Apa itu Hadirin?</h2>
                    <p class="text-sm text-slate-600 leading-relaxed max-w-4xl">
                        Hadirin mengganti buku absensi kertas dengan pencatatan otomatis dan terpusat. 
                        Siswa cukup memindai kode QR atau memasukkan ID unik miliknya. 
                        <span class="text-indigo-600 font-medium">— guru dan wali murid langsung melihat status kehadiran secara real-time.</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Pilih Peran -->
        <div class="mb-8">
            <h3 class="text-2xl font-bold text-slate-800 text-center mb-2">Pilihlah Peranmu Disisni</h3>
            <p class="text-sm text-slate-500 text-center mb-8">Tentukan cara masuk sesuai peranmu di sekolah!</p>
        </div>

        <!-- Cards Peran -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Card Siswa -->
            {{-- Kartu ini tidak pernah disembunyikan: pilih peran harus bisa
                 dilakukan sebelum login. Yang dijaga hanya link tujuan, agar
                 URL halaman absensi tidak bocor ke akun non-siswa. --}}
            <div class="bg-white rounded-2xl shadow-md border border-slate-200/60 p-6 text-center card-hover transition-all duration-300">
                <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-graduate text-3xl text-indigo-600"></i>
                </div>
                <h4 class="text-xl font-bold text-slate-800 mb-2">Siswa</h4>
                <p class="text-sm text-slate-500 mb-4">Absen mandiri lewat scan QRCode atau kode unik.</p>

                @if ($bisaAbsen)
                    <a href="{{ route('absensi.index') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-xl transition shadow-md shadow-indigo-200/60">
                        <i class="fas fa-arrow-right mr-1"></i> {{ $labelSiswa }}
                    </a>
                @elseif ($user === null)
                    {{-- Belum login: arahkan ke login, bukan ke halaman absensi,
                         supaya middleware tetap yang menjaga halamannya. --}}
                    <a href="{{ route('login') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-xl transition shadow-md shadow-indigo-200/60">
                        <i class="fas fa-right-to-bracket mr-1"></i> {{ $labelSiswa }}
                    </a>
                @else
                    {{-- Sudah login tapi bukan siswa: jelaskan, jangan paksa. --}}
                    <span class="inline-block cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-6 py-2.5 font-medium text-slate-400" title="Gunakan akun siswa untuk absen.">
                        <i class="fas fa-lock mr-1"></i> {{ $labelSiswa }}
                    </span>
                @endif
            </div>

            <!-- Card Guru / Wali Kelas -->
            <div class="bg-white rounded-2xl shadow-md border border-slate-200/60 p-6 text-center card-hover transition-all duration-300 md:scale-105 md:shadow-lg">
                <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chalkboard-teacher text-3xl text-emerald-600"></i>
                </div>
                <h4 class="text-xl font-bold text-slate-800 mb-2">Guru / Wali Kelas</h4>
                <p class="text-sm text-slate-500 mb-4">Memantau kehadiran Real-Time dan unduh rekap bulanan.</p>
                <a href="{{ route('guru.dashboard') }}" class="inline-block bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-6 py-2.5 rounded-xl transition shadow-md shadow-emerald-200/60">
                    <i class="fas fa-arrow-right mr-1"></i> Klik
                </a>
            </div>

            <!-- Card Admin / Kepsek -->
            <div class="bg-white rounded-2xl shadow-md border border-slate-200/60 p-6 text-center card-hover transition-all duration-300">
                <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-shield text-3xl text-purple-600"></i>
                </div>
                <h4 class="text-xl font-bold text-slate-800 mb-2">Admin / Kepsek</h4>
                <p class="text-sm text-slate-500 mb-4">Kelola data seluruh siswa dan lihat laporan menyeluruh.</p>
                <a href="{{ route('cms.dashboard') }}" class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-medium px-6 py-2.5 rounded-xl transition shadow-md shadow-purple-200/60">
                    <i class="fas fa-arrow-right mr-1"></i> Klik
                </a>
            </div>
        </div>

        <!-- Statistik Cepat -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 text-center">
                <p class="text-2xl font-bold text-indigo-600">1,234</p>
                <p class="text-xs text-slate-500">Siswa Terdaftar</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 text-center">
                <p class="text-2xl font-bold text-emerald-600">96%</p>
                <p class="text-xs text-slate-500">Tingkat Kehadiran</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 text-center">
                <p class="text-2xl font-bold text-purple-600">48</p>
                <p class="text-xs text-slate-500">Guru Aktif</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 text-center">
                <p class="text-2xl font-bold text-amber-600">12</p>
                <p class="text-xs text-slate-500">Kelas</p>
            </div>
        </div>

    </main>

    <!-- ========== FOOTER ========== -->
    <footer class="w-full border-t border-slate-200/60 py-6 mt-8 text-center text-xs text-slate-400 bg-white/40 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 flex flex-wrap items-center justify-center gap-3">
            <span>© 2026 Sistem Absensi</span>
            <span class="w-px h-3 bg-slate-300"></span>
            <span>Hadirin.web</span>
            <span class="w-px h-3 bg-slate-300"></span>
            <span><i class="fas fa-image mr-1"></i> Beranda.png</span>
        </div>
    </footer>

    <!-- ========== JAVASCRIPT: UPDATE NAMA DARI EMAIL ========== -->
    <script>
 // ============================================================
// MOBILE MENU TOGGLE
// ============================================================
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const icon = document.getElementById('mobileMenuIcon');
    if (!menu) return;
    
    menu.classList.toggle('hidden');
    if (icon) {
        if (menu.classList.contains('hidden')) {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        } else {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
        }
    }
}

// ============================================================
// INIT - Update nama dari localStorage
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const nama = localStorage.getItem('user_nama');
    const email = localStorage.getItem('user_email');
    
    console.log('📧 Email dari storage:', email);
    console.log('👤 Nama dari storage:', nama);
    
    if (nama) {
        // Update nama di navbar desktop
        const navName = document.getElementById('userNavName');
        if (navName) {
            navName.textContent = nama;
            console.log('✅ Navbar desktop diupdate:', nama);
        }
        
        // Update nama di navbar mobile (hanya nama depan)
        const navNameMobile = document.getElementById('userNavNameMobile');
        if (navNameMobile) {
            navNameMobile.textContent = nama.split(' ')[0];
            console.log('✅ Navbar mobile diupdate:', nama.split(' ')[0]);
        }
    } else {
        console.warn('⚠️ Nama tidak ditemukan di localStorage. Silakan login dulu.');
    }
});
    </script>

</body>
</html>