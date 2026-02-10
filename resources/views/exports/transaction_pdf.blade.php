<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi</title>
    <style>
        /* --- RESET & BASE STYLES --- */
        @page {
            margin: 1cm 1.5cm;
            /* Margin halaman yang pas */
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            /* Ukuran font dasar sedikit dikecilkan agar muat banyak */
            color: #333;
            line-height: 1.4;
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

        .text-muted {
            color: #6c757d;
            font-size: 8pt;
        }

        .no-wrap {
            white-space: nowrap;
        }

        /* --- HEADER SECTION (KOP SURAT SEDERHANA) --- */
        .report-header {
            width: 100%;
            margin-bottom: 25px;
            border-bottom: 3px solid #2c3e50;
            /* Garis aksen tebal di bawah header */
            padding-bottom: 15px;
        }

        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
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

        /* --- MAIN DATA TABLE --- */
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

        /* --- CONTENT STYLING --- */
        .invoice-number {
            color: #2c3e50;
            font-size: 10pt;
        }

        /* Styling untuk List Item & Payment yang rapi */
        .detail-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .detail-list li {
            margin-bottom: 3px;
            padding-bottom: 3px;
            font-size: 10.5pt;

            /* border-bottom: 1px dashed #dce4ec; */
            /* Garis putus-putus halus pemisah item */
        }

        .detail-list li:last-child {
            border-bottom: none;
            /* Hilangkan garis di item terakhir */
        }

        .qty-badge {
            background-color: #e9ecef;
            color: #495057;
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 7pt;
            font-weight: bold;
            margin-left: 5px;
        }

        /* Grand Total Besar */
        .grand-total {
            font-size: 11pt;
            font-weight: bold;
            color: #2c3e50;
        }

        /* --- MODERN STATUS BADGES (Pill Shape) --- */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 50px;
            /* Membuat bentuk pil */
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 5px;
        }

        .bg-success {
            background-color: #d1e7dd;
            color: #0f5132;
            /* Hijau lembut modern */
        }

        .bg-warning {
            background-color: #fff3cd;
            color: #664d03;
            /* Kuning lembut modern */
        }

        .bg-danger {
            background-color: #f8d7da;
            color: #842029;
            /* Merah lembut modern */
        }

        /* --- FOOTER --- */
        .footer-info {
            margin-top: 30px;
            font-size: 8pt;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
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
    </style>
</head>

<body>
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <div class="report-title">Laporan Transaksi</div>
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
                <th class="text-center" style="width: 5%">No</th>
                <th style="width: 17%">Invoice & Tanggal</th>
                <th style="width: 18%">Pelanggan</th>
                <th style="width: 30%">Detail Item Pembelian</th>
                <th style="width: 18%">Riwayat Pembayaran</th>
                <th class="text-right" style="width: 12%">Total & Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $trx)
                <tr>
                    <td class="text-center" style="vertical-align: middle">{{ $index + 1 }}</td>

                    <td style="vertical-align: middle">
                        <div class="invoice-number fw-bold">{{ $trx->invoice }}</div>
                        <div class="text-muted">{{ $trx->created_at->format('d M Y') }}</div>
                        <div class="text-muted">{{ $trx->created_at->format('H:i') }} WIB</div>
                    </td>

                    <td style="vertical-align: middle">
                        @if ($trx->customer)
                            <div class="fw-bold">{{ $trx->customer->customer_name }}</div>
                            @if ($trx->customer->number_phone_customer)
                                <div class="text-muted">
                                    {{ $trx->customer->number_phone_customer }}
                                </div>
                            @endif
                            @if ($trx->customer->address)
                                <div class="text-muted" style="font-style: italic; margin-top: 2px;">
                                    {{ Str::limit($trx->customer->address, 40) }}
                                </div>
                            @endif
                        @else
                            <span class="text-muted">- Pelanggan Umum -</span>
                        @endif
                    </td>

                    <td style="vertical-align: middle">
                        @if ($trx->items && $trx->items->count() > 0)
                            <ul class="detail-list">
                                @foreach ($trx->items as $item)
                                    <li>
                                        {{ $item->product->name ?? 'Produk telah dihapus' }}
                                        <span class="qty-badge no-wrap">x {{ $item->quantity }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted">Tidak ada item</span>
                        @endif
                    </td>
                    @php
                        $totalPaid = $trx->payments->sum('amount');
                        $remaining = max($trx->grand_total - $totalPaid, 0);
                    @endphp
                    <td style="vertical-align: middle">
                        @if ($trx->payments && $trx->payments->count() > 0)
                            <ul class="detail-list">
                                @foreach ($trx->payments as $pay)
                                    <li>
                                        <span class="text-muted">{{ $pay->created_at->format('d/m/y') }}:</span>
                                        <span class="fw-bold" style="float: right;">Rp
                                            {{ number_format($pay->amount, 0, ',', '.') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($remaining > 0)
                                <div class="text-danger text-right small mt-1" style="color: #d35400;">
                                    Sisa: Rp
                                    {{ number_format($remaining, 0, ',', '.') }}</div>
                            @endif
                        @else
                            <span class="text-muted">Belum ada pembayaran</span>
                        @endif
                    </td>

                    <td class="text-right" style="vertical-align: middle">
                        <div class="grand-total no-wrap">Rp {{ number_format($trx->grand_total, 0, ',', '.') }}</div>

                        @if ($trx->status == 'repayment')
                            <span class="badge bg-success">Lunas</span>
                        @elseif($trx->status == 'cancelled')
                            <span class="badge bg-danger">Dibatalkan</span>
                        @else
                            <span class="badge bg-warning">Belum Lunas</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px; color: #999;">
                        Tidak ada data transaksi yang ditemukan untuk periode/filter ini.
                    </td>
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
                <strong>Total Transaksi: {{ $transactions->count() }} Data</strong>
            </td>
        </tr>
    </table>

</body>

</html>
