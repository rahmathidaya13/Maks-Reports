<?php

namespace App\Repositories;

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
        $lastMonthEnd   = (clone $endDate)->subMonth();
        /**
         * ======================================================
         * TRANSAKSI LUNAS (HANYA 2 BULAN TERKAIT)
         * ======================================================
         */
        $transactions = TransactionModel::query()
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
        $completedThisMonth = $transactions->filter(
            fn($trx) =>
            $trx->payments_sum_amount >= $trx->price_final &&
                Carbon::parse($trx->payments_max_payment_date)
                ->between($startDate, $endDate)
        );

        $completedLastMonth = $transactions->filter(
            fn($trx) =>
            $trx->payments_sum_amount >= $trx->price_final &&
                Carbon::parse($trx->payments_max_payment_date)
                ->between($lastMonthStart, $lastMonthEnd)
        );

        /**
         * ======================================================
         * TRANSAKSI DP / BELUM LUNAS
         * ======================================================
         */

        $dpThisMonth = $transactions->filter(
            fn($trx) =>
            $trx->payments_sum_amount > 0 &&
                $trx->payments_sum_amount < $trx->price_final &&
                Carbon::parse($trx->transaction_date)
                ->between($startDate, $endDate)
        );

        /**
         * ======================================================
         * HITUNG METRIK UTAMA
         * ======================================================
         */
        $totalSales     = $completedThisMonth->sum('price_final');
        $lastMonthSales = $completedLastMonth->sum('price_final');

        // hitung GROWTH
        $growth = $lastMonthSales > 0
            ? (($totalSales - $lastMonthSales) / $lastMonthSales) * 100
            : 0;

        /**
         * ======================================================
         * CHART HARIAN
         * ======================================================
         */
        $dailyStats = TransactionModel::query()
            ->where('created_by', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE(transaction_date) as date, SUM(price_final) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        /**
         * ======================================================
         * TOP PRODUCTS
         * ======================================================
         */
        $topProducts = TransactionModel::query()
            ->where('created_by', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->select('product_id', DB::raw('COUNT(*) as total'))
            ->groupBy('product_id')
            ->with('product:product_id,name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'name'  => $item->product->name ?? '-',
                'total' => $item->total,
            ]);

        return [
            'stats' => [
                'bonus' => round($totalSales * 0.01, 2),
                'sales_volume' => $totalSales,
                'products_sold' => $completedThisMonth->count(),
                'growth' => round($growth, 1),
                'pending_count' => $dpThisMonth->count(),
                'current_month_name' => $startDate->translatedFormat('F Y'),
                'remaining_payment' => $dpThisMonth->sum(fn($trx) =>
                $trx->price_final - $trx->payments_sum_amount),
            ],
            'chart_data' => [
                'labels' => $dailyStats->pluck('date'),
                'values' => $dailyStats->pluck('total'),
            ],
            'top_products' => $topProducts,
        ];
    }
}
