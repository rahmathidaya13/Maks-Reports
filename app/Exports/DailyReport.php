<?php

namespace App\Exports;

use App\Models\DailyReportModel;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyReport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $start_date = null;
    protected $end_date = null;
    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }
    public function collection()
    {
        // Mengambil data dan memetakan (map) untuk menyesuaikan urutan dan format
        $data = DailyReportModel::with('creator')
            ->where('created_by', auth()->user()->id)
            ->whereNull('deleted_at')
            ->whereBetween('date', [$this->start_date, $this->end_date])
            ->get()
            ->map(function ($report, $index) {
                return [
                    'no' => $index + 1,
                    'tanggal' => Carbon::parse($report->date)->translatedFormat('d-m-Y'),
                    'leads' => (int) $report->leads ?? 0,
                    'closing' => (int) $report->closing ?? 0,
                    'fu_yesterday' => $report->fu_yesterday ?? 0,
                    'fu_yesterday_closing' => $report->fu_yesterday_closing ?? 0,
                    'fu_before_yesterday' => $report->fu_before_yesterday ?? 0,
                    'fu_before_yesterday_closing' => $report->fu_before_yesterday_closing ?? 0,
                    'fu_last_week' => $report->fu_last_week ?? 0,
                    'fu_last_week_closing' => $report->fu_last_week_closing ?? 0,
                    'engage_old_customer' => $report->engage_old_customer ?? 0,
                    'engage_closing' => $report->engage_closing ?? 0,
                ];
            });
        // Jika tidak ada data, tambahkan 1 baris keterangan
        if ($data->count() === 0) {
            return collect([
                [
                    'No' => '',
                    'tanggal' => 'Tidak ada data',
                    'leads' => '',
                    'closing' => '',
                    'fu_yesterday' => '',
                    'fu_yesterday_closing' => '',
                    'fu_before_yesterday' => '',
                    'fu_before_yesterday_closing' => '',
                    'fu_last_week' => '',
                    'fu_last_week_closing' => '',
                    'engage_old_customer' => '',
                    'engage_closing' => '',
                ]
            ]);
        }
        return $data;
    }

    public function headings(): array
    {
        $user = auth()->user();
        $branchName = $user->profile->branch->name ?? '-';

        $weekStart = Carbon::parse($this->start_date)->weekOfMonth;
        $weekEnd = Carbon::parse($this->end_date)->weekOfMonth;
        return [
            ['LAPORAN LEADS HARIAN'],
            ['PT. Toko Maksindo Cabang ' . ucwords($branchName)],
            ['Tanggal Laporan: ' . Carbon::now()->translatedFormat('l, d/m/Y') . ', ' . 'Minggu ke ' . $weekStart . ($weekStart != $weekEnd ? ' s/d ' . $weekEnd : '')],
            [],
            [
                'No',
                'Tanggal',
                'Leads',
                'Closing',
                'FU Konsumen Kemarin (H-1)',
                'Closing',
                'FU Konsumen Kemarennya (H-2)',
                'Closing',
                'FU Konsumen Minggu Kemarennya',
                'Closing',
                'Engage Konsumen Lama',
                'Closing'
            ]
        ];
    }
    public function styles(Worksheet $sheet)
    {
        // =====================================
        //  MERGE UNTUK HEADING UTAMA
        // =====================================
        // foreach (range(1, 12) as $row) {
        //     $sheet->mergeCells("A{$row}:L{$row}");
        // }
        $sheet->mergeCells("A1:L1");
        $sheet->mergeCells("A2:L2");
        $sheet->mergeCells("A3:L3");
        $sheet->mergeCells("A4:L4");

        // Judul utama (A1)
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 21,
                'name' => 'Calibri',
                'color' => ['argb' => 'ff000000'], // biru gelap premium
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'ffffffff'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Sub Judul (A2 & A3)
        $sheet->getStyle('A2:L4')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'name' => 'Calibri',
                'color' => ['argb' => 'ff424242'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'ffffffff'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);


        // Tinggi baris judul dan sub judul
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(18);
        $sheet->getRowDimension(4)->setRowHeight(18);


        // ==============================
        // 🎨 HEADER TABLE (Baris 5)
        // ==============================
        $sheet->getStyle('A5:L5')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'], // putih
                'size' => 12,
                'name' => 'Calibri'
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'ff2d7244'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // ==============================
        // 📄 ISI DATA
        // ==============================
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A6:L$lastRow")->applyFromArray([
            'font' => [
                'size' => 12,
                'name' => 'Calibri'
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);


        // ==============================
        // 🔢 TOTAL JUMLAH 
        // ==============================


        // Hitung total jumlah status
        $total = DailyReportModel::with('creator')
            ->whereBetween('date', [$this->start_date, $this->end_date])
            ->where('created_by', auth()->user()->id)
            ->whereNull('deleted_at')
            ->get();

        // Baris total berada setelah data terakhir
        $totalRow = $lastRow + 1;

        // Tulis label TOTAL (A–D)
        $sheet->setCellValue("B$totalRow", "Total");

        // $sheet->mergeCells("A$totalRow:D$totalRow");

        // Tulis totalnya (kolom E)
        $sheet->setCellValue("C$totalRow", $total->sum('leads'));
        $sheet->setCellValue("D$totalRow", $total->sum('closing'));
        $sheet->setCellValue("E$totalRow", $total->sum('fu_yesterday'));
        $sheet->setCellValue("F$totalRow", $total->sum('fu_yesterday_closing'));
        $sheet->setCellValue("G$totalRow", $total->sum('fu_before_yesterday'));
        $sheet->setCellValue("H$totalRow", $total->sum('fu_before_yesterday_closing'));
        $sheet->setCellValue("I$totalRow", $total->sum('fu_last_week'));
        $sheet->setCellValue("J$totalRow", $total->sum('fu_last_week_closing'));
        $sheet->setCellValue("K$totalRow", $total->sum('engage_old_customer'));
        $sheet->setCellValue("L$totalRow", $total->sum('engage_closing'));

        // Styling baris total
        $sheet->getStyle("A$totalRow:L$totalRow")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'ff2d7244'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        $sheet->getRowDimension($totalRow)->setRowHeight(25);



        // 🌫 Zebra striping
        if ($lastRow > 6) {
            for ($row = 6; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle("A$row:L$row")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'ebebebfa'],
                        ],
                    ]);
                }
            }
        }

        // =====================================
        // TANDA TANGAN (TTD)
        // =====================================

        // $ttdStart = $totalRow + 3;
        // Tanggal + Kota
        // $sheet->getStyle("C{$ttdStart}")->applyFromArray([
        //     'font' => [
        //         'bold' => false,
        //         'size' => 12,
        //     ],
        //     'alignment' => [
        //         'horizontal' => Alignment::HORIZONTAL_CENTER,
        //         'vertical' => Alignment::VERTICAL_CENTER,
        //     ],
        // ]);
        // Label: Dibuat oleh - Diketahui oleh
        // $ttdRow1 = $ttdStart + 2;

        // $sheet->mergeCells("A{$ttdRow1}:B{$ttdRow1}");

        // $sheet->setCellValue("A{$ttdRow1}", "Dibuat oleh,");
        // $sheet->setCellValue("C{$ttdRow1}", "Diketahui oleh,");
        // $sheet->setCellValue("E{$ttdRow1}", "Disetujui oleh,");

        // $sheet->getStyle("A{$ttdRow1}:E{$ttdRow1}")->applyFromArray([
        //     'font' => [
        //         'bold' => false,
        //         'size' => 12,
        //     ],
        //     'alignment' => [
        //         'horizontal' => Alignment::HORIZONTAL_CENTER,
        //         'vertical' => Alignment::VERTICAL_CENTER,
        //     ],
        // ]);

        // // Ruang untuk tanda tangan (kosong)
        // $ttdRow2 = $ttdRow1 + 4;

        // $sheet->mergeCells("A{$ttdRow2}:B{$ttdRow2}");

        // // Nama user dan jabatan tertentu
        // $sheet->setCellValue("A{$ttdRow2}", auth()->user()->name);
        // $sheet->setCellValue("C{$ttdRow2}", "__________________");
        // $sheet->setCellValue("E{$ttdRow2}", "__________________");

        // $sheet->getStyle("A{$ttdRow2}:E{$ttdRow2}")->applyFromArray([
        //     'font' => [
        //         'bold' => true,
        //         'size' => 12,
        //     ],
        //     'alignment' => [
        //         'horizontal' => Alignment::HORIZONTAL_CENTER,
        //         'vertical' => Alignment::VERTICAL_CENTER,
        //     ],
        // ]);

        // $sheet->getStyle("A{$ttdRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // $sheet->getStyle("C{$ttdRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // $sheet->getStyle("E{$ttdRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);



        // Tinggi baris untuk header dan isi data
        $sheet->getRowDimension(5)->setRowHeight(26);

        for ($row = 6; $row <= $lastRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(22);
        }
        return [];
    }

}
