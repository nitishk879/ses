<?php

namespace Database\Seeders;

use App\Models\TalentFeature;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class TalentFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TalentFeature::truncate();
        $datafile = File::get(public_path('/json/talentFeatures.json'));
        $features = json_decode($datafile);

        foreach ($features as $feature) {
            TalentFeature::create([
                'feature_1' => $feature->feature_1,
                'feature_2' => $feature->feature_2,
                'feature_3' => $feature->feature_3,
                'feature_4' => $feature->feature_4,
                'feature_5' => $feature->feature_5,
                'feature_6' => $feature->feature_6,
                'feature_7' => $feature->feature_7,
                'feature_8' => $feature->feature_8,
                'feature_9' => $feature->feature_9,
                'feature_10' => $feature->feature_10,
                'feature_11' => $feature->feature_11,
                'feature_12' => $feature->feature_12,
                'talent_id' => $feature->talent_id,
            ]);
        }
    }
}
