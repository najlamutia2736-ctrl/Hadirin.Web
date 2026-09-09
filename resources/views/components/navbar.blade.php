    <!-- ---------- NAVBAR ---------- -->
    <nav class="flex items-center justify-between flex-wrap bg-white/80 backdrop-blur-sm p-4 rounded-2xl shadow-sm border border-slate-200/60">
      <!-- Brand / Logo -->
      <div class="flex items-center gap-2">
        <span class="text-2xl font-bold text-indigo-700 tracking-tight">Hadirin.<span class="text-slate-700">web</span></span>
        <span class="hidden sm:inline-block text-xs font-medium bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full">beta</span>
      </div>

      <!-- Menu tengah (desktop) -->
      <div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600">
        <a href="#" class="hover:text-indigo-600 transition">Beranda</a>
        <a href="#" class="hover:text-indigo-600 transition">Absen Siswa</a>
        <a href="#" class="hover:text-indigo-600 transition">Dashboard Guru</a>
        <a href="#" class="hover:text-indigo-600 transition">Rekap</a>
      </div>

      <!-- Tombol Login (desktop) -->
      <div class="hidden md:block">
        <a href="#" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-full shadow-md shadow-indigo-200 transition">
          <i class="fas fa-arrow-right-to-bracket text-xs"></i> Log In
        </a>
      </div>

      <!-- Mobile hamburger (hanya ikon, tanpa dropdown agar tetap sederhana) -->
      <div class="md:hidden flex items-center gap-3">
        <a href="#" class="text-sm font-medium text-indigo-600 bg-indigo-50 px-4 py-2 rounded-full">Log In</a>
        <button class="text-slate-500 hover:text-indigo-600 transition">
          <i class="fas fa-bars text-xl"></i>
        </button>
      </div>
    </nav>
