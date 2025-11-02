<?php

namespace Database\Seeders;

use App\Models\RolesModel;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['roles_id' => Str::uuid(), 'name' => 'manager', 'short_name' => 'mgr', 'position_code' => RolesModel::generateUniqueCode(), 'description' => 'Memantau laporan semua cabang'],
            ['roles_id' => Str::uuid(), 'name' => 'spv', 'short_name' => 'spv', 'position_code' => RolesModel::generateUniqueCode(), 'description' => 'Memantau laporan cabang'],
            ['roles_id' => Str::uuid(), 'name' => 'admin', 'short_name' => 'admin', 'position_code' => RolesModel::generateUniqueCode(), 'description' => 'Memantau laporan cabang'],
            ['roles_id' => Str::uuid(), 'name' => 'sales', 'short_name' => 'sales', 'position_code' => RolesModel::generateUniqueCode(), 'description' => 'Memantau laporan cabang'],
            ['roles_id' => Str::uuid(), 'name' => 'teknisi', 'short_name' => 'teknisi', 'position_code' => RolesModel::generateUniqueCode(), 'description' => 'Memantau laporan cabang'],

        ];

        foreach ($roles as $role) {
            RolesModel::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
