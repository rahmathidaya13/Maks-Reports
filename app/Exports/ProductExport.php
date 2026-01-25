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

class ProductExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $query;
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
            'ID Produk',
            'Nama Produk',
            'Kategori',
            'Cabang',
            'Harga',
            'Kondisi Produk',
            'Tanggal Berlaku',
            'Tanggal Berakhir',
            'Tipe Harga',
            'Status Publikasi',
        ];
    }

    // Mengatur Isi Data per Baris
    public function map($product): array
    {
        // LOGIKA FORMAT HARGA (Mirip seperti di PDF, tapi versi teks)
        // Kita gabungkan semua harga cabang jadi satu string dengan enter (\n)
        $priceDetails = $product->prices->map(function ($price) {
            $branchName = $price->branch->name ?? 'Pusat';

            // Format Rupiah
            $base = number_format($price->base_price, 0, ',', '.');

            if ($price->price_type === 'discount') {
                $disc = number_format($price->discount_price, 0, ',', '.');
                // Hitung Persen
                $persen = 0;
                if ($price->base_price > 0) {
                    $persen = round((($price->base_price - $price->discount_price) / $price->base_price) * 100);
                }

                // Output: [Cabang A] Rp 90.000 (Disc 10% dari Rp 100.000)
                return "[{$branchName}] Rp {$disc} (Disc {$persen}% dr Rp {$base})";
            } else {
                // Output: [Cabang A] Rp 100.000
                return "Rp {$base}";
            }
        })->implode("\n"); // Gabungkan dengan Enter

        // Jika tidak ada harga
        if (empty($priceDetails)) {
            $priceDetails = "Belum ada harga";
        }

        return [
            substr(str_replace('-', '', $product->product_id), 0, 11), // ID
            ucwords($product->name),       // Nama
            ucwords(str_replace('-', ' ', $product->category)),   // Kategori
            ucwords($product->prices->first()->branch?->name),   // cabang
            $priceDetails,        // Harga (Multi-line)
            strtoupper($product->item_condition), // Kondisi (NEW/USED)
            Carbon::parse($product->valid_from)->format('d M y'),
            Carbon::parse($product->valid_until)->format('d M y'),
            ucwords($product->price_type),
            ucwords($product->status),

        ];
    }
    // Styling Excel (Biar Rapi & Cantik)
    public function styles(Worksheet $sheet)
    {
        return [
            // Baris 1 (Header): Bold, Putih, Background Biru Gelap
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2C3E50'], // Midnight Blue (sama kayak PDF)
                ],
                'alignment' => ['horizontal' => 'center'],
            ],

            // Kolom E (Detail Harga): Aktifkan Wrap Text (Biar enter-nya kebaca)
            'E' => [
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_TOP
                ],
            ],

            // Semua Sel: Vertical Top biar rapi
            'A:G' => [
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP],
            ]
        ];
    }
}
