<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignRoleWithPermissionsById
{
    protected $user;
    protected $roleName;
    public function __construct(User $users, string $roleName)
    {
        $this->user = $users;
        $this->roleName = $roleName;
    }
    public function execute(): User
    {
        $role = Role::where('name', $this->roleName)->firstOrFail();
        $this->user->syncRoles($role);
        $permissionById = $role->permissions()->pluck('id')->toArray();
        $this->user->permissions()->sync($permissionById);
        return $this->user;
    }
}
