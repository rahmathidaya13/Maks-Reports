<?php

namespace App\Repositories;

use App\Models\DailyReportModel;
use Illuminate\Pagination\LengthAwarePaginator;

class DailyReportsRepository extends BaseCacheRepository
{
    protected string $cachePrefix = 'daily_reports_data';
    /**
     * Override: definisikan cara ambil data dari database
     */
    protected function getData(array $filters = []): LengthAwarePaginator
    {
        // Ambil data
        $query = DailyReportModel::query()
            ->with('creator')
            ->where('created_by', auth()->user()->id)
            ->whereNull('deleted_at');

        /*
    |--------------------------------------------------------------------------
    | FILTER TANGGAL
    | Vue mengirim null jika tidak dipilih
    |--------------------------------------------------------------------------
    */
        $startDate = $filters['start_date'] ?? null;
        $endDate   = $filters['end_date'] ?? null;

        if ($startDate === null && $endDate === null) {
            // Default: hari ini
            $query->whereDate('date', now()->toDateString());
        } elseif ($startDate !== null && $endDate !== null) {
            // Rentang tanggal
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($startDate !== null) {
            // Hanya start_date
            $query->whereDate('date', '>=', $startDate);
        } elseif ($endDate !== null) {
            // Hanya end_date
            $query->whereDate('date', '<=', $endDate);
        }

        return $query
            ->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 1);
    }
}
