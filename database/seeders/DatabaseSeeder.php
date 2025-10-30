<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\BranchesModel;
use App\Models\RolesModel;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BranchSeeder::class,
            RoleSeeder::class
        ]);
        $user = User::firstOrCreate(
            ['email' => 'rahmadlawrent6@gmail.com'],
            [
                'id' => Str::uuid(),
                'name' => 'Rahmat Hidayah',
                'email_verified_at' => now(),
                'password' => bcrypt('@Rahmad12345'),
                'level' => 'developer',
            ]
        );
        $user->profile()->updateOrCreate(
            ['users_id' => $user->id],
        );
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
