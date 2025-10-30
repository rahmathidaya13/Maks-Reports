<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PageModel;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\UserPagePermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserPagePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('pages')->truncate();
        DB::table('permissions')->truncate();

        // === Halaman yang tersedia saat ini ===
        $pages = [
            [
                'id' => Str::uuid(),
                'name' => 'Home',
                'slug' => 'home',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // === Jenis izin yang berlaku di semua halaman ===
        $permissions = [
            [
                'id' => Str::uuid(),
                'key' => 'can_view',
                'label' => 'Melihat halaman',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'key' => 'can_add',
                'label' => 'Menambah data',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'key' => 'can_edit',
                'label' => 'Mengedit data',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'key' => 'can_delete',
                'label' => 'Menghapus data',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'key' => 'can_download',
                'label' => 'Mengunduh data atau laporan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('pages')->insert($pages);
        DB::table('permissions')->insert($permissions);

        $this->command->info('✅ Seeder PagesAndPermissionsSeeder berhasil dijalankan!');
    }
}
