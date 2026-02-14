<?php

namespace App\Exports;


use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ProductExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
        return [
            'No',
            'ID Produk',
            'Nama Produk',
            'Kategori',
            'Cabang',
            'Harga Asli',
            'Diskon',
            'Harga Diskon',
            'Kondisi Produk',
            'Tanggal Berlaku',
            'Tanggal Berakhir',
            'Tipe Harga',
            'Status Publikasi',
        ];
    }
    private function getPriceAttributes($price)
    {
        $finalPrice = $price->base_price;
        if ($price->price_type === 'discount') {
            $finalPrice = $price->discount_price;
        } else {
            $finalPrice = 0;
        }

        $discountInfo = 0;
        if ($price->price_type === 'discount' && $price->base_price > 0) {
            $persen = round((($price->base_price - $price->discount_price) / $price->base_price) * 100);
            $discountInfo = $persen;
        }

        return [
            'price_origin' => "Rp " . number_format($price->base_price, 0, ',', '.'),
            'price_discount' => "Rp " . number_format($finalPrice, 0, ',', '.'),
            'discount_info' => $discountInfo > 0 ? $discountInfo . '%' : '0%',
            'price_type'    => $price->price_type == 'discount' ? 'Diskon' : 'Normal',
            'status'     => $price->status,
            'valid_from' => $price->valid_from ? Carbon::parse($price->valid_from)->format('d/m/Y') : "Efektif",
            'valid_until' => $price->valid_until ? Carbon::parse($price->valid_until)->format('d/m/Y') : "Seterusnya",
        ];
    }
    // Mengatur Isi Data per Baris
    public function map($productPrices): array
    {
        $this->rowNumber++;
        $priceData = $this->getPriceAttributes($productPrices);
        return [
            $this->rowNumber,
            substr(str_replace('-', '', $productPrices->product?->product_id), 0, 11), // ID
            ucwords($productPrices->product?->name ?? '-'),       // Nama
            ucwords(str_replace('-', ' ', $productPrices->product?->category)),   // Kategori
            ucwords($productPrices->branch?->name ?? 'Pusat'),   // cabang
            $priceData['price_origin'],
            $priceData['discount_info'],
            $priceData['price_discount'],
            ucwords($productPrices->product?->item_condition ?? '-'), // Kondisi
            $priceData['valid_from'],
            $priceData['valid_until'],
            ucwords($priceData['price_type']),
            ucwords($priceData['status']),
        ];
    }
    // Styling Excel (Biar Rapi & Cantik)
    public function styles(Worksheet $sheet)
    {
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(18);

        // header utama style
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'], // putih
                'size' => 12,
                'name' => 'Calibri'
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF2C3E50'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // isi data style
        $lastRow = $sheet->getHighestRow();
        // Langkah A: Semua Kolom di Rata Tengah
        if ($lastRow  > 1) {
            $sheet->getStyle("A2:M$lastRow")->applyFromArray([
                'font' => [
                    'size' => 12,
                    'name' => 'Calibri'
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                    'shrinkToFit' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ]);
        }

        // Langkah B: TIMPA Kolom B menjadi Rata Kiri (Pinggir)
        // Perhatikan range-nya: "B2:B$lastRow" (Dari B2 sampai B paling bawah)
        $sheet->getStyle("C2:C$lastRow")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT, // Paksa jadi Kiri
                'vertical' => Alignment::VERTICAL_CENTER, // Tetap vertikal tengah agar rapi
            ],
        ]);

        if ($lastRow > 2) {
            for ($row = 2; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle("A$row:M$row")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'ebebebfa'],
                        ],
                    ]);
                }
            }
        }
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // 1. Cek berapa baris yang terisi
                $lastRow = $event->sheet->getHighestRow();
                // dd($lastRow);
                // 2. Jika baris cuma 1, berarti isinya cuma HEADER doang (Data Kosong)
                if ($lastRow === 1) {

                    // Ambil kolom terakhir (misal 'I' atau 'L')
                    $lastColumn = $event->sheet->getHighestColumn();

                    // 3. Tulis Pesan di Cell A2
                    $event->sheet->setCellValue('A2', 'DATA TIDAK TERSEDIA');

                    // 4. Merge Cells dari A2 sampai kolom terakhir (Biar rata tengah cantik)
                    $event->sheet->mergeCells('A2:' . $lastColumn . '2');

                    // 5. Styling Pesan Error-nya
                    $event->sheet->getStyle('A2')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'italic' => true,
                            'color' => ['argb' => 'FFE74C3C'], // Warna Merah
                            'size' => 12,
                            'name' => 'Calibri'
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFFEEEE'], // Background Merah Muda sangat soft
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FF000000'],
                            ],
                        ],
                    ]);

                    // Opsional: Set tinggi baris pesan biar agak lega
                    $event->sheet->getRowDimension(2)->setRowHeight(30);
                }
            },
        ];
    }
}
