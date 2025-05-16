<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@stagepass.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_admin' => true,
            'created_at' => now()->subMonths(6),
        ]);

        // Create another admin
        User::create([
            'name' => 'John Admin',
            'email' => 'john.admin@stagepass.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_admin' => true,
            'created_at' => now()->subMonths(5),
        ]);

        // Create regular test users
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_admin' => false,
            'created_at' => now()->subMonths(4),
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_admin' => false,
            'created_at' => now()->subMonths(3),
        ]);

        // Create additional random users with varied creation dates
        collect(range(1, 20))->each(function ($index) {
            User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => false,
                'created_at' => now()->subMonths(rand(1, 6))->subDays(rand(1, 30)),
            ]);
        });
    }
} 