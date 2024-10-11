<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "company_logo" => $this->faker->imageUrl(300, 300),
            "company_name" => $this->faker->company(),
            "company_email" => $this->faker->email(),
            "company_location" => $this->faker->streetAddress(),
            "industry" => $this->faker->randomElement(["Industrial", "Electronics"]),
            "company_url" => $this->faker->url(),
            "telephone_number" => $this->faker->phoneNumber(),
            "established" => $this->faker->dateTime(),
            "number_of_employees" => $this->faker->numberBetween(2, 10),
            "capital" => $this->faker->randomFloat(2, 100),
            "dispatch_business_license" => $this->faker->md5(),
            "area_of_expertise" => $this->faker->randomElement(["Yes", "No"]),
            "specialized_industries" => $this->faker->randomElement(["Yes", "No"]),
            "introduction" => $this->faker->paragraph(),
            "company_information_disclose" => $this->faker->boolean(),
            "user_id" => User::factory(),
            "updater_id" => $this->faker->randomDigit(),
            "deleter_id" => $this->faker->randomDigit(),
        ];
    }
}
