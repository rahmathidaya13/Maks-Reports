<?php

namespace App\Http\Controllers\Customer;

use App\Exports\CustomerExport;
use App\Http\Controllers\Controller;
use App\Models\CustomerModel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class CustomerExportController extends Controller
{
    public function export(Request $request)
    {
        $this->authorize('export', CustomerModel::class);

        // PDF butuh RAM besar untuk merender tabel panjang.
        // Kita set ke 1GB (1024M) atau unlimited (-1) dan
        // waktu eksekusi 5 menit.
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $query = CustomerModel::query()
            ->with(['creator', 'transactions'])
            ->where('created_by', auth()->id());

        // cek apakah ada data yang sedang dipilih
        if ($request->filled('all_id') && is_array($request['all_id'])) {
            $query->whereIn('customer_id', $request['all_id']);
        }

        // Cek apakah ada data yang ditemukan
        if ($query->count() === 0) {
            return back()->withErrors(['warning' => 'Tidak ada data pelanggan yang ditemukan untuk diekspor.']);
        }

        if ($request['format'] === 'excel') {
            if ($query->count() > 500) {
                return back()->withErrors(['error' => 'Data terlalu banyak untuk format Excel (>500). Silakan kurangi data yang akan diexport.']);
            }

            return Excel::download(new CustomerExport($query), 'Laporan_Pelanggan_' . now()->format('Ymd_His') . '.xlsx');
        }
        if ($request['format'] === 'pdf') {
            if ($query->count() > 500) {
                return back()->withErrors(['error' => 'Data terlalu banyak untuk format PDF (>500). Silakan kurangi data yang akan diexport.']);
            }

            $pdf = Pdf::loadView('exports.customers_export', [
                'customers' => $query->get(),
                'logo' => public_path('storage/logo/logo.jpg')
            ]);
            return $pdf->setPaper('a4', 'landscape')
                ->setWarnings(false)
                ->stream('Daftar_Pelanggan_' . now()->format('Ymd_His') . '.pdf');
        }

        return back()->withErrors(['error' => 'Format export tidak valid.']);
    }
}
