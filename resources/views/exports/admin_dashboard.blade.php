<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        /* Pengaturan Halaman Premium */
        @page {
            margin: 40px 50px;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #2d3748;
            line-height: 1.5;
        }

        /* Header Korporat */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #3182ce;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .company-name {
            font-size: 20pt;
            font-weight: bold;
            color: #2b6cb0;
            margin: 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .report-title {
            font-size: 14pt;
            color: #4a5568;
            margin: 5px 0 0 0;
        }

        .meta-info {
            text-align: right;
            font-size: 9pt;
            color: #718096;
        }

        /* Typography */
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #2b6cb0;
            text-transform: uppercase;
            margin-bottom: 10px;
            margin-top: 30px;
            letter-spacing: 0.5px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        /* Tabel Ringkasan (Mirip "Cards") */
        .summary-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: separate;
            border-spacing: 10px 0;
        }

        .summary-box {
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            padding: 15px;
            text-align: center;
            width: 25%;
        }

        .summary-label {
            font-size: 8pt;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: block;
        }

        .summary-value {
            font-size: 14pt;
            font-weight: bold;
            color: #2d3748;
        }

        .text-success {
            color: #38a169;
        }

        .text-warning {
            color: #d69e2e;
        }

        .text-danger {
            color: #e53e3e;
        }

        /* Tabel Data Elegan (Batas Bawah Saja) */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9.5pt;
        }

        .data-table th {
            background-color: #edf2f7;
            color: #4a5568;
            text-transform: uppercase;
            font-size: 8pt;
            padding: 10px;
            text-align: left;
            border-top: 1px solid #cbd5e0;
            border-bottom: 2px solid #cbd5e0;
        }

        .data-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        /* Efek Zebra Halus */
        .data-table tbody tr:nth-child(even) {
            background-color: #fbfbfc;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            width: 100%;
            text-align: start;
            font-size: 8pt;
            color: #a0aec0;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
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
    </style>
</head>

<body>
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <div class="report-title">{{ $title }}</div>
                <strong>Periode:</strong> {{ \Carbon\Carbon::parse($startOfMonth)->translatedFormat('d F Y') }} -
                {{ \Carbon\Carbon::parse($endOfMonth)->translatedFormat('d F Y') }}<br>
                <strong>Dicetak Oleh:</strong> {{ $adminName }}<br>
                <strong>Waktu Cetak:</strong> {{ now()->translatedFormat('d M Y, H:i') }} WIB
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

    <table class="summary-table">
        <tr>
            <td class="summary-box">
                <span class="summary-label">Total Transaksi</span>
                <span class="summary-value">{{ number_format($data['widgets']['total_sales'], 0, ',', '.') }}</span>
            </td>
            <td class="summary-box">
                <span class="summary-label">Transaksi Lunas</span>
                <span class="summary-value text-success">Rp
                    {{ number_format($data['widgets']['total_revenue'], 0, ',', '.') }}</span>
            </td>
            <td class="summary-box">
                <span class="summary-label">Transaksi Belum Lunas</span>
                <span class="summary-value text-warning">Rp
                    {{ number_format($data['widgets']['total_revenue_dp'], 0, ',', '.') }}</span>
            </td>
            <td class="summary-box">
                <span class="summary-label">Transaksi Dibatalkan</span>
                <span class="summary-value text-danger">Rp
                    {{ number_format($data['widgets']['total_revenue_batal'], 0, ',', '.') }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Top 10 Cabang Terbaik</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 35%;">Nama Cabang</th>
                <th style="width: 25%;" class="text-center">Jumlah Transaksi</th>
                <th style="width: 25%;" class="text-right">Transaksi Lunas</th>
                <th style="width: 20%;" class="text-right">Transaksi DP</th>
                <th style="width: 20%;" class="text-right">Transaksi Batal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['leaderboards']['top_branches'] as $index => $branch)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="fw-bold">{{ $branch->name }}</td>
                    <td class="text-center">{{ $branch->transactions_count ?? 0 }} trx</td>
                    <td class="text-left fw-bold text-success">
                        Rp {{ number_format($branch->total_lunas ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="text-left text-warning">
                        Rp {{ number_format($branch->total_dp ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="text-left text-danger">
                        Rp {{ number_format($branch->total_batal ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px;">Belum ada data transaksi cabang pada
                        periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Top 10 Produk Terlaris</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 75%;">Nama Produk</th>
                <th style="width: 20%;" class="text-center">Kuantitas Terjual</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['leaderboards']['top_products'] as $index => $product)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="fw-bold">{{ $product->name }}</td>
                    <td class="text-center">
                        <span
                            style="background-color: #ebf8fa; color: #319795; padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 8pt;">
                            {{ $product->transactions_sum_quantity ?? 0 }} unit
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center" style="padding: 20px;">Belum ada data penjualan produk pada
                        periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh {{ $adminName }} pada {{ now()->translatedFormat('d M Y, H:i') }} WIB <br>
        Dokumen ini adalah laporan komputerisasi dan sah tanpa tanda tangan.
    </div>

</body>

</html>
