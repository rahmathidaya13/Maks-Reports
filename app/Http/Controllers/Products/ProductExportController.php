<?php

namespace App\Http\Controllers\Products;

use App\Models\ProductModel;
use Illuminate\Http\Request;
use App\Models\BranchesModel;
use App\Exports\ProductExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ProductPriceModel;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ProductExportController extends Controller
{
    public function export(Request $request)
    {
        // PDF butuh RAM besar untuk merender tabel panjang.
        // Kita set ke 1GB (1024M) atau unlimited (-1) dan waktu eksekusi 5 menit.
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        // salah seharus product price
        $query = ProductPriceModel::query()
            ->with(['creator', 'product', 'branch']);

        // Filter Kategori
        if ($request->filled('category')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category', $request['category']);
            });
        }

        // Filter Kondisi
        if ($request->filled('item_condition')) {
            // $query->where('item_condition', $request['item_condition']);
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('item_condition', $request['item_condition']);
            });
        }

        // Filter Cabang (Agak tricky karena relasinya di tabel product_prices)
        if ($request->filled('branch')) {
            $query->whereHas('branch', function ($q) use ($request) {
                $q->where('name', $request['branch']);
            });
        }

        if ($request['format'] === 'excel') {
            return Excel::download(new ProductExport($query), 'Daftar_Produk_' . now()->format('Ymd_His') . '.xlsx');
        }

        if ($request['format'] === 'pdf') {
            if ($query->count() > 1000) {
                return back()->withErrors(['error' => 'Data terlalu banyak untuk format PDF (>1000). Silakan Export menggunakan Excel.']);
            }
            $products = $query->get();
            $pdf = Pdf::loadView('exports.product_pdf', [
                'products' => $products,
                'title' => 'Daftar Produk ' . ($request['branch'] ? BranchesModel::where('name', $request['branch'])->first()->name : 'Semua Cabang'),
                'filter_branch' => $request['branch'] ? BranchesModel::where('name', $request['branch'])->first()->name : 'Semua Cabang',
                'logo' => public_path('storage/logo/logo.jpg')
            ]);
            return $pdf->setPaper('a4', 'landscape')
                ->setWarnings(false)
                ->stream('Daftar_Produk_' . now()->format('Ymd_His') . '.pdf');
        }
    }

    public function information(Request $request)
    {

        $product = ProductPriceModel::query()
            ->with(['creator', 'product', 'branch']);

        if ($request->filled('branch')) {
            $branch = $request->input('branch');
            $product->whereHas('branch', function ($q) use ($branch) {
                $q->where('name', $branch);
            });
        }
        if ($request->filled('category') || $request->filled('item_condition')) {
            $product->whereHas('product', function ($q) use ($request) {
                // Filter Kategori
                if ($request->filled('category')) {
                    $q->where('category', $request->input('category'));
                }

                // Filter Kondisi
                if ($request->filled('item_condition')) {
                    $q->where('item_condition', $request->input('item_condition'));
                }
            });
        }

        $total = $product->count();

        return response()->json([
            'status' => true,
            'total' => $total,
            'message' => 'Informasi berhasil dimuat'
        ]);
    }
}
