<?php

namespace App\Http\Controllers\Authorization;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'keyword',
            'limit',
            'page',
            'order_by',
        ]);
        return Inertia::render('Authorization/Roles/Index', [
            'roles' => Role::with('permissions')->paginate($filters['limit'] ?? 10)->appends($filters),
            'filters' => $filters,
        ]);
    }
    public function create()
    {
        return Inertia::render('Authorization/Roles/Form', [
            'permissions' => Permission::all(),
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create(['name' => $validated['name']]);
        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions'] ?? []);
        }
        return redirect()->route('roles')->with('message', 'Role created successfully.');
    }
}
