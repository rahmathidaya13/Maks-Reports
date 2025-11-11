<?php

namespace App\Repositories;

use App\Models\DailyReportModel;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;

class DailyReportsRepository extends BaseCacheRepository
{
    protected string $cachePrefix = 'daily_reports_data';
    /**
     * Override: definisikan cara ambil data dari database
     */
    protected function getData(array $filters = []): LengthAwarePaginator
    {
        $user = Auth::user();
        $query = DailyReportModel::with('creator')
            ->when(!empty($filters['keyword']) && $user->hasRole(['admin', 'super-admin', 'developer', 'editor']), function ($q) use ($filters) {
                $search = $filters['keyword'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('date', 'like', "%{$search}%")
                        ->orWhereHas('creator', function ($creator) use ($search) {
                            $creator->where('name', 'like', "%{$search}%");
                        });
                });
            })->when($user->hasRole('user'), function ($q) use ($user) {
                $q->where('created_by', $user->id);
            })->when(!empty($filters['start_date']) && !empty($filters['end_date']), function ($q) use ($filters) {
                // filter rentang tanggal
                $q->whereBetween('date', [
                    $filters['start_date'],
                    $filters['end_date']
                ]);
            });

        return $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
