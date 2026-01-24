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
            ->with(['profile', 'roles', 'permissions']);

        /*
|--------------------------------------------------------------------------
| FILTER KEYWORD (Vue kirim '')
|--------------------------------------------------------------------------
*/
        if (isset($filters['keyword']) && $filters['keyword'] !== ''  && $filters['keyword'] !== null) {
            $search = $filters['keyword'];

            $query->where('name', 'like', "%{$search}%");
        }

        /*
|--------------------------------------------------------------------------
| FILTER STATUS USER (Vue kirim null)
|--------------------------------------------------------------------------
*/
        if (array_key_exists('active_emp', $filters) && $filters['active_emp'] !== null) {
            // Jika filter dikirim → pakai nilai tersebut
            $query->where('status', $filters['active_emp']);
        } else {
            // Default: hanya user active
            $query->where('status', 'active');
        }

        /*
|--------------------------------------------------------------------------
| PAGINATION
|--------------------------------------------------------------------------
*/
        $users = $query
            ->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 5);

        /*
|--------------------------------------------------------------------------
| TRANSFORM RESPONSE
|--------------------------------------------------------------------------
*/
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
