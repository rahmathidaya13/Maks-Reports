<?php

namespace App\Repositories;

use App\Models\ProductModel;
use App\Models\BranchesModel;
use App\Models\TransactionModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminDashboardRepository extends BaseCacheRepository
{
    protected string $cachePrefix = 'dashboard_cache_admin_';
    protected function getData(array $filters = []): LengthAwarePaginator
    {
        throw new \LogicException('Dashboard does not support pagination.');
    }
    public function getDashboard($userId, $startOfMonth, $endOfMonth): array
    {
        $filters = compact('startOfMonth', 'endOfMonth');
        $cacheKey = $this->buildCacheKey($userId, $filters);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        $lock = Cache::lock("lock_{$cacheKey}", $this->lockSeconds);
        try {
            $lock->block($this->lockWaitSeconds);
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }
            $data = $this->buildDashboardData($userId, $startOfMonth, $endOfMonth);
            Cache::put($cacheKey, $data, now()->addMinutes($this->cacheMinutes));
            $this->addKeyToUserList($userId, $cacheKey);
            return $data;
        } finally {
            optional($lock)->release();
        }
    }
    protected function buildDashboardData($userId,  $startOfMonth, $endOfMonth)
    {
        // Total Penjualan Keseluruhan Bulan Ini (Lunas + DP)
        $totalSales = TransactionModel::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Total Uang Masuk (Lunas)
        $totalRevenueLunas = TransactionModel::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'repayment') // Sesuaikan nama kolom status Anda
            ->sum('grand_total');

        // Total Uang Menggantung (DP / Belum Lunas)
        $totalRevenueBelumLunas = TransactionModel::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'payment')
            ->sum('grand_total');

        // Total Transaksi yang dibatalkan
        $totalRevenueBatal = TransactionModel::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'cancelled')
            ->sum('grand_total');

        // Top 5 Cabang Terbaik (Berdasarkan jumlah transaksi bulan ini)
        $topBranches = BranchesModel::withCount(['transactions' => function ($query) use ($startOfMonth, $endOfMonth) {
            // Jangan lupa tambahkan nama tabel agar tidak ambigu (contoh: transactions.created_at)
            $query->whereBetween('transactions.created_at', [$startOfMonth, $endOfMonth]);
        }])
            // 1. Hitung Total Lunas
            ->withSum(['transactions as total_lunas' => function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('transactions.created_at', [$startOfMonth, $endOfMonth])
                    ->where('transactions.status', 'repayment'); // Sesuaikan status
            }], 'grand_total')
            // 2. Hitung Total DP
            ->withSum(['transactions as total_dp' => function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('transactions.created_at', [$startOfMonth, $endOfMonth])
                    ->where('transactions.status', 'payment'); // Sesuaikan status
            }], 'grand_total')
            ->withSum(['transactions as total_batal' => function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('transactions.created_at', [$startOfMonth, $endOfMonth])
                    ->where('transactions.status', 'cancelled'); // Sesuaikan status
            }], 'grand_total')
            ->orderByDesc('transactions_count')
            ->take(10)
            ->get(['branches_id', 'name']); // Ambil nama cabang dan id-nya saja

        // Top 5 Produk Terlaris Bulan Ini
        // Asumsi: Transaksi berelasi dengan Product melalui transactions
        $topProducts = ProductModel::withSum(['transactions' => function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereHas('transactions', function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('transactions.created_at', [$startOfMonth, $endOfMonth]);
            });
        }], 'quantity')
            ->orderByDesc('transactions_sum_quantity')
            ->take(10)
            ->get(['product_id', 'name', 'image_path', 'image_link']);

        // DATA GRAFIK (Statistik Penjualan Harian 1 Minggu Terakhir)
        $now = now();
        $last7Days = $now->copy()->subDays(6)->startOfDay();
        $transactions = TransactionModel::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(transaction_id) as total_trx'),
            DB::raw('SUM(grand_total) as total_revenue')
        )
            ->where('created_at', '>=', $last7Days)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // MAPPING HARI KOSONG (Sangat Penting untuk Grafik)
        $dailySales = [];
        for ($i = 6; $i >= 0; $i--) {
            // Mundur dari 6 hari lalu sampai hari ini (0)
            $dateString = $now->copy()->subDays($i)->format('Y-m-d');

            // Cari apakah di tanggal ini ada transaksi?
            $trx = $transactions->firstWhere('date', $dateString);

            $dailySales[] = [
                'date' => $dateString,
                'total_trx' => $trx ? $trx->total_trx : 0,
                'total_revenue' => $trx ? (int) $trx->total_revenue : 0, // Pastikan integer
            ];
        }

        return [
            'widgets' => [
                'total_sales'   => $totalSales,
                'total_revenue' => $totalRevenueLunas,
                'total_revenue_dp' => $totalRevenueBelumLunas,
                'total_revenue_batal' => $totalRevenueBatal
            ],
            'leaderboards' => [
                'top_branches' => $topBranches,
                'top_products' => $topProducts,
            ],
            'charts' => [
                'daily_sales' => $dailySales
            ],
            'current_month' => $now->translatedFormat('F Y')
        ];
    }
}
