@extends('layouts.app')

@section('konten')
    <div>
        <!-- sapaan & ringkasan -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Selamat datang, Admin 👋</h2>
            <p class="text-gray-600 mt-1">Ringkasan data sekolah hari ini.</p>
        </div>

        <!-- kartu statistik (4 kolom) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <!-- Students -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 flex items-center">
                <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 text-xl">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Students</p>
                    <p class="text-2xl font-bold text-gray-800">1,248</p>
                </div>
            </div>
            <!-- Teachers -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 flex items-center">
                <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-green-600 text-xl">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Teachers</p>
                    <p class="text-2xl font-bold text-gray-800">86</p>
                </div>
            </div>
            <!-- Classes -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 flex items-center">
                <div class="w-12 h-12 rounded-full bg-purple-50 flex items-center justify-center text-purple-600 text-xl">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Classes</p>
                    <p class="text-2xl font-bold text-gray-800">32</p>
                </div>
            </div>
            <!-- Users -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 flex items-center">
                <div class="w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center text-amber-600 text-xl">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Users</p>
                    <p class="text-2xl font-bold text-gray-800">154</p>
                </div>
            </div>
        </div>

        <!-- konten tambahan: aktivitas terbaru / tabel ringkas -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Aktivitas terbaru</h3>
                <a href="#" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Lihat
                    semua</a>
            </div>
            <div class="divide-y divide-gray-100">
                <!-- item 1 -->
                <div class="flex items-center px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-sm">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-800">Siswa baru terdaftar</p>
                        <p class="text-xs text-gray-500">Rina Wijaya · 10 menit lalu</p>
                    </div>
                    <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">Baru</span>
                </div>
                <!-- item 2 -->
                <div class="flex items-center px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-sm">
                        <i class="fas fa-chalkboard"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-800">Jadwal kelas diperbarui</p>
                        <p class="text-xs text-gray-500">Kelas 10A · 1 jam lalu</p>
                    </div>
                </div>
                <!-- item 3 -->
                <div class="flex items-center px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div
                        class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-sm">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-800">Akun pengguna baru</p>
                        <p class="text-xs text-gray-500">Operator sekolah · 3 jam lalu</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
