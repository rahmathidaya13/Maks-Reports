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
        $query = Permission::when(filled($filters['keyword'] ?? null), function ($q) use ($filters) {
            $q->where('name', 'like', '%' . trim($filters['keyword']) . '%');
        });

        return $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
