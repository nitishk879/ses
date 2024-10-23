<?php

namespace Database\Seeders;

use App\Enums\GenderEnum;
use App\Enums\LangEnum;
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
            UserSeeder::class,
            CategorySeeder::class,
            SubCategorySeeder::class,
            IndustrySeeder::class,
            FeatureSeeder::class,
            SampleSeeder::class
        ]);

//        User::factory()->create([
//            'firstname' => 'Nitish',
//            'lastname' => 'K.',
//            'email' => 'nitishk879@gmail.com',
//            'username' => 'nitishk879',
//            'password' => Hash::make('password'),
//            'date_of_birth' => now()->subYears(30),
//            'gender' => GenderEnum::MALE,
//            'languages' => 1
//        ])->roles()->attach([1, 2]);
//        User::factory()->create([
//            'firstname' => 'Neha',
//            'lastname' => 'Pal',
//            'email' => 'neha.pal@gmail.com',
//            'username' => 'neha.pal',
//            'password' => Hash::make('password'),
//            'date_of_birth' => now()->subYears(30),
//            'gender' => GenderEnum::FEMALE,
//            'languages' => 3
//        ])->roles()->attach([2, 3]);
//        User::factory()->create([
//            'firstname' => 'Lakshay',
//            'lastname' => 'Kapoor',
//            'email' => 'mysterysolver008@gmail.com',
//            'username' => 'mysterysolver008',
//            'password' => Hash::make('password'),
//            'date_of_birth' => now()->subYears(30),
//            'gender' => GenderEnum::MALE,
//            'languages' => 1
//        ])->roles()->attach([2]);
//        User::factory()->create([
//            'firstname' => 'Neha',
//            'lastname' => 'Kanwar',
//            'email' => 'neha.kanw@gmail.com',
//            'username' => 'neha.kanw',
//            'password' => Hash::make('password'),
//            'date_of_birth' => now()->subYears(30),
//            'gender' => GenderEnum::FEMALE,
//            'languages' => 3
//        ])->roles()->attach([2, 3, 4, 5]);
//        User::factory()->create([
//            'firstname' => 'Asheesh',
//            'lastname' => 'Mehta',
//            'email' => 'asheesh.yogi@gmail.com',
//            'username' => 'asheesh.yogi',
//            'password' => Hash::make('password'),
//            'date_of_birth' => now()->subYears(30),
//            'gender' => GenderEnum::MALE,
//            'languages' => 1
//        ])->roles()->attach([1,2]);

        $this->call([
            CompanySeeder::class,
            ProjectSeeder::class,
            LocationSeeder::class,
            TalentSeeder::class,
        ]);
    }
}
