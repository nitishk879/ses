<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Country::truncate();
        $datafile = File::get(public_path('/json/countries.json'));
        $countries = json_decode($datafile);

        foreach ($countries as $country) {
            Country::create([
                'name' => $country->title,
                'slug' => $country->slug,
                'country_code' => $country->country_code,
                'phone_code' => $country->phone_code,
                'currency_symbol' => $country->currency_symbol,
            ]);
        }
    }
}
