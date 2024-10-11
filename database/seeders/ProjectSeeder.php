<?php

namespace Database\Seeders;

use App\Models\Project;
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
            Project::create([
                "title" => $project->title,
                "slug" => Str::slug($project->title, '-'),
                "minimum_price" => $project->minimum_price,
                "maximum_price" => $project->maximum_price,
                "skill_matching" => $project->skill_matching,
                "accept" => $project->accept,
                "remote_operation_possible" => $project->remote_operation_possible,
                "contract_start_date" => $project->contract_start_date,
                "contract_end_date" => $project->contract_end_date,
                "possible_to_continue" => $project->possible_to_continue ?? false,
                "project_description" => $project->project_details,
                "personnel_requirement" => $project->personnel_requirement,
                "project_finalized" => $project->project_finalized,
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
            ])->subCategories()->attach([1, 2, 3]);
        }
    }
}
