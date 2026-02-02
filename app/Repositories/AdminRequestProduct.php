<?php

namespace App\Repositories;

use Spatie\Permission\Models\Role;
use App\Models\ProductRequestUserModel;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminRequestProduct extends BaseCacheRepository
{
    protected string $cachePrefix = 'admin_request_product_';
    /**
     * Override: definisikan cara ambil data dari database
     */
    protected function getData(array $filters = []): LengthAwarePaginator
    {
        $query = ProductRequestUserModel::query()
            ->with(['user.profile.branch', 'product'])
            ->where('user_id', auth()->id())
            ->orderByRaw("FIELD('status', 'pending', 'approved', 'rejected')");
        return $query
            ->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
