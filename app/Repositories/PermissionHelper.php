<?php

namespace App\Repositories;

use App\Models\User;

class PermissionHelper
{
    /**
     * Ambil permission default dari role_has_permissions user
     */
    public static function roleHasPermissions(User $user): array
    {
        $role = $user->roles->first();

        return $role
            ? $role->permissions->pluck('name')->toArray()
            : [];
    }

    /**
     * Ambil permission custom dari model_has_permissions user (override)
     */
    public static function modelHasPermissions(User $user): array
    {
        return $user->permissions->pluck('name')->toArray() ?? [];
    }

    /**
     * Gabungan permission role + custom
     */
    public static function allPermissions(User $user): array
    {
        return array_values(array_unique(array_merge(
            self::roleHasPermissions($user),
            self::modelHasPermissions($user)
        )));
    }

    /**
     * Cek apakah permission berasal dari role
     */
    public static function isFromRole(User $user, string $permission): bool
    {
        return in_array($permission, self::roleHasPermissions($user));
    }

    /**
     * Cek apakah permission custom dari model_has_permissions
     */
    public static function isCustom(User $user, string $permission): bool
    {
        return in_array($permission, self::modelHasPermissions($user));
    }
}
