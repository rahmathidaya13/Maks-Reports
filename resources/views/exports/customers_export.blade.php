<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Pelanggan</title>
    <style>
        @page {
            margin: 1cm 1.5cm;
            /* Margin halaman yang pas */
        }

        body {
            font-family: sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.4;

        }

        .header {
            width: 100%;
            margin-bottom: 25px;
            border-bottom: 3px solid #2c3e50;
            /* Garis aksen tebal di bawah header */
            padding-bottom: 15px;
            position: relative;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 70%;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 30%;
        }

        .logo-img {
            max-height: 60px;
            width: auto;
        }

        .report-title {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #34495e;
        }

        .report-subtitle {
            font-size: 10pt;
            color: #7f8c8d;
            margin: 3px 0;
            font-weight: bold;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: 20px;
        }

        /* Table Header Styling */
        .table-data thead th {
            background-color: #2c3e50;
            /* Warna Slate Blue */
            color: #ffffff;
            text-transform: uppercase;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            padding: 12px 8px;
            border: none;
            text-align: left;
        }

        /* Table Body Styling */
        .table-data tbody td {
            padding: 10px 8px;
            vertical-align: top;
            /* Penting agar konten sejajar di atas */
            border-bottom: 1px solid #e9ecef;
            /* Garis pemisah halus antar baris */
        }

        /* Zebra Striping (Baris selang-seling) untuk readability */
        .table-data tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .badge {
            background: #eef2f7;
            color: #2c3e50;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #888;
            font-size: 8pt;
        }

        .footer-info {
            margin-top: 30px;
            font-size: 8pt;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
            padding-top: 10px;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <div class="report-title">Data Pelanggan</div>
                <div class="report-subtitle">{{ ucwords(auth()->user()->name) }} -
                    {{ ucwords(auth()->user()->profile->branch->name) }} </div>
                <div class="text-muted">
                    Dicetak: {{ now()->format('d F Y, H:i') }}
                </div>
            </div>

            <div class="header-right">
                @if (!empty($logo))
                    <img src="{{ $logo }}" class="logo-img" alt="Logo Perusahaan">
                @else
                    <h2 style="color:#bdc3c7; margin:0;">COMPANY LOGO</h2>
                @endif
            </div>
        </div>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 5%" class="text-center">No</th>
                <th style="width: 20%">Id Pelanggan</th>
                <th style="width: 20%">Identitas (Nama & NIK)</th>
                <th style="width: 15%">Kontak</th>
                <th style="width: 15%">Usaha</th>
                <th style="width: 30%">Alamat & Lokasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $index => $c)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ substr($c->customer_id, 0, 8) }}</td>
                    <td>
                        <strong>{{ ucwords($c->customer_name) }}</strong><br>
                        <span class="text-muted">NIK: {{ $c->national_id_number ?? '-' }}</span>
                    </td>
                    <td>
                        {{ $c->number_phone_customer }}
                    </td>
                    <td>
                        <span>{{ ucwords($c->type_bussiness) }}</span>
                    </td>
                    <td>
                        {{ $c->address }}<br>
                        <span class="text-muted">{{ ucwords($c->city) }}, {{ ucwords($c->province) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">Data tidak ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer-info" width="100%">
        <tr>
            <td>
                Dicetak oleh sistem pada {{ now()->toDateTimeString() }}. <br>
                Dokumen ini adalah laporan komputerisasi dan sah tanpa tanda tangan.
            </td>
            <td class="text-right" style="vertical-align: top;">
                <strong>Total Pelanggan: {{ $customers->count() }} Data</strong>
            </td>
        </tr>
    </table>
</body>

</html>
