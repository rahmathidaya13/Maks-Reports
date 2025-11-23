<?php

namespace App\Repositories;

use Illuminate\Support\Carbon;
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
        $recent_timestamp = session('recent_timestamp');
        $recent_action = session('highlight_type');

        $query = StoryStatusReportModel::with('creator')
            ->where('created_by', auth()->user()->id)
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
        $storyReport = $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);

        if ($recent_timestamp) {
            $recent = Carbon::parse($recent_timestamp);
            $storyReport->getCollection()->transform(function ($item) use ($recent, $recent_action) {
                $item->is_recent_created = false;
                $item->is_recent_updated = false;

                if ($recent_action === 'create' && $item->created_at >= $recent) {
                    $item->is_recent_created = $item->created_at->gt(now()->subDay());
                }

                if ($recent_action === 'edit' && $item->updated_at >= $recent) {
                    $item->is_recent_updated = $item->updated_at->gt(now()->subDay());
                }

                return $item;
            });
        }

        return $storyReport;
    }
}
