<?php

namespace App\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;

class AuthorizationPermissions extends BaseCacheRepository
{
    protected string $cachePrefix = 'authorization_permissions';
    /**
     * Override: definisikan cara ambil data dari database
     */
    protected function getData(array $filters = []): LengthAwarePaginator
    {
        $query = Permission::when(!empty($filters['keyword']), function ($q) use ($filters) {
            $search = $filters['keyword'];
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%");
            });
        });

        return $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
