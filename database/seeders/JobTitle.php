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
            ['created_by' => $develop, 'title' => 'sales', 'job_title_code' => JobTitleModel::generateUniqueCode(), 'title_alias' => 'sls', 'description' => 'Bertugas menjual produk dan melapor harian.'],
            ['created_by' => $develop, 'title' => 'manager', 'job_title_code' => JobTitleModel::generateUniqueCode(), 'title_alias' => 'mgr', 'description' => 'Bertugas mengarahkan dan memantau.'],
            ['created_by' => $develop, 'title' => 'teknisi', 'job_title_code' => JobTitleModel::generateUniqueCode(), 'title_alias' => 'tkn', 'description' => 'Bertugas melakukan perbaikan.'],
            ['created_by' => $develop, 'title' => 'developer', 'job_title_code' => JobTitleModel::generateUniqueCode(), 'title_alias' => 'dev', 'description' => 'Bertugas melakukan perbaikan.'],
        ];

        foreach ($defaults as $data) {
            JobTitleModel::firstOrCreate(['title' => $data['title']], $data);
        }
    }
}
