<?php

namespace App\Repositories;

use App\Models\JobTitleModel;
use Illuminate\Pagination\LengthAwarePaginator;

class JobTitle extends BaseCacheRepository
{
    protected string $cachePrefix = 'job_title';
    /**
     * Override: definisikan cara ambil data dari database
     */
    protected function getData(array $filters = []): LengthAwarePaginator
    {
        $userId = auth()->user()->id;
        $query = JobTitleModel::where('created_by', $userId)
            ->when(!empty($filters['keyword']), function ($q) use ($filters) {
                $search = $filters['keyword'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%");
                    $sub->orWhere('title_alias', 'like', "%{$search}%");
                    $sub->orWhere('job_title_code', 'like', "%{$search}%");
                });
            });

        return $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
