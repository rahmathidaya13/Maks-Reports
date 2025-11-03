<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\JobTitleModel;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JobTitle extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        static $develop = null;

        if (is_null($develop)) {
            $develop = User::where('email', 'rahmadlawrent6@gmail.com')->value('id');
        }

        $defaults = [
            ['created_by' => $develop, 'title' => 'Sales', 'slug' => 'sales', 'job_title_code' => JobTitleModel::generateUniqueCode(), 'title_alias' => 'SLS', 'description' => 'Bertugas menjual produk dan melapor harian.'],
            ['created_by' => $develop, 'title' => 'Manager', 'slug' => 'manager', 'job_title_code' => JobTitleModel::generateUniqueCode(), 'title_alias' => 'MGR', 'description' => 'Bertugas mengarahkan dan memantau.'],
            ['created_by' => $develop, 'title' => 'Teknisi', 'slug' => 'teknisi', 'job_title_code' => JobTitleModel::generateUniqueCode(), 'title_alias' => 'TKN', 'description' => 'Bertugas melakukan perbaikan.'],
            ['created_by' => $develop, 'title' => 'Developer', 'slug' => 'developer', 'job_title_code' => JobTitleModel::generateUniqueCode(), 'title_alias' => 'DEV', 'description' => 'Bertugas melakukan perbaikan.'],
            ['created_by' => $develop, 'title' => 'Admin', 'slug' => 'admin', 'job_title_code' => JobTitleModel::generateUniqueCode(), 'title_alias' => 'ADM', 'description' => 'Bertugas mengelola administrasi.'],
        ];

        foreach ($defaults as $data) {
            JobTitleModel::firstOrCreate(['title' => $data['title']], $data);
        }
    }
}
