<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolesAndPermissionsSeede extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan izin unik dibuat terlebih dahulu
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Definisikan Hak Akses (Permissions)
        Permission::create(['name' => 'view_reports']);
        Permission::create(['name' => 'create_ticket']);
        Permission::create(['name' => 'edit_any_ticket']);
        Permission::create(['name' => 'access_admin_dashboard']);

        // 2. Definisikan Jabatan (Roles)
        $manager = Role::create(['name' => 'manager']);
        $teknisi = Role::create(['name' => 'teknisi']);
        $developer = Role::create(['name' => 'developer']);

        // 3. Hubungkan Hak Akses ke Jabatan

        // Manager (bisa lihat laporan dan edit semua tiket)
        $manager->givePermissionTo(['view_reports', 'edit_any_ticket']);

        // Teknisi (hanya bisa membuat tiket)
        $teknisi->givePermissionTo('create_ticket');

        // Super Admin (mendapatkan semua izin)
        $developer->givePermissionTo(Permission::all());

        // 4. Berikan Peran ke User Awal (Opsional)
        $developer = \App\Models\User::first(); // Ambil user pertama (misalnya user yang Anda buat saat register)
        if ($developer) {
            $developer->assignRole('developer');
        }
    }
}
