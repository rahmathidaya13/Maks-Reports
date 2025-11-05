<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\RolesModel;
use Illuminate\Support\Str;
use App\Models\BranchesModel;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $user = User::firstOrCreate(
            ['email' => 'rahmadlawrent6@gmail.com'],
            [
                'id' => Str::uuid(),
                'name' => 'Rahmat Hidayah',
                'email_verified_at' => now(),
                'password' => bcrypt('@Rahmad12345'),
                'role' => 'developer',
            ]
        );
        $user->profile()->updateOrCreate(
            ['users_id' => $user->id],
        );

        $this->call([
            BranchSeeder::class,
            JobTitle::class,
            RolesSeeder::class
        ]);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
