<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Country>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->title(),
            'slug' => $this->faker->slug(),
            'country_code' => $this->faker->countryCode(),
            'phone_code' => $this->faker->phoneNumber(),
            'currency_symbol' => $this->faker->currencyCode(),
        ];
    }
}
