{{-- Banner sapaan. Halaman memakainya lewat @include('guru.partials.banner', ['subtitle' => '...']) --}}
@php
    $subtitle = $subtitle ?? 'Ringkasan kelas hari ini';
@endphp

<div
    class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600 p-6 shadow-lg shadow-indigo-500/20">
    <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10"></div>
    <div class="pointer-events-none absolute -bottom-24 right-40 h-56 w-56 rounded-full bg-white/5"></div>
    <div class="pointer-events-none absolute right-56 top-8 h-16 w-16 rotate-12 rounded-2xl bg-white/10"></div>

    <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium text-indigo-100">{{ $subtitle }}</p>
            <h2 class="mt-1 text-2xl font-bold text-white">
                Halo, <span data-guru-nama>-</span> 👋
            </h2>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white ring-1 ring-white/20">
                    <i class="fas fa-calendar-day"></i>
                    <span id="currentDate">-</span>
                </span>
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white ring-1 ring-white/20">
                    <i class="fas fa-chalkboard"></i>
                    Kelas <span data-guru-kelas>-</span>
                </span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{ $actions ?? '' }}
        </div>
    </div>
</div>

<script>
    // Tanggal hari ini pada banner
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('currentDate');
        if (!el) return;

        el.textContent = new Date().toLocaleDateString('id-ID', {
            weekday: 'long',
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });
    });
</script>
