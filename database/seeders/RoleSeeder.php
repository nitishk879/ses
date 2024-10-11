<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = array([
            'title' => 'Admin',
            'slug'  => 'admin'
        ],
            [
                'title' => 'User',
                'slug'  => 'user'
            ],
            [
                'title' => 'Operator',
                'slug'  => 'talent'
            ]);

        foreach ($roles as $role) {
            Role::create([
                'title' => $role['title'],
                'slug' => Str::slug($role['slug'], '_'),
            ]);
        }
    }
}
