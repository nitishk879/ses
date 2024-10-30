<?php

namespace Database\Factories;

use App\Enums\AffiliationEnum;
use App\Enums\ParticipationEnum;
use App\Enums\TalentCharEnum;
use App\Enums\WorkLocationEnum;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Talent>
 */
class TalentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'resume' => $this->faker->filePath(),
            'availability' => $this->faker->randomElement(ParticipationEnum::cases())->value,
            'joining_date' => $this->faker->date(),
            'affiliation' => $this->faker->randomElement(AffiliationEnum::cases())->value,
            'quasi_delegation_possible' => $this->faker->boolean(),
            'available_for_contract' => $this->faker->boolean(),
            'available_for_dispatch' => $this->faker->boolean(),
            'request_for_contract' => $this->faker->boolean(),
            'remote_work_preferred' => $this->faker->boolean(),
            'work_location_prefer' => $this->faker->randomElement(WorkLocationEnum::cases())->value,
            'characteristics' => $this->faker->randomElements(
                array_map(fn($enum) => $enum->value, TalentCharEnum::cases()),
                $this->faker->numberBetween(2, count(TalentCharEnum::cases()))
            ),
            'experience_pr' => $this->faker->text(),
            'qualifications' => $this->faker->text(),
            'min_monthly_price' => $this->faker->randomFloat(2, 100000, 1000000),
            'max_monthly_price' => $this->faker->randomFloat(2, 100000, 1000000),
            'other_desire_conditions' => $this->faker->text(),
            'user_id' => User::factory(),
            'company_id' => rand(1, Company::count()),
        ];
    }
}
