<table>
    <tr>
        <td colspan="5">LAPORAN DATA KINERJA PENJUALAN</td>
    </tr>
    <tr>
        <td colspan="5">Periode: {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} s/d
            {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
        <td colspan="5"></td>
    </tr>
    <tr>
        <th style="background-color: #d9ead3;">Indikator</th>
        <th style="background-color: #d9ead3;">Total / Nominal</th>
    </tr>
    <tr>
        <td>Total Transaksi</td>
        <td>{{ $data['widgets']['total_sales'] }}</td>
    </tr>
    <tr>
        <td>Pendapatan Lunas</td>
        <td>{{ $data['widgets']['total_revenue'] }}</td>
    </tr>
    <tr>
        <td>Piutang / DP</td>
        <td>{{ $data['widgets']['total_revenue_dp'] }}</td>
    </tr>
    <tr>
        <td>Potensi Batal</td>
        <td>{{ $data['widgets']['total_revenue_batal'] }}</td>
    </tr>
    <tr>
        <td colspan="5"></td>
    </tr>
    <tr>
        <th colspan="5" style="background-color: #c9daf8; font-weight: bold;">KINERJA CABANG TERBAIK</th>
    </tr>
    <tr >
        <th style="font-weight: bold; border: 1px solid #000;">No</th>
        <th style="font-weight: bold; border: 1px solid #000;">Nama Cabang</th>
        <th style="font-weight: bold; border: 1px solid #000;">Jml Transaksi</th>
        <th style="font-weight: bold; border: 1px solid #000;">Omset Lunas</th>
        <th style="font-weight: bold; border: 1px solid #000;">Piutang (DP)</th>
    </tr>
    @foreach ($data['leaderboards']['top_branches'] as $index => $branch)
        <tr>
            <td style="border: 1px solid #000;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000;">{{ $branch->name }}</td>
            <td style="border: 1px solid #000;">{{ $branch->transactions_count ?? 0 }}</td>
            <td style="border: 1px solid #000;">{{ $branch->total_lunas ?? 0 }}</td>
            <td style="border: 1px solid #000;">{{ $branch->total_dp ?? 0 }}</td>
        </tr>
    @endforeach
</table>
