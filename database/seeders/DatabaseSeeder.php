<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\RolesModel;
use Illuminate\Support\Str;
use App\Models\BranchesModel;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Services\AssignRoleWithPermissionsById;

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
            ]
        );

        // Pastikan role 'developer' sudah ada (guard_name penting!)
        $role = Role::firstOrCreate([
            'name' => 'developer',
            'guard_name' => 'web',
        ]);
        // Reset cache dulu untuk jaga-jaga
        // app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Assign role ke user
        $user->syncRoles($role);
        $user->syncPermissions($role->permissions()->pluck('name')->toArray());

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
