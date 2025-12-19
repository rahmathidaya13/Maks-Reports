<?php

namespace App\Repositories;

use App\Models\BranchesModel;
use App\Models\SalesRecords;
use Illuminate\Pagination\LengthAwarePaginator;

class SalesRecordRepository extends BaseCacheRepository
{
    protected string $cachePrefix = 'sales_records_cache';
    /**
     * Override: definisikan cara ambil data dari database
     */
    protected function getData(array $filters = []): LengthAwarePaginator
    {
        $query = SalesRecords::with(['creator', 'product'])
            ->where('created_by', auth()->id())
            ->whereNull('deleted_at')
            ->when(!empty($filters['keyword']), function ($q) use ($filters) {
                $search = $filters['keyword'];
                $q->WhereHas('product', function ($product) use ($search) {
                    $product->where('name', 'like', "%{$search}%");
                });
            });

        return $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
