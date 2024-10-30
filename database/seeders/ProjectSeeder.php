<?php

namespace Database\Seeders;

use App\Enums\LangEnum;
use App\Enums\WorkLocationEnum;
use App\Models\Feature;
use App\Models\Industry;
use App\Models\Location;
use App\Models\Project;
use App\Models\SubCategory;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::truncate();
        $datafile = File::get(public_path('/dataset/projects.json'));
        $projects = json_decode($datafile);

        foreach ($projects as $project) {
            $currentProject = Project::create([
                "title" => $project->title,
                "slug" => Str::slug($project->title, '-'),
                "minimum_price" => $project->minimum_price,
                "maximum_price" => $project->maximum_price,
                "skill_matching" => $project->skill_matching,
                "accept" => $project->accept,
                "experience" => $project->experience ?? json_encode([1,2,3]),
                "languages" => $project->languages ?? LangEnum::toArray(LangEnum::en->value),
                "remote_operation_possible" => $project->remote_operation_possible,
                "contract_start_date" => $project->contract_start_date ?? today()->addDays(7),
                "contract_end_date" => $project->contract_end_date ?? today()->addMonth(),
                'work_location_prefer' => $project->work_location_prefer ?? json_encode(random_int(1, count(WorkLocationEnum::cases()))),
                "possible_to_continue" => $project->possible_to_continue ?? false,
                "project_description" => $project->project_details,
                "personnel_requirement" => $project->personnel_requirement,
                "project_finalized" => $project->project_finalized,
                "affiliation" => $project->affiliation,
                "trade_classification" => $project->trade_classification,
                "contract_classification" => $project->contract_classification,
                "deadline" => $project->deadline,
                "number_of_application" => $project->number_of_application,
                "number_of_interviewers" => $project->number_of_interviewers,
                "commercial_flow" => $project->commercial_flow,
                "person_in_charge" => $project->person_in_charge,
                "is_public" => $project->is_public,
                "company_info_disclose" => $project->company_info_disclose,
                "company_id" => $project->company_id,
                "updater_id" => $project->updater_id,
                "deleter_id" => $project->deleter_id,
                'user_id' => $project->user_id,
            ]);

//            $currentProject->subCategories()->attach([random_int(1, SubCategory::count()), random_int(1, SubCategory::count())]);
//            $currentProject->locations()->attach([random_int(1, 46), random_int(1, 46), random_int(1, 46)]);
//            $currentProject->features()->attach([random_int(1, Feature::count()), random_int(1, Feature::count())]);
//            $currentProject->industries()->attach([random_int(1, Industry::count()), random_int(1, Industry::count())]);
            $currentProject->subCategories()->attach($this->uniqueRandomIds(1, SubCategory::count(), 2));
            $currentProject->locations()->attach($this->uniqueRandomIds(1, 46, 3));
            $currentProject->features()->attach($this->uniqueRandomIds(1, Feature::count(), 2));
            $currentProject->industries()->attach($this->uniqueRandomIds(1, Industry::count(), 2));
        }
    }

    public function uniqueRandomIds(int $min, int $max, int $count): array
    {
        if ($count > ($max - $min + 1)) {
            throw new Exception("Count is greater than the number of unique values in the range.");
        }

        $uniqueIds = [];
        while (count($uniqueIds) < $count) {
            $randomId = random_int($min, $max);
            $uniqueIds[$randomId] = true; // Using associative keys for uniqueness
        }

        return array_keys($uniqueIds);
    }
}
