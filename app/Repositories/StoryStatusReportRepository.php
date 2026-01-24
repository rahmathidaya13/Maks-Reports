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
        $query = StoryStatusReportModel::query()
            ->with('creator')
            ->where('created_by', auth()->id())
            ->whereNull('deleted_at');

        /*
    |--------------------------------------------------------------------------
    | DEFAULT DATE (Vue kirim null)
    |--------------------------------------------------------------------------
    */
        if (
            (!isset($filters['start_date']) || $filters['start_date'] === null) &&
            (!isset($filters['end_date']) || $filters['end_date'] === null)
        ) {
            // Jika user tidak memberi filter → ambil data hari ini
            $query->whereDate('report_date', now()->toDateString());
        }

        /*
    |--------------------------------------------------------------------------
    | FILTER RANGE DATE
    |--------------------------------------------------------------------------
    */
        if (
            isset($filters['start_date'], $filters['end_date']) &&
            $filters['start_date'] !== null &&
            $filters['end_date'] !== null
        ) {
            $query->whereBetween('report_date', [
                $filters['start_date'],
                $filters['end_date'],
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | FILTER KEYWORD (Vue kirim '')
    |--------------------------------------------------------------------------
    */
        if (isset($filters['keyword']) && $filters['keyword'] !== '' && $filters['keyword'] !== null) {
            $search = $filters['keyword'];

            $query->where('report_code', 'like', "%{$search}%");
        }

        return $query
            ->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
