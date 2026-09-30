{{-- Style khusus halaman Guru. Dipakai lewat @push('styles') di layouts/app.blade.php --}}
<style>
    /* kartu statistik */
    .stat-card {
        border-radius: 12px;
        padding: 20px;
        color: white;
    }

    .stat-blue {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    }

    .stat-green {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .stat-orange {
        background: linear-gradient(135deg, #f97316, #ea580c);
    }

    .stat-red {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    /* chart */
    .chart-container {
        position: relative;
        height: 280px;
    }

    /* badge absensi */
    .badge-hadir {
        background: #dcfce7;
        color: #166534;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
    }

    .badge-izin {
        background: #fef9c3;
        color: #854d0e;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
    }

    .badge-sakit {
        background: #fce4ec;
        color: #b91c1c;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
    }

    .badge-alpha {
        background: #fee2e2;
        color: #991b1b;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
    }

    /* badge metode */
    .badge-scan {
        background: #e0e7ff;
        color: #4338ca;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
    }

    .badge-id {
        background: #d1fae5;
        color: #065f46;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
    }

    .badge-izin-metode {
        background: #fef3c7;
        color: #92400e;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
    }

    /* progress bar */
    .progress-bar {
        height: 8px;
        border-radius: 4px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: 4px;
    }

    /* toast real-time */
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(24px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
</style>
