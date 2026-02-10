<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class TransactionExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
            ['LAPORAN TRANSAKSI'], // Baris 1: Judul Besar
            ['Cabang: ' . strtoupper($branchName)], // Baris 2: Cabang
            ['Dicetak Oleh: ' . ucfirst($userName) . ' | Tgl: ' . now()->format('d/m/Y H:i')], // Baris 3: Info Detail
            [''], // Baris 4: Kosong (Spacer)
            [
                'No',
                'Invoice',
                'Tanggal Transaksi',
                'Pelanggan',
                'Detail Item Belanja',
                'Riwayat Pembayaran',
                'Total Tagihan',
                'Status',
            ]
        ];
    }
    public function map($transaction): array
    {
        $this->rowNumber++;

        // 1. Format List Item (Menggunakan \n untuk baris baru dalam cell Excel)
        $itemsList = $transaction->items->map(function ($item) {
            $productName = $item->product->name ?? 'Produk Dihapus';
            return "- {$productName} (x{$item->quantity})";
        })->implode("\n");

        // 2. Format History Pembayaran
        $paymentList = "-";
        if ($transaction->payments->count() > 0) {
            $paymentList = $transaction->payments->map(function ($pay) {
                $date = Carbon::parse($pay->created_at)->format('d/m/Y H:i');
                $amount = number_format($pay->amount, 0, ',', '.');
                return "{$date}:  Rp {$amount}";
            })->implode("\n");
        }
        $customerInfo = "Pelanggan Umum"; // Default jika customer null (dihapus/guest)

        if ($transaction->customer) {
            // Kita tampung dalam array agar mudah digabung
            $details = [];

            // Nama
            $details[] = '-' . $transaction->customer->customer_name;

            // No HP (Cek jika ada)
            if (!empty($transaction->customer->number_phone_customer)) {
                $details[] = '-' . $transaction->customer->number_phone_customer;
            }

            // Alamat (Cek jika ada)
            if (!empty($transaction->customer->address)) {
                $details[] = '-' . $transaction->customer->address;
            }

            // Gabungkan dengan Enter (\n)
            $customerInfo = implode("\n",  $details);
        }

        // 3. Translate Status
        $statusLabel = match ($transaction->status) {
            'repayment' => 'Lunas',
            'cancelled' => 'Dibatalkan',
            'payment'   => 'Belum Lunas',
            default     => 'Unknown'
        };

        return [
            $this->rowNumber,
            "'" . $transaction->invoice,
            Carbon::parse($transaction->created_at)->format('d/m/Y'),
            $customerInfo,
            $itemsList ?: 'Tidak ada item',
            $paymentList,
            "Rp " . number_format($transaction->grand_total, 0, ',', '.'),
            $statusLabel,
        ];
    }
    public function styles(Worksheet $sheet)
    {
        // A. Style Judul Laporan (Baris 1)
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
        // 1. HEADER STYLE (A1 sampai H5)
        $sheet->getStyle('A5:H5')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'], // Putih
                'size' => 12,
                'name' => 'Calibri'
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF2C3E50'], // Warna Biru Tua Slate
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

        $lastRow = $sheet->getHighestRow();

        if ($lastRow > 5) {
            // 2. DEFAULT STYLE UNTUK SEMUA DATA (Rata Tengah & Vertikal Atas)
            $sheet->getStyle("A6:H$lastRow")->applyFromArray([
                'font' => ['size' => 11, 'name' => 'Calibri'],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER, // Penting biar rapi kalau item banyak
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'wrapText' => true, // Penting agar \n terbaca sebagai enter
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ]);

            // 3. OVERRIDE ALIGNMENT KOLOM TERTENTU

            // Kolom B (Invoice), D (Item), E (Payment) -> Rata Kiri
            $sheet->getStyle("D6:D$lastRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("E6:E$lastRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // 4. ZEBRA STRIPING (Warna selang-seling)
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
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                // Merge Baris 1, 2, dan 3 dari Kolom A sampai H
                $event->sheet->mergeCells('A1:H1'); // Judul Besar
                $event->sheet->mergeCells('A2:H2'); // Cabang
                $event->sheet->mergeCells('A3:H3'); // Info User & Tanggal
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
