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
            ['branches_id' => Str::uuid(), 'branch_code' => BranchesModel::generateUniqueCode(), 'name' => 'pekanbaru', 'address' => 'Jl. Soekarno Hatta No.10, Pekanbaru'],
            ['branches_id' => Str::uuid(), 'branch_code' => BranchesModel::generateUniqueCode(), 'name' => 'medan', 'address' => 'Jl. Gajah Mada No.15, Medan'],
            ['branches_id' => Str::uuid(), 'branch_code' => BranchesModel::generateUniqueCode(), 'name' => 'surabaya', 'address' => 'Jl. Ahmad Yani No.7, Surabaya'],
        ];

        foreach ($branches as $branch) {
            BranchesModel::firstOrCreate(['name' => $branch['name']], $branch);
        }
    }
}
