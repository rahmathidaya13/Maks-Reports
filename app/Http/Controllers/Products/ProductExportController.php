<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\BranchesModel;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ProductExportController extends Controller
{
    public function export(Request $request)
    {
        // PDF butuh RAM besar untuk merender tabel panjang.
        // Kita set ke 1GB (1024M) atau unlimited (-1) dan waktu eksekusi 5 menit.
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
        $query = ProductModel::query()->with(['creator', 'prices']);

        // Filter Kategori
        if ($request->filled('category')) {
            $query->where('category', $request['category']);
        }

        // Filter Kondisi
        if ($request->filled('item_condition')) {
            $query->where('item_condition', $request['item_condition']);
        }

        // Filter Cabang (Agak tricky karena relasinya di tabel product_prices)
        if ($request->filled('branches_id')) {
            // Hanya ambil produk yang punya harga di cabang tersebut
            $query->whereHas('prices', function ($q) use ($request) {
                $q->where('branch_id', $request['branches_id']);
            });

            // Opsional: Batasi eager loading prices hanya ke cabang tersebut agar data bersih
            $query->with(['prices' => function ($q) use ($request) {
                $q->where('branch_id', $request['branches_id']);
            }]);
        }

        $products = $query->get();
        // if ($request['format'] === 'excel') {
        //     return Excel::download(new ProductsExport($products), 'laporan_produk.xlsx');
        // }

        if ($request['format'] === 'pdf') {
            // if ($query->count() > 1000) {
            //     return back()->with('error', 'Data terlalu banyak untuk format PDF (>1000). Silakan Export menggunakan Excel.');
            // }
            $pdf = Pdf::loadView('exports.product_pdf', [
                'products' => $products,
                'filter_branch' => $request['branches_id'] ? BranchesModel::find($request['branches_id'])->name : 'Semua Cabang'
            ]);
            return $pdf->setPaper('a4', 'landscape')
                ->setWarnings(false)
                ->stream('laporan_produk.pdf');
        }
    }
}
