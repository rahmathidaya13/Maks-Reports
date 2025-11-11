<?php

namespace App\Http\Controllers\Authorization;

use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use App\Repositories\AuthorizationPermissions;

class PermissionController extends Controller
{
    protected $permissions;
    public function __construct(AuthorizationPermissions $permissionRepos)
    {
        $this->permissions = $permissionRepos;
    }
    public function index(Request $request)
    {
        $filters = $request->only([
            'keyword',
            'limit',
            'page',
            'order_by',
        ]);
        $permissions = $this->permissions->getCached(auth()->id(), $filters);
        return Inertia::render('Authorization/Permissions/Index', compact('filters', 'permissions'));
    }

    public function create()
    {
        $this->permissions->clearCache(auth()->id());
        return Inertia::render('Authorization/Permissions/Form');
    }
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|unique:permissions,name',
                'guard_name' => 'required|string',
            ],
            [
                'name.required' => 'Permission wajib dibuat',
                'name.string' => 'Permission wajib tidak sesuai',
                'name.unique' => 'Permission sudah ada',

                'guard_name.required' => 'Guard name wajib dibuat',
                'guard_name.string' => 'Guard name wajib tidak sesuai',
            ]
        );
        Permission::create([
            'name' => Str::slug($validated['name']),
            'guard_name' => $validated['guard_name']
        ]);
        $this->permissions->clearCache(auth()->id());
        return redirect()->route('permissions')->with('message', 'Permission ' . $validated['name'] . ' Berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $permissions = Permission::findOrFail($id);
        $this->permissions->clearCache(auth()->id());
        return Inertia::render('Authorization/Permissions/Form', compact('permissions'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|unique:permissions,name,' . $id . ',.id',
                'guard_name' => 'required|string',
            ],
            [
                'name.required' => 'Permission wajib dibuat',
                'name.string' => 'Permission wajib tidak sesuai',
                'name.unique' => 'Permission sudah ada',
                'guard_name.required' => 'Guard name wajib dibuat',
                'guard_name.string' => 'Guard name wajib tidak sesuai',
            ]
        );
        $permissions = Permission::findOrFail($id);
        $permissions->update([
            'name' => Str::slug($validated['name']),
            'guard_name' => $validated['guard_name']
        ]);
        $this->permissions->clearCache(auth()->id());
        return redirect()->route('permissions')->with('message', 'Permission ' . $validated['name'] . ' Berhasil diperbarui');
    }
    public function destroy(Permission $permission, string $id)
    {
        $permission::findOrFail($id)->delete();
        $this->permissions->clearCache(auth()->id());
        return redirect()->route('permissions')->with('message', 'Permission ' . $permission->name . ' Berhasil dihapus.');
    }

    public function destroyAll(Request $request)
    {
        $all_id = $request->input('all_id', []);
        if (!count($all_id)) return back()->with('message', 'Tidak ada data yang dipilih.');
        Permission::whereIn('id', $all_id)->delete();
        $this->permissions->clearCache(auth()->id());
        return redirect()->route('permissions')->with('message', count($all_id) . ' Data berhasil Terhapus.');
    }
}
