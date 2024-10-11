<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Location::truncate();
        $datafile = File::get(public_path('/dataset/locations.json'));
        $locations = json_decode($datafile);

        foreach ($locations as $location) {
            Location::create([
                'title' => $location->title,
                'slug'  => Str::slug($location->title, '-'),
                'country_id'    => $location->country_id ?? 1,
            ]);
        }
    }
}
