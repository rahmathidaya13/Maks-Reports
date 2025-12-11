<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $defaults = [
            ['name' => 'developer', 'guard_name' => 'web'],
            ['name' => 'admin', 'guard_name' => 'web'],
            ['name' => 'user', 'guard_name' => 'web'],
        ];

        foreach ($defaults as $data) {
            Role::firstOrCreate(['name' => $data['name']], $data);
        }

        // $permissions = [
        //     'create',
        //     'read',
        //     'update',
        //     'delete',
        //     'manage-backup',
        //     'manage-roles',
        //     'manage-permission',
        //     'import',
        //     'export',
        //     'share',
        //     'download'
        // ];
        // foreach ($permissions as $perm) {
        //     Permission::firstOrCreate(['name' => $perm]);
        // }
        // assign semua permission ke user
        // $userRole = Role::where('name', 'user')->first();
        // $userRole->syncPermissions(['create', 'read', 'update', 'delete', 'share', 'download']);

        // $devRole = Role::where('name', 'developer')->first();
        // $devRole->syncPermissions(permission::all()->pluck('name')->toArray());
    }
}
