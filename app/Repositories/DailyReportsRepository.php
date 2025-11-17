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
        $query = DailyReportModel::with('creator')
            ->when(!empty($filters['start_date']) && !empty($filters['end_date']), function ($q) use ($filters) {
                // filter rentang tanggal
                $q->whereBetween('date', [
                    $filters['start_date'],
                    $filters['end_date']
                ]);
            });
        return $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 5);
    }
}
