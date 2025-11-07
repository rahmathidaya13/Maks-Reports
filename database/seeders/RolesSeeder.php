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
        $defaults = [
            ['name' => 'developer', 'guard_name' => 'web'],
            ['name' => 'super_admin', 'guard_name' => 'web'],
            ['name' => 'admin', 'guard_name' => 'web'],
            ['name' => 'editor', 'guard_name' => 'web'],
            ['name' => 'user', 'guard_name' => 'web'],
        ];

        foreach ($defaults as $data) {
            Role::firstOrCreate(['name' => $data['name']], $data);
        }

        $permissions = ['create', 'read', 'update', 'delete', 'manage-roles', 'manage-permissions'];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // assign semua permission ke developer
        // $devRole = Role::where('name', 'developer')->first();
        // $devRole->syncPermissions(Permission::all());
    }
}
