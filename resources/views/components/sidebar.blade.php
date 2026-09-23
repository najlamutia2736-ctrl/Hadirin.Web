@php
    $menuGroups = [
        [
            'label' => 'Menu Utama',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'cms.dashboard', 'icon' => 'fas fa-tachometer-alt'],
            ],
        ],
        [
            'label' => 'Manajemen',
            'items' => [
                ['label' => 'Students', 'route' => 'cms.students', 'icon' => 'fas fa-user-graduate'],
                ['label' => 'Teachers', 'route' => 'cms.teachers', 'icon' => 'fas fa-chalkboard-teacher'],
                ['label' => 'Classes', 'route' => 'cms.classes', 'icon' => 'fas fa-book-open'],
            ],
        ],
        [
            'label' => 'Sistem',
            'items' => [
                ['label' => 'Users', 'route' => 'cms.users', 'icon' => 'fas fa-users-cog'],
                ['label' => 'Rekap', 'route' => 'cms.rekap', 'icon' => 'fas fa-clipboard-list'],
            ],
        ],
    ];
@endphp

<aside class="sidebar-nav w-64 shrink-0 flex flex-col border-r border-gray-200 bg-gradient-to-b from-white via-white to-gray-50 shadow-sm">
    <!-- brand / logo -->
    <div class="relative h-16 shrink-0 flex items-center gap-3 overflow-hidden border-b border-gray-200 px-6">
        <span class="pointer-events-none absolute -right-8 -top-10 h-28 w-28 rounded-full bg-indigo-100/60 blur-2xl"></span>
        <div
            class="relative grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/30">
            <i class="fas fa-book text-lg"></i>
        </div>
        <div class="relative leading-tight">
            <p class="text-base font-semibold tracking-tight text-gray-800">Hadirin.Web</p>
            <p class="text-[11px] font-medium text-indigo-500">School Management</p>
        </div>
    </div>

    <!-- menu navigasi -->
    <nav class="sidebar-nav-scroll flex-1 overflow-y-auto px-3 py-2">
        @foreach ($menuGroups as $group)
            <p class="px-3 pt-5 pb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                {{ $group['label'] }}
            </p>

            <div class="space-y-1">
                @foreach ($group['items'] as $item)
                    @php $isActive = request()->routeIs($item['route']); @endphp

                    <a href="{{ route($item['route']) }}"
                        @class([
                            'sidebar-link group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200',
                            'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-md shadow-indigo-500/30' => $isActive,
                            'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => ! $isActive,
                        ])>
                        @if ($isActive)
                            <span class="absolute -left-3 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r-full bg-indigo-600"></span>
                        @endif

                        <span
                            @class([
                                'grid h-8 w-8 shrink-0 place-items-center rounded-lg transition-colors duration-200',
                                'bg-white/20 text-white' => $isActive,
                                'bg-gray-100 text-gray-500 group-hover:bg-indigo-100 group-hover:text-indigo-600' => ! $isActive,
                            ])>
                            <i class="{{ $item['icon'] }} text-sm"></i>
                        </span>

                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    <!-- footer sidebar (user info) -->
    <div class="shrink-0 border-t border-gray-200 bg-white/70 p-4">
        <div class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 ring-1 ring-gray-100">
            <div class="relative shrink-0">
                <div
                    class="grid h-9 w-9 place-items-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-sm font-semibold text-white">
                    AD
                </div>
                <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-gray-50"></span>
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-gray-800">Admin</p>
                <p class="truncate text-xs text-gray-500">admin@sekolah.id</p>
            </div>
            <i class="fas fa-ellipsis-v shrink-0 text-xs text-gray-400"></i>
        </div>
    </div>
</aside>

<style>
    .sidebar-nav-scroll {
        scrollbar-width: thin;
        scrollbar-color: #c7d2fe transparent;
    }

    .sidebar-nav-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-nav-scroll::-webkit-scrollbar-thumb {
        background-color: #c7d2fe;
        border-radius: 9999px;
    }

    .sidebar-nav-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
</style>
