<?php

namespace App\Repositories;

use Spatie\Permission\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

class AuthorizationRoles extends BaseCacheRepository
{
    protected string $cachePrefix = 'authorization_roles';
    /**
     * Override: definisikan cara ambil data dari database
     */
    protected function getData(array $filters = []): LengthAwarePaginator
    {
        $query = Role::query()
            ->with('permissions')
            ->when(
                filled($filters['keyword'] ?? null),
                fn($q) => $q->where('name', 'like', '%' . trim($filters['keyword']) . '%')
            );

        return $query
            ->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
