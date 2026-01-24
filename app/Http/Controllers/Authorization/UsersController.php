<?php

namespace App\Http\Controllers\Authorization;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\UserService;
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
            'active_emp',
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
        return Inertia::render('Authorization/UsersHandle/Form', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function edit(string $id)
    {
        $userForDeveloper = auth()->user()->hasRole('developer');
        if (!$userForDeveloper) {
            return back()->with('warning', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }
        // dd($userForDeveloper);
        // Gunakan variable singular '$user' karena findOrFail mengembalikan 1 objek
        $user = User::with(['profile', 'permissions', 'roles', 'profile.branch', 'profile.jobTitle'])
            ->findOrFail($id);

        // Format data (Transformation)
        $formattedUser = [
            'id' => $user->id,
            'google_id' => $user->google_id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status,
            'profile' => $user->profile,
            'is_active' => $user->is_active,
            'roles' => $user->getRoleNames()->toArray(),
            'permissions' => $user->getAllPermissions(),
        ];

        // Clear cache jika diperlukan (sesuai logic Anda)
        $this->userRepository->clearCache(auth()->id());

        $roles = Role::with('permissions')->get(['id', 'name']);
        $permissions = Permission::all(['id', 'name']);

        $this->userRepository->clearCache(auth()->id());
        return Inertia::render('Authorization/UsersHandle/Form', [
            'users' => $formattedUser,
            'roles' => $roles,
            'permissions' => $permissions
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
        $users->status = $request->input('status');
        $users->update();
        if (isset($request['roles'])) {
            // sync role
            $users->syncRoles($request['roles']);
        }
        if (isset($request['permissions'])) {
            // sync permission langsung ( custom per user )
            $users->syncPermissions($request->input('permissions'));
        }
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

        $this->userRepository->clearCache(auth()->id());
        return redirect()->route('users')->with('message', 'User ' . $users->name . ' Berhasil dihapus.');
    }

    public function removeAuthorization(Request $request, string $id)
    {
        $users = User::findOrFail($id);
        $users->removeRole($request['role']);
        $users->revokePermissionTo($request['permission'] ?? []);
        $this->userRepository->clearCache(auth()->id());
        return redirect()->route('users')->with('message', 'User ' . $users->name . ' Berhasil dihapus.');
    }

    public function detail(string $id)
    {
        // Gunakan variable singular '$user' karena findOrFail mengembalikan 1 objek
        $user = User::with(['profile', 'permissions', 'roles', 'profile.branch', 'profile.jobTitle'])
            ->findOrFail($id);

        // Format data (Transformation)
        $formattedUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'profile' => $user->profile,
            'is_active' => $user->is_active,
            'status' => $user->status,
            'roles' => $user->getRoleNames()->toArray(),
            'permissions' => $user->getAllPermissions(),
        ];

        // Clear cache jika diperlukan (sesuai logic Anda)
        $this->userRepository->clearCache(auth()->id());
        return response()->json([
            'status' => true,
            'message' => 'Success',
            'users' => $formattedUser,
        ]);
    }
    public function refresh()
    {
        $this->userRepository->clearCache(auth()->id());
        return redirect()->route('users')->with('message', 'Data pengguna berhasil diperbarui');
    }
}
