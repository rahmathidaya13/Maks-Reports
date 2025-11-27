<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<title>LAPORAN HARIAN UPDATE STATUS</title>

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 50px;
        }

        body {
            font-family: Poppins, Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .table-main {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #2d7244;
            color: white;
            padding: 8px;
            font-size: 12px;
            border: 1px solid #000;
        }

        td {
            padding: 6px;
            border: 1px solid #000;
            text-align: center;
            font-size: 12px;

        }

        .title {
            font-size: 21px;
            font-weight: bold;
            text-align: center;
        }

        .subtitle {
            text-align: center;
            margin-top: 1px;
            font-size: 14px;
        }

        .footer {
            margin-top: 10px;
            width: 100%;
        }

        .ttd-table {
            border: none;
            table-layout: fixed;
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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


        tbody tr:nth-child(odd) td {
            background-color: #ebebfa;
            color: #000000;
        }

        tbody tr:nth-child(even) td {
            background-color: #ffffff;
            color: #000000;
        }


        tfoot tr td:nth-child(1),
        tfoot tr td:nth-child(2),
        tfoot tr td:nth-child(3) {
            border: none;
            /* Gunakan !important jika border standar sulit di-override */
        }

        .ttd-table td {
            background-color: #ffffff !important;
            /* Atur ke warna latar belakang default dokumen Anda (biasanya putih) */
            color: #000000;
            /* Pastikan warna teks tetap hitam */
        }

        .table-header {
            width: 100%;
            /* border-collapse: collapse; */
            border-spacing: 0;
            border: 1px solid #e6e6e6;
            border-radius: 3px;
            overflow: hidden
        }

        .table-header tbody tr td {
            background-color: #f0f0f0 !important;
            /* Atur ke warna latar belakang default dokumen Anda (biasanya putih) */
            color: #000000;
            border: none;
            text-align: left;
            margin-top: 0;
            padding-top: 4px;
            padding-bottom: 4px;

            /* Opsional: Mengatur jarak horizontal (kiri-kanan) */
            padding-left: 5;
            padding-right: 5px;
            font-size: 14px;

            text-overflow: ellipsis;
            word-break: break-all;
            word-wrap: break-word;
        }

        .table-header tr td:nth-child(3) {
            font-weight: bolder;
            border-left: none;

        }

        .table-header tr td:nth-child(2) {
            padding: 0;
            border-left: none;
            border-right: none;
        }

        .table-header tr td:nth-child(1) {
            border-right: none;
            width: 120px;
        }

        .divider {
            border-bottom: 1px solid #131313;
            margin-bottom: 15px;
            margin-top: 15px;
        }
    </style>
</head>

<body>

    {{-- <div class="title">LAPORAN HARIAN UPDATE STATUS</div>
    <div class="subtitle">PT. Toko Maksindo Cabang {{ ucwords($branch) }}</div>
    <div class="subtitle">Tanggal Laporan:
        {{ \Carbon\Carbon::now()->translatedFormat('l, d-m-Y') . ', ' . 'Minggu ke-' . \Carbon\Carbon::now()->weekOfMonth }}
    </div> --}}
    <div class="header">
        <table class="table-header">
            <tbody>
                <tr>
                    <td>Judul</td>
                    <td>:</td>
                    <td>Laporan Harian Update Status</td>
                </tr>

                <tr>
                    <td>Tempat</td>
                    <td>:</td>
                    <td>PT. Toko Maksindo Cabang {{ ucwords($branch) }}</td>
                </tr>
                <tr>
                    <td>Tanggal Laporan</td>
                    <td>:</td>
                    <td>{{ \Carbon\Carbon::now()->translatedFormat('l, d-m-Y') }}</td>
                </tr>
                <tr>
                    <td>Periode</td>
                    <td>:</td>
                    <td>{{ 'Minggu ke-' . \Carbon\Carbon::now()->weekOfMonth }}</td>
                </tr>
                <tr>
                    <td>Dibuat Oleh</td>
                    <td>:</td>
                    <td>{{ ucwords($nama ?? 'Pembuat') . ' ' . '(' . $jabatan . ')' }}</td>
                </tr>

            </tbody>
        </table>
    </div>
    <div class="divider"></div>

    <table class="table-main">
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
                        <b>{{ __('................................') }}</b>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
