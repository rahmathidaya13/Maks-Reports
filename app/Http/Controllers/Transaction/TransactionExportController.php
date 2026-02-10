<?php

namespace App\Http\Controllers\Transaction;

use App\Exports\TransactionExport;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use App\Models\BranchesModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\TransactionModel;
use App\Http\Controllers\Controller;

class TransactionExportController extends Controller
{
    public function export(Request $request)
    {
        $this->authorize('export', TransactionModel::class);

        // PDF butuh RAM besar untuk merender tabel panjang.
        // Kita set ke 1GB (1024M) atau unlimited (-1) dan
        // waktu eksekusi 5 menit.
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
        $query = TransactionModel::query()
            ->with(['creator', 'customer', 'items.product', 'payments'])
            ->where('created_by', auth()->id());

        // cek apakah ada data yang sedang dipilih
        if ($request->filled('all_id') && is_array($request['all_id'])) {
            $query->whereIn('transaction_id', $request['all_id']);
        }

        // Cek apakah ada data yang ditemukan
        if ($query->count() === 0) {
            return back()->withErrors(['error' => 'Tidak ada data transaksi yang ditemukan untuk diekspor.']);
        }
        // cek apakah formatnya excel atau pdf
        if ($request['format'] === 'excel') {
            if ($query->count() > 500) {
                return back()->withErrors(['error' => 'Data terlalu banyak untuk format Excel (>500). Silakan kurangi data yang akan diexport.']);
            }
            return Excel::download(new TransactionExport($query), 'Laporan_Transaksi_' . now()->format('Ymd_His') . '.xlsx');
        }

        if ($request['format'] === 'pdf') {
            if ($query->count() > 500) {
                return back()->withErrors(['error' => 'Data terlalu banyak untuk format PDF (>500). silakan kurangi data yang akan diexport.']);
            }
            $transaction = $query->get();
            $pdf = Pdf::loadView('exports.transaction_pdf', [
                'transactions' => $transaction,
                'logo' => public_path('storage/logo/logo.jpg')
            ]);
            return $pdf->setPaper('a4', 'landscape')
                ->setWarnings(false)
                ->stream('Laporan_Transaksi_' . now()->format('Ymd_His') . '.pdf');
        }
        return back()->withErrors(['error' => 'Format export tidak valid.']);
    }
}
