<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<title>LAPORAN HARIAN UPDATE STATUS</title>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: #2d7244;
            color: white;
            padding: 8px;
            border: 1px solid #000;
        }

        td {
            padding: 6px;
            border: 1px solid #000;
            text-align: center;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
        }

        .subtitle {
            text-align: center;
            margin-top: -5px;
        }

        .footer {
            margin-top: 10px;
            width: 100%;
        }

        .ttd-table {
            border: none;
            table-layout: fixed;
        }

        .ttd-table td {
            border: none;
            padding: 0;
            vertical-align: top;
            font-size: 14px;
            width: 50%;
        }

        /* Tambahkan kelas khusus untuk perataan teks */
        .ttd-left {
            text-align: left;
            /* Teks TTD Dibuat Oleh akan rata kiri di kolomnya */
            padding-left: 20px;
            /* Tambahkan sedikit padding dari tepi kiri */
        }

        .ttd-right {
            text-align: right;
            /* Teks TTD Diketahui Oleh akan rata kanan di kolomnya */
            padding-right: 20px;
            /* Tambahkan sedikit padding dari tepi kanan */
        }
        .ttd-table .spacer {
            height: 60px;
        }
    </style>
</head>

<body>

    <div class="title">LAPORAN HARIAN UPDATE STATUS</div>
    <div class="subtitle">PT. Toko Maksindo Cabang {{ ucwords($branch) }}</div>
    <div class="subtitle">Tanggal Laporan:
        {{ \Carbon\Carbon::now()->translatedFormat('l, d-m-Y') . ', ' . 'Minggu ke-' . \Carbon\Carbon::now()->weekOfMonth }}
    </div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Status</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Jumlah Status</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($reports as $row)
                <tr>
                    <td>{{ $row['no'] }}</td>
                    <td>{{ $row['kode'] }}</td>
                    <td>{{ $row['tanggal'] }}</td>
                    <td>{{ $row['jam'] }}</td>
                    <td>{{ $row['jumlah_status'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td><b>Total</b></td>
                <td><b>{{ $total }}</b></td>
            </tr>
        </tfoot>
    </table>


    <div class="footer">
        <table class="ttd-table">
            <tr>
                <td class="ttd-left" style="width: 50%;">
                    <div>Dibuat Oleh,</div>
                    <div class="spacer"></div>
                    <div>
                        <b>{{ ucwords($nama ?? 'Pembuat') }}</b>
                    </div>
                </td>

                <td class="ttd-right" style="width: 50%;">
                    <div>Diketahui Oleh,</div>
                    <div class="spacer"></div>
                    <div>
                        <b>{{ ucwords($known_by_name ?? 'YANG MENGETAHUI') }}</b>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>

</body>

</html>

</body>

</html>
