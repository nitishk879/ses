<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Industry::truncate();
        $datafile = File::get(public_path('/dataset/industries.json'));
        $industries = json_decode($datafile);

        foreach ($industries as $industry) {
            Industry::create([
                "title" => $industry->title,
                "slug" => $industry->slug ?? Str::slug($industry->title, '_'),
                "description" => $industry->description,
            ]);
        }
    }
}
