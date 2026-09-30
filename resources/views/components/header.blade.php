@php
    // Judul & breadcrumb bisa dikirim halaman lewat @section('title') / @section('breadcrumb'),
    // atau diset langsung: @php($pageTitle = 'Kelola Data Kelas')
    $pageTitle = $pageTitle ?? 'Dashboard';
    $breadcrumbItems = $breadcrumbItems ?? [['label' => $pageTitle, 'current' => true]];
@endphp

<header class="h-16 shrink-0 bg-white border-b border-gray-200 flex items-center justify-between px-6 shadow-sm">
    <div class="min-w-0">
        @if (! empty($breadcrumbItems))
            <nav class="flex items-center gap-1 text-xs text-gray-400">
                @foreach ($breadcrumbItems as $crumb)
                    @if (! $loop->first)
                        <i class="fas fa-chevron-right text-[8px]"></i>
                    @endif
                    <span @class([
                        'truncate' => $loop->last,
                        'font-medium text-indigo-600' => ($crumb['current'] ?? false),
                    ])>{{ $crumb['label'] }}</span>
                @endforeach
            </nav>
        @endif

        <h1 class="mt-0.5 truncate text-xl font-semibold text-gray-800">{{ $pageTitle }}</h1>
    </div>

    <div class="flex shrink-0 items-center space-x-4">
        @isset($headerActions)
            {{ $headerActions }}
        @endisset

        <button type="button" class="text-gray-500 hover:text-gray-700 focus:outline-none">
            <i class="fas fa-bell text-lg"></i>
        </button>
        <button type="button" class="text-gray-500 hover:text-gray-700 focus:outline-none">
            <i class="fas fa-envelope text-lg"></i>
        </button>

        <div
            class="grid h-8 w-8 place-items-center rounded-full bg-indigo-100 font-semibold text-sm text-indigo-700"
            id="headerInitial">{{ $menuUser['initial'] ?? 'AD' }}</div>
    </div>
</header>
