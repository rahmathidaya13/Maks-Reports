<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\PagePermissionModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            ['name' => 'Home', 'slug' => 'home'],
            ['name' => 'Halaman Jabatan', 'slug' => 'roles-list'],
        ];

        foreach ($pages as $page) {
            PagePermissionModel::updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'page_permissions_id' => (string) Str::uuid(),
                    'name' => $page['name']
                ]
            );
        }
    }
}
