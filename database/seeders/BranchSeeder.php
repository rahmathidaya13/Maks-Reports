<?php

namespace Database\Seeders;

use App\Models\BranchesModel;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            ['branches_id' => Str::uuid(), 'name' => 'pekanbaru', 'address' => 'Jl. Soekarno Hatta No.10, Pekanbaru', 'phone' => '0761-123456'],
            ['branches_id' => Str::uuid(), 'name' => 'medan', 'address' => 'Jl. Gajah Mada No.15, Medan', 'phone' => '061-654321'],
            ['branches_id' => Str::uuid(), 'name' => 'surabaya', 'address' => 'Jl. Ahmad Yani No.7, Surabaya', 'phone' => '031-7654321'],
        ];

        foreach ($branches as $branch) {
            BranchesModel::firstOrCreate(['name' => $branch['name']], $branch);
        }
    }
}
