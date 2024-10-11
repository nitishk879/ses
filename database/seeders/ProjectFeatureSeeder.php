<?php

namespace Database\Seeders;

use App\Models\ProjectFeature;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProjectFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProjectFeature::truncate();
        $datafile = File::get(public_path('dataset/project_features.json'));
        $project_features = json_decode($datafile);
        foreach ($project_features as $project_feature) {
            ProjectFeature::create([
                "recruitment_target_1" => $project_feature->recruitment_target_1,
                "recruitment_target_2" => $project_feature->recruitment_target_2,
                "recruitment_target_3" => $project_feature->recruitment_target_3,
                "recruitment_target_4" => $project_feature->recruitment_target_4,
                "recruitment_target_5" => $project_feature->recruitment_target_5,
                "recruitment_target_6" => $project_feature->recruitment_target_6,
                "case_feature_1" => $project_feature->case_feature_1,
                "case_feature_2" => $project_feature->case_feature_2,
                "case_feature_3" => $project_feature->case_feature_3,
                "case_feature_4" => $project_feature->case_feature_4,
                "case_feature_5" => $project_feature->case_feature_5,
                "case_feature_6" => $project_feature->case_feature_6,
                "case_feature_7" => $project_feature->case_feature_7,
                "case_feature_8" => $project_feature->case_feature_8,
                "case_feature_9" => $project_feature->case_feature_9,
                "case_feature_10" => $project_feature->case_feature_10,
                "case_feature_11" => $project_feature->case_feature_11,
                "case_feature_12" => $project_feature->case_feature_12,
                "case_feature_13" => $project_feature->case_feature_13,
                "case_feature_14" => $project_feature->case_feature_14,
                "case_feature_15" => $project_feature->case_feature_15,
                "project_id" => $project_feature->project_id,
            ]);
        }
    }
}
