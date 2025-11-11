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
            ->with(['roles', 'permissions'])
            ->when(!empty($filters['keyword']), function ($q) use ($filters) {
                $search = $filters['keyword'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            });
        $users =  $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);

        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'is_active' => $user->is_active,
                'first_login' => $user->first_login
                    ? Carbon::parse($user->first_login)->diffForHumans()
                    : null,
                'last_login' => $user->last_login
                    ? Carbon::parse($user->last_login)->toDateTimeString()
                    : null,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ];
        });

        return $users;
    }
}
