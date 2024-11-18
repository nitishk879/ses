<?php

namespace Database\Factories;

use App\Enums\AffiliationEnum;
use App\Enums\ContractClassificationEnum;
use App\Enums\InterviewEnum;
use App\Enums\LangEnum;
use App\Enums\ScoringEnum;
use App\Enums\TradeClassification;
use App\Enums\WorkLocationEnum;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle(),
            'slug' => $this->faker->slug(8),
            'minimum_price' => $this->faker->numberBetween(1000, 9000),
            'maximum_price' => $this->faker->numberBetween(1000, 9000),
            'skill_matching' => $this->faker->boolean(),
            'accept' => $this->faker->boolean(),
            'experience' => $this->faker->randomElement([1,2,3]),
            'languages' => $this->faker->randomElements(
                array_map(fn($lang) => $lang->value, LangEnum::cases()),
                $this->faker->numberBetween(1, count(LangEnum::cases()))
            ),
            'work_location_prefer' => $this->faker->randomElements(
                array_map(fn($workLocation) => $workLocation->value, WorkLocationEnum::cases()),
                $this->faker->numberBetween(1, count(WorkLocationEnum::cases()))
            ),
            'remote_operation_possible' => $this->faker->boolean(),
            'contract_start_date' => $this->faker->dateTime(),
            'contract_end_date' => $this->faker->dateTime(),
            'possible_to_continue' => $this->faker->boolean(),
            'project_description' => $this->faker->text(200),
            'personnel_requirement' => $this->faker->text(200),
            'project_finalized' => $this->faker->boolean(),
            'trade_classification' => $this->faker->randomElement(TradeClassification::cases())->value,
            'scoring' => $this->faker->randomElements(
                array_map(fn($scoring) => $scoring->value, ScoringEnum::cases()),
                $this->faker->numberBetween(1, count(ScoringEnum::cases()))
            ),
            'contract_classification' => $this->faker->randomElement(ContractClassificationEnum::cases())->value,
            'affiliation' =>$this->faker->randomElements(
                array_map(fn($enum) => $enum->value, AffiliationEnum::cases()),
                $this->faker->numberBetween(2, count(AffiliationEnum::cases()))
            ),
            'deadline' => $this->faker->dateTime(),
            'number_of_application' => $this->faker->numberBetween(1, 50),
            'number_of_interviewers' => $this->faker->numberBetween(1, count(InterviewEnum::cases())),
            "commercial_flow" => $this->faker->numberBetween(1, 2),
            "person_in_charge" => $this->faker->text(200),
            "is_public" => $this->faker->boolean(),
            "company_info_disclose" => $this->faker->boolean(),
            'company_id' => $this->faker->numberBetween(1, Company::count()),
            "user_id" => $this->faker->numberBetween(1, User::count()),
            "updater_id" => $this->faker->numberBetween(1, 5),
            "deleter_id" => $this->faker->numberBetween(1, 5),
        ];
    }
}
