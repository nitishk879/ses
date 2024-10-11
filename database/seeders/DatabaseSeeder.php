<?php

namespace Database\Seeders;

use App\Enums\GenderEnum;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            SubCategorySeeder::class,
        ]);

        User::factory()->create([
            'firstname' => 'Nitish',
            'lastname' => 'K.',
            'email' => 'nitishk879@gmail.com',
            'username' => 'nitishk879',
            'password' => Hash::make('password'),
            'date_of_birth' => now()->subYears(30),
            'gender' => GenderEnum::MALE,
        ])->roles()->attach([1, 2]);

        User::factory()->create([
            'firstname' => 'Neha',
            'lastname' => 'Pal',
            'email' => 'neha.pal@gmail.com',
            'username' => 'neha.pal',
            'password' => Hash::make('password'),
            'date_of_birth' => now()->subYears(30),
            'gender' => GenderEnum::FEMALE,
        ])->roles()->attach([2, 3, 4, 5]);

        $this->call([
            CompanySeeder::class,
            ProjectSeeder::class,
            FeatureSeeder::class,
            LocationSeeder::class
//            ProjectFeatureSeeder::class
        ]);
    }
}
