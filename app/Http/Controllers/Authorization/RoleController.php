<?php

namespace App\Http\Controllers\Authorization;

use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Repositories\AuthorizationRoles;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    protected $roleRepository;
    public function __construct(AuthorizationRoles $roleRepos)
    {
        $this->roleRepository = $roleRepos;
    }
    public function index(Request $request)
    {
        $filters = $request->only([
            'keyword',
            'limit',
            'page',
            'order_by',
        ]);
        $roles = $this->roleRepository->getCached(auth()->id(), $filters);
        // dd($roles);
        return Inertia::render('Authorization/Roles/Index', [
            'roles' => $roles,
            'filters' => $filters,
        ]);
    }
    public function create()
    {
        $this->roleRepository->clearCache(auth()->id());
        return Inertia::render('Authorization/Roles/Form', [
            'permissions' => Permission::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|unique:roles,name',
                'permissions' => 'required|array',
                'permissions.*' => 'string|exists:permissions,name',
            ],
            [
                'name.required' => 'Role wajib diisi.',
                'name.unique' => 'Role sudah ada.',
                'name.string' => 'Role wajib tidak sesuai.',
                'permissions.array' => 'Permission wajib dipilih',
                'permissions.required' => 'Permission salah satu wajib dipilih',
                'permissions.*.exists' => 'Permission tidak ditemukan.',
                'permissions.*.string' => 'Permission invalid.',
            ]
        );

        $role = new Role();
        $role->name = Str::slug($validated['name']);
        $role->save();

        $checkRole =  Role::where('name', $role->name)->first();
        if ($checkRole) {
            $checkRole->syncPermissions($validated['permissions']);
        }
        $this->roleRepository->clearCache(auth()->id());
        return redirect()->route('roles')->with('message', 'Role ' . $validated['name'] . ' Berhasil ditambahkan.');
    }
    public function show(Role $roles, string $id)
    {
        $role = $roles::with('permissions')->findOrFail($id);
        $this->roleRepository->clearCache(auth()->id());
        return response()->json([
            'status' => true,
            'roles' => $role,
        ]);
    }
    public function edit(Role $roles, string $id)
    {
        return Inertia::render('Authorization/Roles/Form', [
            'role' => $roles->with('permissions')->findOrFail($id),
            'permissions' => Permission::all(),
        ]);
    }

    public function update(Role $roles, Request $request, string $id)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|unique:roles,name,' . $id . '.id',
                'permissions' => 'required|array',
                'permissions.*' => 'string|exists:permissions,name',
            ],
            [
                'name.required' => 'Role wajib diisi.',
                'name.unique' => 'Role sudah ada.',
                'name.string' => 'Role wajib tidak sesuai.',
                'permissions.array' => 'Permission wajib dipilih',
                'permissions.required' => 'Permission salah satu wajib dipilih',
                'permissions.*.exists' => 'Permission tidak ditemukan.',
                'permissions.*.string' => 'Permission invalid.',
            ]
        );

        $role = $roles::findOrFail($id);
        $role->name = Str::slug($validated['name']);
        $role->update();

        $checkRole =  Role::where('name', $role->name)->first();
        if ($checkRole) {
            $checkRole->syncPermissions($validated['permissions']);
        }
        $this->roleRepository->clearCache(auth()->id());

        return redirect()->route('roles')->with('message', 'Role ' . $validated['name'] . ' Berhasil diperbarui.');
    }

    public function destroy(Role $role, string $id)
    {
        $role::findOrFail($id)->delete();
        $this->roleRepository->clearCache(auth()->id());
        return redirect()->route('roles')->with('message', 'Role ' . $role->name . ' Berhasil dihapus.');
    }

    public function destroyAll(Role $role, Request $request)
    {
        $all_id = $request->input('all_id', []);
        if (!count($all_id)) return back()->with('message', 'Tidak ada data yang dipilih.');

        $this->roleRepository->clearCache(auth()->id());

        $role::whereIn('id', $all_id)->delete();
        return redirect()->route('roles')->with('message', count($all_id) . ' Data berhasil Terhapus.');
    }
}
