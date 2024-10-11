<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectFeature>
 */
class ProjectFeatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "recruitment_target_1" => $this->faker->boolean(),
            "recruitment_target_2" => $this->faker->boolean(),
            "recruitment_target_3" => $this->faker->boolean(),
            "recruitment_target_4" => $this->faker->boolean(),
            "recruitment_target_5" => $this->faker->boolean(),
            "recruitment_target_6" => $this->faker->boolean(),
            "case_feature_1" => $this->faker->boolean(),
            "case_feature_2" => $this->faker->boolean(),
            "case_feature_3" => $this->faker->boolean(),
            "case_feature_4" => $this->faker->boolean(),
            "case_feature_5" => $this->faker->boolean(),
            "case_feature_6" => $this->faker->boolean(),
            "case_feature_7" => $this->faker->boolean(),
            "case_feature_8" => $this->faker->boolean(),
            "case_feature_9" => $this->faker->boolean(),
            "case_feature_10" => $this->faker->boolean(),
            "case_feature_11" => $this->faker->boolean(),
            "case_feature_12" => $this->faker->boolean(),
            "case_feature_13" => $this->faker->boolean(),
            "case_feature_14" => $this->faker->boolean(),
            "case_feature_15" => $this->faker->boolean(),
            "project_id" => Project::factory()
        ];
    }
}
