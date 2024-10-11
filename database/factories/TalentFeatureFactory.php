<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TalentFeature>
 */
class TalentFeatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'feature_1' => $this->faker->boolean(),
            'feature_2' => $this->faker->boolean(),
            'feature_3' => $this->faker->boolean(),
            'feature_4' => $this->faker->boolean(),
            'feature_5' => $this->faker->boolean(),
            'feature_6' => $this->faker->boolean(),
            'feature_7' => $this->faker->boolean(),
            'feature_8' => $this->faker->boolean(),
            'feature_9' => $this->faker->boolean(),
            'feature_10' => $this->faker->boolean(),
            'feature_11' => $this->faker->boolean(),
            'feature_12' => $this->faker->boolean(),
            'talent_id' => $this->faker->boolean(),
        ];
    }
}
