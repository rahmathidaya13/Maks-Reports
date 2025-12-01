<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm 15mm;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12px;
            color: #2c3e50;
            position: relative;
        }

        /* WATERMARK LOGO */
        .logo-watermark {
            position: absolute;
            top: {{ $logo_top ?? '50%' }};
            left: {{ $logo_left ?? '50%' }};
            transform: translate(-50%, -50%);
            opacity: {{ $logo_opacity ?? 0.08 }};
            width: {{ $logo_width ?? '450px' }};
            /* memastikan selalu di belakang teks */
            z-index: -1;
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            text-align: left;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #d0d0d0;
            position: relative;
            z-index: 10;
            background-color: #fafafa;
        }

        .header-left {
            max-width: 50%;
            padding-left: 5px;
            /* supaya teks tidak mepet logo */
        }

        .header-right .logo {
            width: 200px;
            position: absolute;
            top: 25;
            right: 10;
            height: auto;
            object-fit: contain;
        }

        .header .title {
            font-size: 22px;
            font-weight: bold;
            color: #32465a;
        }

        .header .subtitle {
            font-size: 16px;
            color: #423f3f;
        }

        .header .info {
            margin-top: 5px;
            font-size: 14px;
            color: #555;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            z-index: 10;
            position: relative;
            text-align: center;
        }

        table th {
            background: #1b1b1b;
            font-weight: bold;
            border: 1px solid #bdc3c7;
            padding: 8px;
            color: #f7f7f7;
            font-size: 12px;
        }

        table td {
            border: 1px solid #dcdcdc;
            padding: 8px;
            font-size: 12px;
            color: #000000;
        }

        tbody tr:nth-child(odd) td {
            background-color: #eeeef3c2;
            color: #000000;
        }

        table tr:nth-child(even) {
            background: #ffffff;
        }

        /* SIGNATURES */
        .sign-section {
            margin-top: 45px;
            position: relative;
            z-index: 10;
        }

        .sign-table {
            width: 100%;
            text-align: center;
        }

        .sign-table td {
            height: 100px;
            vertical-align: bottom;
        }

        .sign-title {
            /* font-weight: bold; */
            margin-bottom: 60px;
            color: #2c3e50;
        }

        .sign-table tr td {
            background-color: transparent !important;
        }
    </style>
</head>

<body>

    <!-- LOGO WATERMARK -->
    {{-- @if (!empty($logo))
        <img src="{{ $logo }}" class="logo-watermark">
    @endif --}}

    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            <div class="title">{{ $title ?? 'Laporan Korporat' }}</div>
            <div class="subtitle">{{ $subtitle ?? 'Subjudul Laporan' }}</div>
            <div class="info">
                @foreach ($info as $item)
                    {{ $item }} <br>
                @endforeach
            </div>
        </div>
        @if (!empty($logo))
            <div class="header-right">
                <img src="{{ $logo }}" class="logo" alt="Logo">
            </div>
        @endif
    </div>

    <!-- TABEL DATA -->
    <table>
        <thead>
            <tr>
                @foreach ($headers as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @forelse($data as $row)
                <tr>
                    @foreach ($row as $col)
                        <td>{{ $col }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" style="text-align:center;">
                        Tidak ada data
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="{{ count($headers) - 2 }}" style="border:none !important;"></td>
                <td><b>Total</b></td>
                <td><b>{{ $total ?? '0' }}</b></td>
            </tr>
        </tfoot>
    </table>

    <!-- TANDA TANGAN -->
    <div class="sign-section">
        <table class="sign-table">
            <tr>
                <td>
                    <div class="sign-title">Dibuat Oleh,</div>
                    <div><b>{{ $dibuat ?? '_________________' }}</b></div>
                </td>

                <td>
                    <div class="sign-title">Diketahui Oleh,</div>
                    <div><b>{{ $diketahui ?? '_________________' }}</b></div>
                </td>

                <td>
                    <div class="sign-title">Disetujui Oleh,</div>
                    <div><b>{{ $disetujui ?? '__________________' }}</b></div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
