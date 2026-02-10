<?php

namespace App\Repositories;

use App\Models\TransactionItemModel;
use Carbon\Carbon;
use App\Models\TransactionModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;


class DashboardRepository extends BaseCacheRepository
{
    protected string $cachePrefix = 'dashboard_cache_';
    protected function getData(array $filters = []): LengthAwarePaginator
    {
        throw new \LogicException('Dashboard does not support pagination.');
    }
    public function getDashboard($userId, int $month, int $year): array
    {
        $filters = compact('month', 'year');
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
            $data = $this->buildDashboardData($userId, $month, $year);
            Cache::put($cacheKey, $data, now()->addMinutes($this->cacheMinutes));
            $this->addKeyToUserList($userId, $cacheKey);
            return $data;
        } finally {
            optional($lock)->release();
        }
    }
    protected function buildDashboardData($userId, int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate   = (clone $startDate)->endOfMonth();

        $lastMonthStart = (clone $startDate)->subMonth();
        $lastMonthEnd   = (clone $lastMonthStart)->endOfMonth();
        /**
         * ======================================================
         * TRANSAKSI LUNAS (HANYA 2 BULAN TERKAIT)
         * ======================================================
         */
        $transactions = TransactionModel::query()
            ->with(['items.product', 'customer', 'creator', 'payments'])
            ->where('created_by', $userId)
            ->where('status', '!=', 'cancelled')
            ->whereNull('cancelled_at')
            ->whereBetween('transaction_date', [$lastMonthStart, $endDate])
            ->withSum('payments', 'amount')
            ->withMax('payments', 'payment_date')
            ->get();

        /**
         * ======================================================
         * FILTER LUNAS BULAN INI & BULAN LALU
         * ======================================================
         */
        $isPaid = fn($trx, $status = 'repayment') =>
        $trx->status === $status &&
            ($trx->payments_sum_amount >= $trx->grand_total);

        $completedThisMonth = $transactions->filter(
            fn($trx) =>
            $isPaid($trx) &&
                Carbon::parse($trx->transaction_date)->between($startDate, $endDate)
        );

        $completedLastMonth = $transactions->filter(
            fn($trx) =>
            $isPaid($trx) &&
                Carbon::parse($trx->transaction_date)->between($lastMonthStart, $lastMonthEnd)
        );

        /**
         * ======================================================
         * TRANSAKSI DP / BELUM LUNAS
         * ======================================================
         */

        $dpThisMonth = $transactions->filter(
            fn($trx) =>
            $trx->status === 'payment' && // Status harus payment (bukan cancelled)
                Carbon::parse($trx->transaction_date)->between($startDate, $endDate)
        );

        /**
         * ======================================================
         * HITUNG METRIK UTAMA
         * ======================================================
         */
        $totalSales     = $completedThisMonth->sum('grand_total');
        $lastMonthSales = $completedLastMonth->sum('grand_total');

        // hitung GROWTH
        if ($lastMonthSales > 0) {
            $growth = (($totalSales - $lastMonthSales) / $lastMonthSales) * 100;
        } else {
            $growth = $totalSales > 0 ? 100 : 0;
        }

        // Hitung Total Item Terjual yang lunas dan masih dp atau belum lunas (Quantity)
        $productsSoldQty = $completedThisMonth->sum(function ($trx) {
            return $trx->items->sum('quantity');
        });
        $productsSoldQtyDp = $dpThisMonth->sum(function ($trx) {
            return $trx->items->sum('quantity');
        });

        /**
         * ======================================================
         * CHART HARIAN
         * ======================================================
         */
        $dailyStats = TransactionModel::query()
            ->with(['items.product', 'customer', 'creator', 'payments'])
            ->where('created_by', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE(transaction_date) as date, SUM(grand_total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        /**
         * ======================================================
         * TOP PRODUCTS
         * ======================================================
         */

        $topProducts = TransactionItemModel::query()
            ->with(['creator', 'transactions', 'product'])
            ->select('product_id', DB::raw('SUM(quantity) as total'))

            // 2. FILTER: Cek kondisi di tabel 'Parent' (Transaction)
            ->whereHas('transactions', function ($query) use ($userId, $startDate, $endDate) {
                $query->where('created_by', $userId)
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->where('status', '!=', 'cancelled');
            })
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'name'  => $item->product->name ?? 'Produk Terhapus',
                'total' => (int) $item->total,
            ]);

        return [
            'stats' => [
                'bonus' => round($totalSales * 0.01, 2),
                'sales_volume' => $totalSales,
                'products_sold' => $productsSoldQty,
                'products_sold_dp' => $productsSoldQtyDp,
                'growth' => round($growth, 1),
                'current_month_name' => $startDate->translatedFormat('F Y'),
                'transactions_count_inv' => $completedThisMonth->count(),
                'transactions_count_dp_inv' => $dpThisMonth->count(),
                'remaining_payment' => $dpThisMonth->sum(fn($trx) =>
                $trx->grand_total - $trx->payments_sum_amount),
            ],
            'chart_data' => [
                'labels' => $dailyStats->pluck('date'),
                'values' => $dailyStats->pluck('total'),
            ],
            'top_products' => $topProducts,
        ];
    }
}
