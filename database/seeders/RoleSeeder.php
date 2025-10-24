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
            ['roles_id' => Str::uuid(), 'name' => 'manager', 'description' => 'Memantau laporan semua cabang'],
            ['roles_id' => Str::uuid(), 'name' => 'spv', 'description' => 'Membimbing tim cabang dan validasi laporan'],
            ['roles_id' => Str::uuid(), 'name' => 'admin', 'description' => 'Mengelola data dan laporan harian'],
            ['roles_id' => Str::uuid(), 'name' => 'sales', 'description' => 'Membuat laporan leads dan status story harian'],
            ['roles_id' => Str::uuid(), 'name' => 'teknisi', 'description' => 'Menangani layanan purna jual dan servis pelanggan'],
        ];

        foreach ($roles as $role) {
            RolesModel::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
