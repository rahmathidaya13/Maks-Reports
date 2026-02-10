<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class CustomerExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $query;
    private $rowNumber = 0;
    public function __construct($query)
    {
        $this->query = $query;
    }
    public function query()
    {
        return $this->query;
    }
    public function headings(): array
    {
        $userName = auth()->user()->name ?? '-';
        $branchName = auth()->user()->profile->branch->name ?? '-';
        return [
            ['DATA PELANGGAN'], // Baris 1: Judul Besar
            ['Cabang: ' . strtoupper($branchName)], // Baris 2: Cabang
            ['Dicetak Oleh: ' . ucfirst($userName) . ' | Tgl: ' . now()->format('d/m/Y H:i')], // Baris 3: Info Detail
            [''], // Baris 4: Kosong (Spacer)
            [
                'No',
                'ID Pelanggan',
                'NIK (KTP)',
                'Nama Pelanggan',
                'No. Telepon',
                'Jenis Usaha',
                'Lokasi (Kota/Prov)',
                'Alamat Lengkap',
            ]
        ];
    }
    public function map($customer): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            substr($customer->customer_id, 0, 8),
            $customer->national_id_number ? "'" . $customer->national_id_number : '-', // Kasih tanda petik biar Excel baca sbg Text (bukan angka ilmiah)
            ucwords($customer->customer_name),
            $customer->number_phone_customer,
            ucwords($customer->type_bussiness),
            ucwords($customer->city . ', ' . $customer->province), // Gabung Kota & Prov
            $customer->address,
        ];
    }
    public function styles(Worksheet $sheet)
    {
        // 1. Style Header Laporan (Baris 1-3)
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 20,
                'color' => ['argb' => 'FF2C3E50']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'ffffffff'],
            ],
        ]);

        // B. Style Info Header (Baris 2 & 3)
        $sheet->getStyle('A2:A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'ffffffff'],
            ],
        ]);

        $sheet->getRowDimension(5)->setRowHeight(25); // Header agak tinggi
        // 2. Style Header Tabel (Baris 5)
        $sheet->getStyle('A5:H5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 12, 'name' => 'Calibri'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2C3E50']],
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

        // 3. Style Body Data
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 5) {
            $sheet->getStyle("A6:H$lastRow")->applyFromArray([
                'font' => ['size' => 11, 'name' => 'Calibri'],
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true], // Wrap text penting untuk Alamat
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);

            // Rata Tengah untuk No, NIK, Tgl
            $sheet->getStyle("A6:B$lastRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H6:H$lastRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Atur Lebar Kolom Manual untuk Alamat biar tidak terlalu lebar/sempit
        $sheet->getColumnDimension('G')->setWidth(50); // Kolom Alamat

        for ($row = 6; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle("A$row:H$row")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF2F2F2'], // Abu-abu sangat muda
                    ],
                ]);
            }
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->mergeCells('A1:H1');
                $event->sheet->mergeCells('A2:H2');
                $event->sheet->mergeCells('A3:H3');
                $event->sheet->mergeCells('A4:H4');


                $lastRow = $event->sheet->getHighestRow();

                // Cek Data Kosong (Jika baris terakhir adalah 5, berarti cuma header doang)
                if ($lastRow === 5) {
                    $event->sheet->setCellValue('A6', 'DATA TIDAK TERSEDIA');
                    $event->sheet->mergeCells('A6:H6'); // Merge sampai kolom H
                    $event->sheet->getRowDimension(6)->setRowHeight(30);

                    $event->sheet->getStyle('A6')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'italic' => true,
                            'color' => ['argb' => 'FFE74C3C'], // Merah
                            'size' => 12,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFFEBEB'], // Background Merah Muda
                        ],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);
                }
            },
        ];
    }
}
