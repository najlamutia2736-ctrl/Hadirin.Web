<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Absensi')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <!-- Navbar -->
    <x-navbar />

    <!-- Konten Halaman -->
    <main class="container mx-auto px-4 py-8 bg-white-900">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white shadow-lg mt-8">
        <div class="container mx-auto px-4 py-4 text-center text-gray-600">
            &copy; 2026 Sistem Absensi
        </div>
    </footer>
</body>
</html>