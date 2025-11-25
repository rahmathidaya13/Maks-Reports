<?php

namespace App\Exports;

use App\Models\StoryStatusReportModel;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StatusReportUpdate implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    // Jumlah kolom yang akan diekspor adalah 5 (A hingga E)
    public function collection()
    {
        // Mengambil data dan memetakan (map) untuk menyesuaikan urutan dan format
        return StoryStatusReportModel::with('creator')
            ->get()
            ->map(function ($report, $index) {
                return [
                    'No' => $index + 1,
                    'Nama' => $report->creator->name ?? 'N/A', // Mengganti 'created_by' (ID) dengan 'creator->name'
                    'Kode Status' => $report->report_code,
                    'Tanggal' => Carbon::parse($report->report_date)->translatedFormat('l, d-m-Y'),
                    'Jam' => Carbon::parse($report->report_time)->format('H:i'),
                    'Jumlah Status' => $report->count_status,
                ];
            });
    }

    public function headings(): array
    {
        $user = auth()->user();
        $branchName = $user->profile->branch->name ?? '-';
        $jobTitle = $user->profile->jobTitle->title ?? '-';

        $today = Carbon::now()->locale('id');
        return [
            ['LAPORAN HARIAN UPDATE STATUS'],
            ['PT. Toko Maksindo Cabang ' . ucwords($branchName)],
            ['Tanggal Laporan: ' . Carbon::now()->translatedFormat('l, d-m-Y') . ', ' . 'Minggu ke-' . $today->weekOfMonth],
            [],
            [
                'No',
                'Nama',
                'Kode Status',
                'Tanggal',
                'Jam',
                'Jumlah Status',
            ] // Header tabel (Baris 5)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // =====================================
        //  MERGE UNTUK HEADING UTAMA
        // =====================================
        foreach (range(1, 4) as $row) {
            $sheet->mergeCells("A{$row}:F{$row}");
        }

        // Judul utama (A1)
        $sheet->getStyle('A1')->applyFromArray([
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
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Sub Judul (A2 & A3)
        $sheet->getStyle('A2:A4')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'name' => 'Calibri',
                'color' => ['argb' => 'ff000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'ffffffff'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
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
        $sheet->getStyle('A5:F5')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'], // putih
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'ff2d7244'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // ==============================
        // 📄 ISI DATA
        // ==============================
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle("A6:F$lastRow")->applyFromArray([
            'font' => [
                'size' => 12,
                'name' => 'Calibri'
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF000000'],
                ],
            ],
        ]);


        // ==============================
        // 🔢 TOTAL JUMLAH STATUS
        // ==============================

        // Hitung total jumlah status
        $total = StoryStatusReportModel::sum('count_status');

        // Baris total berada setelah data terakhir
        $totalRow = $lastRow + 1;

        // Tulis label TOTAL (A–D)
        $sheet->setCellValue("E$totalRow", "Total");
        // $sheet->mergeCells("A$totalRow:D$totalRow");

        // Tulis totalnya (kolom E)
        $sheet->setCellValue("F$totalRow", $total);

        // Styling baris total
        $sheet->getStyle("E$totalRow:F$totalRow")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['argb' => 'ff000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFFFFF'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        $sheet->getRowDimension($totalRow)->setRowHeight(25);



        // 🌫 Zebra striping
        for ($row = 6; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle("A$row:F$row")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'ebebebfa'], // putih keabu lembut
                    ],
                ]);
            }
        }

        // =====================================
        // TANDA TANGAN (TTD)
        // =====================================

        $ttdStart = $totalRow + 3;
        // Tanggal + Kota
        $sheet->getStyle("D{$ttdStart}")->applyFromArray([
            'font' => [
                'bold' => false,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Label: Dibuat oleh - Disetujui oleh
        $ttdRow1 = $ttdStart + 2;

        $sheet->setCellValue("D{$ttdRow1}", "Dibuat oleh,");
        $sheet->setCellValue("F{$ttdRow1}", "Disetujui oleh,");

        $sheet->getStyle("D{$ttdRow1}:F{$ttdRow1}")->applyFromArray([
            'font' => [
                'bold' => false,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Ruang untuk tanda tangan (kosong)
        $ttdRow2 = $ttdRow1 + 3;

        // Nama user dan manager
        $sheet->setCellValue("D{$ttdRow2}", "(" . auth()->user()->name . ")");
        $sheet->setCellValue("F{$ttdRow2}", "");

        $sheet->getStyle("D{$ttdRow2}:F{$ttdRow2}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle("D{$ttdRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F{$ttdRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);



        // Tinggi baris untuk header dan isi data
        $sheet->getRowDimension(5)->setRowHeight(26);

        for ($row = 6; $row <= $lastRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(22);
        }
        return [];
    }
}
