<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Dipakai request fetch() dari halaman, mis. pada halaman Kelola Data Kelas --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hadirin.Web')</title>
    <!-- Tailwind via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome untuk ikon (opsional, biar lebih hidup) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet">
    <style>
        /* small custom transition */
        .sidebar-link {
            transition: background 0.2s ease, color 0.2s ease;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-50 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        <!-- ========== SIDEBAR ========== -->
        @include('components.sidebar')

        <!-- ========== KONTEN UTAMA ========== -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- topbar -->
            @include('components.header')

            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">

                <!-- content -->
                @yield('konten')

                <!-- footer kecil (opsional) -->
                @include('components.footer')
            </main>

        </div>
    </div>

    @stack('scripts')
</body>

</html>
