<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::truncate();
        $datafile = File::get(public_path('/dataset/users.json'));
        $talents = json_decode($datafile);

        foreach ($talents as $talent) {
            User::create([
                'firstname' => $talent->firstname,
                'lastname' => $talent->lastname,
                'email' => $talent->email,
                'username' => $talent->username,
                'phone' => $talent->phone ?? random_int(6000000000, 9999999999),
                'date_of_birth' => $talent->date_of_birth,
                'gender' => $talent->gender,
                'languages' => $talent->languages ?? random_int(1, 3),
                'nationality' => $talent->nationality,
                'country' => $talent->country,
                'nearest_station_prefecture' => $talent->nearest_station_prefecture,
                'nearest_station_line' => $talent->nearest_station_line,
                'station_name' => $talent->station_name,
                'is_public' => $talent->is_public,
                'email_verified_at' => $talent->email_verified_at,
                'password' => $talent->password ?? Hash::make('password')
            ])->roles()->sync([1, 2, 3]);
        }
    }
}
