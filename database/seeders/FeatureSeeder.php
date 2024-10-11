<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\ProjectFeature;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProjectFeature::truncate();
        $datafile = File::get(public_path('dataset/features.json'));
        $project_features = json_decode($datafile);

        foreach ($project_features as $project_feature) {
            Feature::create([
                'title' => $project_feature->title,
                'slug' => Str::slug($project_feature->title, '_'),
            ]);
        }
    }
}
