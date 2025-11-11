<?php

namespace App\Http\Controllers\Authorization;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use App\Repositories\AuthorizationUserHandle;

class UsersController extends Controller
{
    protected $userRepository;
    public function __construct(AuthorizationUserHandle $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    public function index(Request $request)
    {
        $filters = $request->only([
            'keyword',
            'limit',
            'page',
            'order_by',
        ]);
        $users = $this->userRepository->getCached(auth()->id(), $filters);
        return Inertia::render('Authorization/UsersHandle/Index', compact('users', 'filters'));
    }
    public function create()
    {
        $users = User::select('id', 'name', 'email')->get();
        $roles = Role::select('id', 'name')->get();
        $permissions = Permission::select('id', 'name')->get();
        $this->userRepository->clearCache(auth()->id());
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions(); // Pastikan cache Spatie direset
        return Inertia::render('Authorization/UsersHandle/Form', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }
    public function edit(string $id)
    {
        $roles = Role::with('permissions')->get(['id', 'name']);
        $permissions = Permission::all(['id', 'name']);
        $users = User::with(['permissions', 'roles'])->findOrFail($id);

        // Format data agar mudah diolah di Vue
        $userData = [
            'id' => $users->id,
            'google_id' => $users->google_id,
            'name' => $users->name,
            'email' => $users->email,
            'status' => $users->status,
            'is_active' => $users->is_active,
            'roles' => $users->getRoleNames(),
            'permissions' => $users->getAllPermissions()->pluck('name'),
        ];

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions(); // Pastikan cache Spatie direset
        $this->userRepository->clearCache(auth()->id());
        return Inertia::render('Authorization/UsersHandle/Form', [
            'users' => $userData,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }
    public function update(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|string|in:active,inactive',
            'roles' => 'required|string',
            'permissions' => 'required|array',
        ], [
            'status.required' => 'Status harus dipilih',
            'status.in' => 'Status tidak tersedia',

            'roles.required' => 'Role wajib dipilih',

            'permissions.array' => 'Permission wajib dipilih',
            'permissions.required' => 'Permission salah satu wajib dipilih',
        ]);
        $users = User::findOrFail($id);
        if (isset($request['roles'])) {
            $users->syncRoles($request['roles']);  // sync role
        }
        if (isset($request['permissions'])) {
            $users->syncPermissions($request['permissions']);  // sync permission langsung (custom per user)
        }
        $users->update([
            'status' => $request['status'],
        ]);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions(); // Pastikan cache Spatie direset
        $this->userRepository->clearCache(auth()->id());
        return redirect()->route('users')->with('message', 'User ' . $users->name . ' Berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $users = User::findOrFail($id);

        // Lepas semua role dan permission sebelum hapus
        $users->syncRoles([]);
        $users->syncPermissions([]);

        $users->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->userRepository->clearCache(auth()->id());

        return redirect()->route('users')->with('message', 'User ' . $users->name . ' Berhasil dihapus.');
    }

    public function removeAuthorization(Request $request, string $id)
    {
        $users = User::findOrFail($id);
        $users->removeRole($request['role']);
        $users->revokePermissionTo($request['permission'] ?? []);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->userRepository->clearCache(auth()->id());
        return redirect()->route('users')->with('message', 'User ' . $users->name . ' Berhasil dihapus.');
    }
}
