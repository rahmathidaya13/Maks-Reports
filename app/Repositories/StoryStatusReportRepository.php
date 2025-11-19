<?php

namespace App\Repositories;

use App\Models\StoryStatusReportModel;
use Illuminate\Pagination\LengthAwarePaginator;

class StoryStatusReportRepository extends BaseCacheRepository
{
    protected string $cachePrefix = 'story_reports_data';
    /**
     * Override: definisikan cara ambil data dari database
     */
    protected function getData(array $filters = []): LengthAwarePaginator
    {
        $query = StoryStatusReportModel::with('creator')
            ->where('created_by', auth()->user()->id)
            ->when(!empty($filters['start_date']) && !empty($filters['end_date']), function ($q) use ($filters) {
                // filter rentang tanggal
                $q->whereBetween('report_date', [
                    $filters['start_date'],
                    $filters['end_date']
                ]);
            });
        return $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
