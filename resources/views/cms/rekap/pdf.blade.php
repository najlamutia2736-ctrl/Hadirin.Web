<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Kehadiran · {{ $periode['label'] }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 15mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background: #f3f4f6;
            color: #111827;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }

        .lembar {
            max-width: 210mm;
            margin: 0 auto;
            padding: 18mm 15mm;
            background: #ffffff;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .08);
        }

        .kop {
            text-align: center;
            border-bottom: 3px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .kop h1 {
            margin: 0;
            font-size: 18px;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .kop p {
            margin: 2px 0 0;
            font-size: 12px;
            color: #4b5563;
        }

        h2.judul {
            margin: 0 0 4px;
            font-size: 14px;
        }

        .meta {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 20px;
            margin-bottom: 14px;
            font-size: 11px;
            color: #374151;
        }

        .meta span b {
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #9ca3af;
            padding: 6px 8px;
        }

        th {
            background: #f3f4f6;
            font-size: 10.5px;
            letter-spacing: .3px;
            text-transform: uppercase;
            text-align: center;
        }

        td {
            text-align: center;
        }

        td.kelas,
        th.kelas {
            text-align: left;
        }

        tfoot td {
            background: #f9fafb;
            font-weight: 700;
        }

        .kosong {
            padding: 18px;
            text-align: center;
            color: #6b7280;
        }

        .ttd {
            display: flex;
            justify-content: flex-end;
            margin-top: 28px;
            page-break-inside: avoid;
        }

        .ttd div {
            width: 60mm;
            text-align: center;
            font-size: 11px;
        }

        .ttd .ruang {
            height: 64px;
        }

        .toolbar {
            max-width: 210mm;
            margin: 0 auto 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .toolbar a,
        .toolbar button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #ffffff;
            color: #1f2937;
            font-size: 12px;
            font-family: inherit;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar .primary {
            background: #4f46e5;
            border-color: #4f46e5;
            color: #ffffff;
        }

        .toolbar .info {
            margin-left: auto;
            color: #6b7280;
            font-size: 11px;
        }

        @media print {
            body {
                padding: 0;
                background: #ffffff;
            }

            .toolbar {
                display: none;
            }

            .lembar {
                max-width: none;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <button type="button" class="primary" onclick="window.print()">
            <i class="fas fa-print"></i>
            Cetak / Simpan PDF
        </button>
        <a href="{{ route('cms.rekap', request()->except('page')) }}">Kembali ke Rekap</a>
        <a href="{{ route('cms.rekap.export.excel', request()->except('page')) }}">Export Excel</a>
        <span class="info">Gunakan "Save as PDF" pada dialog cetak browser untuk menyimpan berkas PDF.</span>
    </div>

    <div class="lembar">
        <div class="kop">
            <h1>{{ config('app.name') }}</h1>
            <p>Laporan Rekap Kehadiran Siswa</p>
        </div>

        <h2 class="judul">Rekap Kehadiran per Kelas</h2>

        <div class="meta">
            <span><b>Periode:</b> {{ $periode['label'] }}</span>
            <span><b>Kelas:</b> {{ $periode['kelas'] ?? 'Semua Kelas' }}</span>
            <span><b>Dicetak:</b> {{ $periode['dicetak'] }}</span>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="kelas" style="width: 22%">Kelas</th>
                    <th>Jumlah Siswa</th>
                    <th>Hadir</th>
                    <th>Izin</th>
                    <th>Sakit</th>
                    <th>Alpa</th>
                    <th>Total Catatan</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rekap as $row)
                    <tr>
                        <td class="kelas">{{ $row['kelas'] }}</td>
                        <td>{{ $row['students'] }}</td>
                        <td>{{ $row['hadir'] }}</td>
                        <td>{{ $row['izin'] }}</td>
                        <td>{{ $row['sakit'] }}</td>
                        <td>{{ $row['alpa'] }}</td>
                        <td>{{ $row['total'] }}</td>
                        <td>{{ $row['persentase'] }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td class="kosong" colspan="8">Belum ada data rekap pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            @if (count($rekap) > 0)
                <tfoot>
                    <tr>
                        <td class="kelas">TOTAL ({{ count($rekap) }} kelas)</td>
                        <td>{{ $total['students'] }}</td>
                        <td>{{ $total['hadir'] }}</td>
                        <td>{{ $total['izin'] }}</td>
                        <td>{{ $total['sakit'] }}</td>
                        <td>{{ $total['alpa'] }}</td>
                        <td>{{ $total['total'] }}</td>
                        <td>{{ $total['persentase'] }}%</td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <p style="font-size: 10.5px; color: #6b7280; margin-top: 8px">
            Catatan: persentase kehadiran dihitung dari jumlah hadir dibagi total catatan absensi pada periode
            {{ $periode['label'] }}.
        </p>

        <div class="ttd">
            <div>
                <div class="ruang"></div>
                Hormat saya,<br>
                Kepala Sekolah
            </div>
        </div>
    </div>

    <script>
        // Buka dialog cetak otomatis supaya pengguna tinggal memilih "Save as PDF".
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>

</html>
