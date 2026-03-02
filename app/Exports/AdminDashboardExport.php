<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;

use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdminDashboardExport implements FromArray, WithEvents, ShouldAutoSize
{
    protected $data;
    protected $start;
    protected $end;
    public function __construct($data, $start, $end)
    {
        $this->data = $data;
        $this->start = $start;
        $this->end = $end;
    }
    public function array(): array
    {
        $rows = [];

        // 1. KOP LAPORAN
        $rows[] = ['LAPORAN KINERJA PENJUALAN - SISTEM POS', '', '', '', ''];
        $rows[] = ['Periode: ' . \Carbon\Carbon::parse($this->start)->translatedFormat('d F Y') . ' s/d ' . \Carbon\Carbon::parse($this->end)->translatedFormat('d F Y'), '', '', '', ''];
        $rows[] = ['', '', '', '', '']; // Baris Kosong

        // 2. RINGKASAN FINANSIAL
        $rows[] = ['RINGKASAN FINANSIAL', '', '', '', ''];
        $rows[] = ['Indikator', 'Nominal', '', '', ''];
        $rows[] = ['Total Transaksi', $this->data['widgets']['total_sales'], '', '', ''];
        $rows[] = ['Pendapatan Lunas', $this->data['widgets']['total_revenue'], '', '', ''];
        $rows[] = ['Piutang / DP', $this->data['widgets']['total_revenue_dp'], '', '', ''];
        $rows[] = ['Potensi Batal', $this->data['widgets']['total_revenue_batal'], '', '', ''];
        $rows[] = ['', '', '', '', '']; // Baris Kosong

        // 3. LEADERBOARD CABANG
        $rows[] = ['KINERJA CABANG TERBAIK', '', '', '', ''];
        $rows[] = ['No', 'Nama Cabang', 'Jumlah Trx', 'Omset Lunas', 'Piutang (DP)'];

        $no = 1;
        if (count($this->data['leaderboards']['top_branches']) > 0) {
            foreach ($this->data['leaderboards']['top_branches'] as $branch) {
                $rows[] = [
                    $no++,
                    $branch->name,
                    $branch->transactions_count ?? 0,
                    $branch->total_lunas ?? 0,
                    $branch->total_dp ?? 0
                ];
            }
        } else {
            $rows[] = ['Belum ada data transaksi cabang.', '', '', '', ''];
        }

        $rows[] = ['', '', '', '', '']; // Baris Kosong

        // 4. LEADERBOARD PRODUK
        $rows[] = ['PRODUK TERLARIS', '', '', '', ''];
        $rows[] = ['No', 'Nama Produk', 'Qty Terjual', '', ''];

        $no = 1;
        if (count($this->data['leaderboards']['top_products']) > 0) {
            foreach ($this->data['leaderboards']['top_products'] as $product) {
                $rows[] = [
                    $no++,
                    $product->name,
                    $product->transactions_sum_quantity ?? 0,
                    '',
                    ''
                ];
            }
        } else {
            $rows[] = ['Belum ada data penjualan produk.', '', '', '', ''];
        }

        return $rows;
    }

    // public function columnWidths(): array
    // {
    //     return [
    //         'A' => 8,   // Kolom No / Indikator
    //         'B' => 35,  // Kolom Nama Cabang/Produk
    //         'C' => 15,  // Kolom Jml Trx
    //         'D' => 20,  // Kolom Lunas
    //         'E' => 20,  // Kolom Piutang
    //     ];
    // }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // --- 1. Styling Kop Surat ---
                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF2B6CB0'); // Biru
                $sheet->getStyle('A2')->getFont()->setItalic(true);

                // --- 2. Styling Header Tabel Finansial (Baris 4 & 5) ---
                $sheet->mergeCells('A4:B4');
                $sheet->getStyle('A4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('A4:B4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2C3E50'); // Dark Blue
                $sheet->getStyle('A5:B5')->getFont()->setBold(true);
                $sheet->getStyle('A5:B5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEDF2F7'); // Light Gray


                // Tambahkan Border untuk Finansial
                $sheet->getStyle('A4:B9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Format Rupiah asli Excel untuk Finansial (Baris 7-9, Kolom B)
                $sheet->getStyle('B7:B9')->getNumberFormat()->setFormatCode('"Rp "#,##0_-');

                // --- 3. Styling Header Tabel Cabang (Baris 11 & 12) ---
                $sheet->mergeCells('A11:E11');
                $sheet->getStyle('A11')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('A11:E11')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2C3E50');
                $sheet->getStyle('A12:E12')->getFont()->setBold(true);
                $sheet->getStyle('A12:E12')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEDF2F7');

                // Format Rupiah untuk Cabang (Kolom D dan E, mulai baris 13 ke bawah)
                // Kita ambil row terakhir dinamis dari data
                $branchStartRow = 13;
                $branchCount = max(count($this->data['leaderboards']['top_branches']), 1);
                $branchEndRow = $branchStartRow + $branchCount - 1;

                $sheet->getStyle("A11:E{$branchEndRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("D{$branchStartRow}:E{$branchEndRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0_-');

                // Rata tengah untuk kolom No dan Qty Cabang
                $sheet->getStyle("A12:A{$branchEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C12:C{$branchEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


                // --- 4. Styling Header Tabel Produk ---
                $prodTitleRow = $branchEndRow + 2;
                $prodHeaderRow = $prodTitleRow + 1;
                $prodStartRow = $prodHeaderRow + 1;
                $prodCount = max(count($this->data['leaderboards']['top_products']), 1);
                $prodEndRow = $prodStartRow + $prodCount - 1;

                $sheet->mergeCells("A{$prodTitleRow}:C{$prodTitleRow}");
                $sheet->getStyle("A{$prodTitleRow}")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle("A{$prodTitleRow}:C{$prodTitleRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2C3E50');

                $sheet->getStyle("A{$prodHeaderRow}:C{$prodHeaderRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$prodHeaderRow}:C{$prodHeaderRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEDF2F7');

                $sheet->getStyle("A{$prodTitleRow}:C{$prodEndRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Rata tengah untuk kolom No dan Qty Produk
                $sheet->getStyle("A{$prodHeaderRow}:A{$prodEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$prodHeaderRow}:C{$prodEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        ];
    }
}
