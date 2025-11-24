<?php

namespace App\Exports;

use App\Models\StoryStatusReportModel;
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
    const LAST_COLUMN = 'E';
    public function collection()
    {
        // Mengambil data dan memetakan (map) untuk menyesuaikan urutan dan format
        return StoryStatusReportModel::with('creator')
            ->get()
            ->map(function ($report) {
                return [
                    // Mengganti 'created_by' (ID) dengan 'creator->name'
                    'Nama' => $report->creator->name ?? 'N/A',
                    'Kode Status' => $report->report_code,
                    'Tanggal' => $report->report_date,
                    'Jam' => $report->report_time,
                    'Jumlah Status' => $report->count_status,
                ];
            });
    }

    public function headings(): array
    {
        return [
            ['LAPORAN HARIAN UPDATE STATUS'],
            ['PT. Toko Maksindo Cabang'],
            ['Tanggal: ' . now()->translatedFormat('d F Y')],
            [], // Baris kosong (Baris 4)
            [
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
        // 1. Merge cell untuk Judul dan Subjudul (Hanya sampai kolom terakhir data: E)
        $sheet->mergeCells('A1:' . self::LAST_COLUMN . '1');
        $sheet->mergeCells('A2:' . self::LAST_COLUMN . '2');
        $sheet->mergeCells('A3:' . self::LAST_COLUMN . '3'); // Merge baris tanggal juga

        // 2. Styling Judul utama (A1)
        $sheet->getStyle('A1')->applyFromArray([
            // ... (style yang sama) ...
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 3. Styling Sub Judul (A2 & A3)
        $sheet->getStyle('A2:' . self::LAST_COLUMN . '3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'name' => 'Calibri'
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Tinggi baris judul
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(20); // Tambahkan tinggi untuk baris tanggal

        // 4. Styling Header tabel (Baris 5)
        $sheet->getStyle('A5:' . self::LAST_COLUMN . '5')->applyFromArray([ // Diubah ke A5:E5
            'font' => [
                'bold' => true,
                'color' => ['argb' => '000000'],
                'size' => 12,
                // ...
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => '87ff84'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(25);


        // 5. Baris isi data (mulai dari baris 6 sampai akhir)
        $firstDataRow = 6; // Baris data dimulai setelah 5 baris header/judul
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle("A$firstDataRow:" . self::LAST_COLUMN . "$lastRow")->applyFromArray([
            'font' => [
                'size' => 12,
                'name' => 'Calibri',
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

        // 6. Zebra striping
        for ($row = $firstDataRow; $row <= $lastRow; $row++) {
            if ($row % 2 != 0) {
                $sheet->getStyle("A$row:" . self::LAST_COLUMN . "$row")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'f1f1f1'],
                    ],
                ]);
            }
            $sheet->getRowDimension($row)->setRowHeight(20);
        }

        return [];
    }
}
