<?php

namespace Database\Seeders;

use App\Models\PageModel;
use App\Models\PermissionModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PagesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            ['name' => 'Home', 'slug' => 'home'],
            // tambahkan halaman lain kalau perlu
        ];
        $commonActions = [
            ['key' => 'can_view', 'label' => 'Lihat'],
            ['key' => 'can_add', 'label' => 'Tambah'],
            ['key' => 'can_edit', 'label' => 'Edit'],
            ['key' => 'can_share', 'label' => 'Bagikan'],
            ['key' => 'can_upload', 'label' => 'Upload'],
            ['key' => 'can_import', 'label' => 'Import'],
            ['key' => 'can_export', 'label' => 'Export'],
            ['key' => 'can_access', 'label' => 'Dapat diakses'],
        ];

        foreach ($pages as $pg) {
            $page = PageModel::firstOrCreate(['slug' => $pg['slug']], $pg);
            foreach ($commonActions as $act) {
                PermissionModel::firstOrCreate([
                    'pages_id' => $page->pages_id,
                    'key' => $act['key']
                ], ['label' => $act['label']]);
            }
        }
    }
}
