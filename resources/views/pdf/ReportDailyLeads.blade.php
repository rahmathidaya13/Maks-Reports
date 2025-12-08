<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <title>{{ $title ?? 'Laporan Summary' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12px;
            color: #2c3e50;
            position: relative;
        }
        .container {
            width: 100%;
            padding: 6px 0;
        }

        /* Table layout mirip Bootstrap */
        .tbl {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            table-layout: fixed;
            /* menjaga kolom tetap proporsional di PDF */
            word-break: break-word;
        }

        .tbl thead tr {
            background-color: #1b1b1b;
            color: #fff;
        }

        .tbl thead th {
            padding: 10px 8px;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid #2b2b2b;
            text-align: center;
        }

        /* Mengatur kolom pertama rata kiri */
        .tbl thead th.text-start {
            text-align: left;
            padding-left: 12px;
        }

        .tbl tbody td {
            padding: 10px 8px;
            border: 1px solid #e1e1e1;
            vertical-align: middle;
            /* align-middle */
            font-size: 12px;
            color: #000;
        }

        /* stripe effect seperti table-striped */
        .tbl tbody tr:nth-child(odd) td {
            background-color: #f7f7f9;
        }

        .tbl tbody tr:nth-child(even) td {
            background-color: #ffffff;
        }

        /* text alignment helpers */
        .text-center {
            text-align: center;
        }

        .text-start {
            text-align: left;
        }

        /* .fw-semibold {
            font-weight: 600;
        } */

        .fw-bold {
            font-weight: 700;
        }

        /* Footer (tfoot) style seperti table-dark */
        .tbl tfoot tr {
            background-color: #1b1b1b;
            color: #fff;
            font-weight: 700;
        }

        .tbl tfoot td {
            padding: 10px 8px;
            border: none;
        }

        /* small responsive fixes for PDF */
        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <div class="container">
        <h3 style="margin:0 0 6px 0;">{{ $title ?? 'Laporan' }}</h3>
        @if (!empty($subtitle))
            <div style="margin:0 0 8px 0; color:#6b6b6b;">{{ $subtitle }}</div>
        @endif

        <table class="tbl" role="table">
            <thead>
                <tr>
                    <th class="text-start">Kategori</th>
                    <th class="text-center">Jumlah</th>
                    <th class="text-center">Closing</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td class="fw-semibold text-start">Leads</td>
                    <td class="text-center fw-semibold">{{ $row->leads ?? 0 }}</td>
                    <td class="text-center fw-semibold">{{ $row->closing ?? 0 }}</td>
                </tr>

                <tr>
                    <td class="fw-semibold text-start">FU Konsumen Kemarin (H-1)</td>
                    <td class="text-center fw-semibold">{{ $row->fu_yesterday ?? 0 }}</td>
                    <td class="text-center fw-semibold">{{ $row->fu_yesterday_closing ?? 0 }}</td>
                </tr>

                <tr>
                    <td class="fw-semibold text-start">FU Konsumen Kemarennya (H-2)</td>
                    <td class="text-center fw-semibold">{{ $row->fu_before_yesterday ?? 0 }}</td>
                    <td class="text-center fw-semibold">{{ $row->fu_before_yesterday_closing ?? 0 }}</td>
                </tr>

                <tr>
                    <td class="fw-semibold text-start">FU Konsumen Minggu Kemarennya</td>
                    <td class="text-center fw-semibold">{{ $row->fu_last_week ?? 0 }}</td>
                    <td class="text-center fw-semibold">{{ $row->fu_last_week_closing ?? 0 }}</td>
                </tr>

                <tr>
                    <td class="fw-semibold text-start">Engage Konsumen Lama</td>
                    <td class="text-center fw-semibold">{{ $row->engage_old_customer ?? 0 }}</td>
                    <td class="text-center fw-semibold">{{ $row->engage_closing ?? 0 }}</td>
                </tr>
            </tbody>

            <tfoot>
                <tr>
                    <td class="text-start fw-bold">Total</td>
                    <td class="text-center fw-bold">
                        {{ ($row->leads ?? 0) +
                            ($row->fu_yesterday ?? 0) +
                            ($row->fu_before_yesterday ?? 0) +
                            ($row->fu_last_week ?? 0) +
                            ($row->engage_old_customer ?? 0) }}
                    </td>
                    <td class="text-center fw-bold">
                        {{ ($row->closing ?? 0) +
                            ($row->fu_yesterday_closing ?? 0) +
                            ($row->fu_before_yesterday_closing ?? 0) +
                            ($row->fu_last_week_closing ?? 0) +
                            ($row->engage_closing ?? 0) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>

</html>
