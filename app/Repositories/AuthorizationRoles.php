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
        $query = Role::with('permissions')
            ->when(!empty($filters['keyword']), function ($q) use ($filters) {
                $search = $filters['keyword'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            });

        return $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
