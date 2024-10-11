<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectDetail>
 */
class ProjectDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "data_category" => $this->faker->randomDigit(),
            "data_medium_section" => $this->faker->randomDigit(),
            "data_sub_section" => $this->faker->randomDigit(),
            "remarks" => $this->faker->sentence(12),
            "text_data" => $this->faker->paragraph(2),
            "numerical_data" => $this->faker->randomDigit(),
            "date_data" => $this->faker->date(),
            "project_id" => Project::factory(),
        ];
    }
}
