<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HADIRIN · Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">

    <!-- ========== NAVBAR ========== -->
    @include('layouts.navbar')

    <!-- ========== MAIN: FORM LOGIN ========== -->
    <main class="flex-1 flex items-center justify-center px-4 py-8 sm:py-12">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-3xl shadow-xl shadow-indigo-100/40 border border-slate-200/60 p-6 sm:p-8">
                
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">
                        <span class="text-indigo-600">HADIRIN</span>
                    </h1>
                    <p class="text-sm text-slate-500 mt-1 font-medium tracking-wide">Absensi Siswa Harian</p>
                </div>

                <!-- Selamat Datang -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-slate-800">Selamat Datang!</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Silahkan Daftarkan Emailmu untuk memulai</p>
                </div>

                <!-- Form Login -->
                <form action="{{ route('login2') }}" method="GET" class="space-y-5">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
                            <i class="fas fa-envelope text-indigo-400 mr-1.5"></i> Email
                        </label>
                        <div class="relative">
                            <input type="email" id="email" name="email" placeholder="Type here" required
                                   class="w-full pl-4 pr-10 py-3 bg-slate-50/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition" />
                            <span class="absolute right-3 top-3.5 text-slate-300 text-sm"><i class="fas fa-envelope"></i></span>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                            <i class="fas fa-lock text-indigo-400 mr-1.5"></i> Password
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" placeholder="Type here" required
                                   class="w-full pl-4 pr-10 py-3 bg-slate-50/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition" />
                            <span class="absolute right-3 top-3.5 text-slate-300 text-sm"><i class="fas fa-lock"></i></span>
                        </div>
                    </div>

                    <!-- Tombol Log in -->
                    <button type="submit"
                            class="w-full mt-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3.5 rounded-xl shadow-md shadow-indigo-200/60 transition flex items-center justify-center gap-2 text-base">
                        <i class="fas fa-sign-in-alt"></i> Log in
                    </button>
                </form>

                <p class="text-xs text-slate-400 text-center mt-6">
                    <i class="far fa-circle-check text-indigo-300 mr-1"></i> aman & terenkripsi
                </p>
            </div>

            <div class="text-center mt-5 text-xs text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-indigo-500 transition"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Beranda</a>
            </div>
        </div>
    </main>

    <!-- ========== FOOTER ========== -->
    <footer class="w-full border-t border-slate-200/60 py-4 text-center text-[10px] text-slate-400 bg-white/40 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 flex flex-wrap items-center justify-center gap-3">
            <span>© 2026 Hadirin.web</span>
            <span class="w-px h-3 bg-slate-300"></span>
            <span>Absensi Siswa Harian</span>
        </div>
    </footer>

</body>
</html>