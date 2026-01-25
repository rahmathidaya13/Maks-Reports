<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Laporan Produk' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 15mm 15mm 15mm;
            /* Top, Right, Bottom, Left */
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            /* Font aman untuk DOMPDF */
            font-size: 11px;
            /* Sedikit dikecilkan agar muat banyak kolom */
            color: #34495e;
            line-height: 1.4;
        }

        /* --- WATERMARK --- */
        .logo-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.03;
            width: 500px;
            z-index: -1;
            filter: grayscale(100%);
        }

        /* --- HEADER SECTION --- */
        .header {
            width: 100%;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #3498db;
            /* Aksen Biru */
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

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 14px;
            color: #7f8c8d;
            font-style: italic;
            margin-bottom: 10px;
        }

        .meta-info {
            font-size: 10px;
            color: #555;
            background-color: #f0f3f5;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
        }

        .logo-img {
            max-height: 60px;
            width: auto;
        }

        /* --- TABLE DESIGN --- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: #fff;
        }

        table th {
            background-color: #2c3e50;
            /* Midnight Blue */
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            padding: 10px 8px;
            border: 1px solid #2c3e50;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 8px;
            border: 1px solid #e0e0e0;
            vertical-align: middle;
            color: #2c3e50;
        }

        /* Zebra Striping yang lebih modern */
        table tbody tr:nth-child(even) {
            background-color: #f8fbfd;
            /* Biru sangat muda */
        }

        table tbody tr:hover {
            background-color: #eef6fb;
        }

        /* Kolom Harga biasanya Rata Kanan */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        /* --- PRODUCT BADGES (Untuk Kondisi) --- */
        .badge {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #fff;
            display: inline-block;
            vertical-align: middle
        }

        /* Warna Badge berdasarkan kondisi */
        .badge.new {
            background-color: #348ad1;
        }

        /* Hijau */
        .badge.used {
            background-color: #f39c12;
        }

        /* Orange */
        .badge.refurbished {
            background-color: #8e44ad;
        }

        /* Ungu */
        .badge.damaged {
            background-color: #c0392b;
        }

        /* Merah */
        .badge.discontinued {
            background-color: #7f8c8d;
        }

        .badge.published {
            background-color: #27ae60;
        }

        .badge.draft {
            background-color: #7f8c8d;
        }

        /* Abu */

        /* --- PRICE TAG STYLING --- */
        .price-tag {
            font-family: "Courier New", monospace;
            font-weight: bold;
            color: #2980b9;
        }

        .discount-strike {
            text-decoration: line-through;
            color: #e74c3c;
            font-size: 9px;
            margin-right: 4px;
        }

        /* --- FOOTER (Halaman & Tanggal) --- */
        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #ddd;
            padding-top: 5px;
            font-size: 9px;
            color: #95a5a6;
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            text-align: left;
            width: 50%;
        }

        .footer-right {
            display: table-cell;
            text-align: right;
            width: 50%;
        }

        /* Nomor Halaman Otomatis DOMPDF */
        .page-number:before {
            content: counter(page);
        }


        /* --- STYLING KHUSUS KOLOM HARGA (YANG DIPERBAIKI) --- */
        .price-box {
            margin-bottom: 6px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #dcdcdc;
        }

        .price-box:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .branch-name {
            font-size: 9px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }

        .price-row {
            display: block;
            margin-bottom: 2px;
        }

        .base-price-strike {
            text-decoration: line-through;
            color: #95a5a6;
            font-size: 12px;
            margin-right: 5px;
        }

        .final-price {
            font-family: "Courier New", monospace;
            font-weight: bold;
            color: #2980b9;
            font-size: 12px;
        }

        .discount-badge {
            background-color: #e74c3c;
            color: white;
            font-size: 9px;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: bold;
            margin-left: 5px;
            vertical-align: middle;
        }

        .period-info {
            font-size: 9px;
            color: #7f8c8d;
            margin-top: 2px;
            display: block;
        }
    </style>
</head>

<body>

    @if (!empty($logo))
        <img src="{{ $logo }}" class="logo-watermark">
    @endif

    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <div class="title">{{ $title ?? 'DAFTAR PRODUK' }}</div>
                <div class="subtitle">Filter Cabang: {{ ucwords($filter_branch) ?? 'Semua Cabang' }}</div>

                <div class="meta-info">
                    Dicetak Oleh: <b>{{ auth()->user()->name ?? 'Admin' }}</b> |
                    Total Produk: <b>{{ count($products) }}</b>
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

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 25%">Nama Produk</th>
                <th style="width: 15%">Kategori</th>
                <th style="width: 10%" class="text-center">Kondisi</th>
                <th style="width: 30%">Detail Harga & Cabang</th>
                <th style="width: 15%">Publikasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $index => $product)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="font-bold">{{ $product->name }}</div>
                        <span style="font-size: 9px; color: #7f8c8d;">ID:
                            {{ substr(str_replace('-', '', $product->product_id), 0, 11) }}</span>
                    </td>
                    <td style="text-align: center">{{ ucwords(str_replace('-', ' ', $product->category)) }}</td>
                    <td class="text-center">
                        {{-- Class dinamis berdasarkan value (new, used, dll) --}}
                        <span class="badge {{ strtolower($product->item_condition) }}">
                            {{ strtoupper($product->item_condition) }}
                        </span>
                    </td>
                    <td>
                        @forelse($product->prices as $price)
                            <div class="price-box">
                                {{-- Nama Cabang --}}
                                <span class="branch-name">
                                    {{ $price->branch->name ?? 'Pusat' }}
                                </span>

                                {{-- Baris Harga --}}
                                <div class="price-row">
                                    @if ($price->price_type == 'discount' && $price->discount_price < $price->base_price)
                                        {{-- Hitung Diskon --}}
                                        @php
                                            $discountPercent = 0;
                                            if ($price->base_price > 0) {
                                                $discountPercent = round(
                                                    (($price->base_price - $price->discount_price) /
                                                        $price->base_price) *
                                                        100,
                                                );
                                            }
                                        @endphp

                                        {{-- Harga Coret --}}
                                        <span class="base-price-strike">
                                           Rp {{ number_format($price->base_price, 0, ',', '.') }}
                                        </span>

                                        {{-- Harga Akhir --}}
                                        <span class="final-price">
                                            Rp {{ number_format($price->discount_price, 0, ',', '.') }}
                                        </span>

                                        {{-- Badge Persen --}}
                                        <span class="discount-badge">
                                            -{{ $discountPercent }}%
                                        </span>
                                    @else
                                        {{-- Harga Normal --}}
                                        <span class="final-price">
                                            Rp {{ number_format($price->base_price, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </div>
                                <span class="period-info">
                                    {{-- Logika Tampilan Tanggal Mulai --}}
                                    @if ($price->valid_from)
                                        {{ 'Berlaku mulai ' . \Carbon\Carbon::parse($price->valid_from)->format('d M y') }}
                                    @else
                                        <span style="font-style:italic;">Efektif</span>
                                    @endif

                                    {{-- Logika Tampilan Tanggal Berakhir --}}
                                    @if ($price->valid_until)
                                        s/d {{ \Carbon\Carbon::parse($price->valid_until)->format('d M y') }}
                                    @else
                                        {{-- Jika null, berarti berlaku selamanya --}}
                                        &#8594; Seterusnya
                                    @endif
                                </span>

                            @empty
                                <div style="color: #bdc3c7; font-style: italic; font-size:9px;">
                                    Belum ada harga diatur.
                                </div>
                        @endforelse
                    </td>
                    <td style="text-align: center">
                        @forelse ($product->prices as $pr)
                            <span class="badge {{ strtolower($pr->status) }}">
                                {{ $pr->status === 'published' ? 'Publish' : 'Draft' }}
                            </span>
                        @empty
                            <span style="color: #bdc3c7; font-style: italic; font-size:9px;">-</span>
                        @endforelse
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">
                        Data Produk Tidak Ditemukan
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="footer-left">
            Dicetak pada: {{ date('d F Y, H:i') }} WIB
        </div>
        <div class="footer-right">
            Halaman <span class="page-number"></span>
        </div>
    </div>

</body>

</html>
