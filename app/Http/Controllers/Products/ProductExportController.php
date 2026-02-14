<?php

namespace App\Http\Controllers\Products;

use Illuminate\Http\Request;
use App\Models\BranchesModel;
use App\Exports\ProductExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ProductPriceModel;
use App\Http\Controllers\Controller;
use App\Models\ProductModel;
use Maatwebsite\Excel\Facades\Excel;

class ProductExportController extends Controller
{
    public function export(Request $request)
    {
        $this->authorize('export', ProductModel::class);
        // PDF butuh RAM besar untuk merender tabel panjang.
        // Kita set ke 1GB (1024M) atau unlimited (-1) dan waktu eksekusi 5 menit.
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        // salah seharus product price
        $query = ProductPriceModel::query()
            ->with(['creator', 'product', 'branch']);


        // cek apakah ada data yang sedang dipilih
        if ($request->filled('all_id') && is_array($request['all_id'])) {
            $query->whereIn('product_price_id', $request['all_id']);
        }

        // Cek apakah ada data yang ditemukan
        if ($query->count() === 0) {
            return back()->withErrors(['warning' => 'Tidak ada data produk yang ditemukan untuk diekspor.']);
        }

        if ($request['format'] === 'excel') {
            if ($query->count() > 500) {
                return back()->withErrors(['error' => 'Data terlalu banyak untuk format Excel (>500). Silakan kurangi data yang akan diexport.']);
            }

            return Excel::download(new ProductExport($query), 'Daftar_Produk_' . now()->format('Ymd_His') . '.xlsx');
        }

        if ($request['format'] === 'pdf') {
            if ($query->count() > 500) {
                return back()->withErrors(['error' => 'Data terlalu banyak untuk format PDF (>500). Silakan Export menggunakan Excel.']);
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
        return back()->withErrors(['error' => 'Format export tidak valid.']);
    }

    public function information(Request $request)
    {
        $this->authorize('export', ProductModel::class);
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
