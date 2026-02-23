<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BranchesModel;
use App\Models\ProductModel;
use App\Models\TransactionModel;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Total Penjualan Keseluruhan Bulan Ini (Lunas + DP)
        $totalSales = TransactionModel::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Total Uang Masuk (Lunas)
        $totalRevenueLunas = TransactionModel::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'repayment') // Sesuaikan nama kolom status Anda
            ->sum('grand_total'); // Sesuaikan nama kolom total harga

        // Total Uang Menggantung (DP / Belum Lunas)
        $totalRevenueBelumLunas = TransactionModel::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'payment')
            ->sum('grand_total'); // Asumsi ada kolom sisa pembayaran

        // Top 5 Cabang Terbaik (Berdasarkan jumlah transaksi bulan ini)
        $topBranches = BranchesModel::withCount(['transactions' => function ($query) use ($startOfMonth, $endOfMonth) {
            // Jangan lupa tambahkan nama tabel agar tidak ambigu (contoh: transactions.created_at)
            $query->whereBetween('transactions.created_at', [$startOfMonth, $endOfMonth]);
        }])
            ->orderByDesc('transactions_count')
            ->take(5)
            ->get(['branches_id', 'name']); // Ambil nama cabang dan id-nya saja

        // Top 5 Produk Terlaris Bulan Ini
        // Asumsi: Transaksi berelasi dengan Product melalui transactions
        $topProducts = ProductModel::withSum(['transactions' => function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereHas('transactions', function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('transactions.created_at', [$startOfMonth, $endOfMonth]);
            });
        }], 'quantity')
            ->orderByDesc('transactions_sum_quantity')
            ->take(5)
            ->get(['product_id', 'name', 'transactions_sum_quantity', 'image_path', 'image_link']);

        return Inertia::render(
            'Admin/Index',
            [
                'widgets' => [
                    'total_sales'   => $totalSales,
                    'total_revenue' => $totalRevenueLunas,
                    'total_revenue_dp' => $totalRevenueBelumLunas
                ],
                'leaderboards' => [
                    'top_branches' => $topBranches,
                    'top_products' => $topProducts,
                ],
            ]
        );
    }
}
