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
            ->whereNull('deleted_at')
            ->when(empty($filters['start_date']) && empty($filters['end_date']), function ($q) {
                // Jika user TIDAK memberikan filter → ambil data hari ini
                $q->whereDate('report_date', now()->toDateString());
            })
            ->when(!empty($filters['start_date']) && !empty($filters['end_date']), function ($q) use ($filters) {
                // filter rentang tanggal
                $q->whereBetween('report_date', [
                    $filters['start_date'],
                    $filters['end_date']
                ]);
            });

        // Ambil data paginasi
        $storyReport = $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);

        return $storyReport;
    }
}
