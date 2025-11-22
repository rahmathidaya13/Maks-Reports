<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class AuthorizationUserHandle extends BaseCacheRepository
{
    protected string $cachePrefix = 'authorization_users_handle';
    /**
     * Override: definisikan cara ambil data dari database
     */
    protected function getData(array $filters = []): LengthAwarePaginator
    {
        $query = User::query()
            ->with(['profile', 'roles', 'permissions'])
            ->when(!empty($filters['keyword']), function ($q) use ($filters) {
                $search = $filters['keyword'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            })
            ->when(!empty($filters['active_emp']), function ($q) use ($filters) {
                $status = $filters['active_emp'] ?? 'active';
                $q->where('status', $status);
            }, function ($q) {
                $q->where('status', 'active');
            });
        $users = $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 5);

        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'is_active' => $user->is_active,
                'first_login' => $user->first_login
                    ? Carbon::parse($user->first_login)->format('d/m/Y, H:i:s')
                    : null,
                'last_login' => $user->last_login
                    ? Carbon::parse($user->last_login)->format('d/m/Y, H:i:s')
                    : null,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ];
        });

        return $users;
    }
}
