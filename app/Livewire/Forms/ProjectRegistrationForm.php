<?php

namespace App\Livewire\Forms;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProjectRegistrationForm extends Form
{
    public ?Project $project;
    #[Validate('required|min:5')]
    public string $title = '';

    #[Validate('required|min:5')]
    public $project_description = '';

    public $slug = "";
    public $minimum_price = "";
    public $maximum_price = "";
    #[Validate('required')]
    public bool $skill_matching = false;
    #[Validate('required')]
    public bool $accept = false;
    #[Validate('required')]
    public bool $remote_operation_possible = false;
    public $contract_start_date = "";
    public $contract_end_date = "";
    #[Validate('required')]
    public bool $possible_to_continue = false;

    public $personnel_requirement = "";
    #[Validate('required')]
    public bool $project_finalized = false;
    public $trade_classification = [];
    public $contract_classification = [];
    public $eligibility = [];
    #[Validate('required')]
    public $deadline = "";
    #[Validate('required')]
    public ?int $number_of_application = 0;
    public ?int $number_of_interviewers = 1;
    public $commercial_flow = "";
    public ?string $person_in_charge = "";
    #[Validate('required')]
    public bool $is_public = false;
    #[Validate('required')]
    public bool $company_info_disclose = false;
    #[Validate('required')]
    public ?int $company_id = null;
    #[Validate('required')]
    public ?int $user_id = null;
    public ?int$updater_id = null;
    public ?int$deleter_id = null;

    public function setProject(Project $project): void
    {
        $this->project = $project;
        $this->title = $project->title ?? '';
        $this->project_description = $project->project_description ?? '';
        $this->slug = $project->slug ?? '';
        $this->minimum_price = $project->minimum_price ?? '';
        $this->maximum_price = $project->maximum_price ?? '';
        $this->skill_matching = $project->skill_matching ?? false;
        $this->remote_operation_possible = $project->remote_operation_possible ?? false;
        $this->contract_start_date = $project->contract_start_date ?? '';
        $this->contract_end_date = $project->contract_end_date ?? '';
        $this->possible_to_continue = $project->possible_to_continue ?? false;
        $this->personnel_requirement = $project->personnel_requirement ?? '';
        $this->project_finalized = $project->project_finalized ?? false;
        $this->trade_classification = $project->trade_classification ?? [];
        $this->contract_classification = $project->contract_classification ?? [];
        $this->deadline = $project->deadline ?? '';
        $this->number_of_application = $project->number_of_application ?? 0;
        $this->number_of_interviewers = $project->number_of_interviewers ?? 1;
        $this->commercial_flow = $project->commercial_flow ?? '';
        $this->person_in_charge = $project->person_in_charge ?? Auth::user()->name;
        $this->is_public = $project->is_public ?? false;
        $this->company_info_disclose = $project->company_info_disclose ?? false;
        $this->company_id = $project->company_id ?? Auth::user()->company->id ?? 0;
        $this->user_id = $project->user_id ?? Auth::user()->id;
        $this->updater_id = $project->updater_id ?? 0;
        $this->deleter_id = $project->deleter_id ?? 0;
    }

    public function save(): void
    {
        $this->validate();

        Project::create($this->only([
            "title",
            "slug",
            "minimum_price",
            "maximum_price",
            "skill_matching",
            "accept",
            "remote_operation_possible",
            "contract_start_date",
            "contract_end_date",
            "possible_to_continue",
            "project_description",
            "personnel_requirement",
            "project_finalized",
            "trade_classification",
            "contract_classification",
            "deadline",
            "number_of_application",
            "number_of_interviewers",
            "commercial_flow",
            "person_in_charge",
            "is_public",
            "company_info_disclose",
            "company_id",
            "user_id"]));
    }

    public function update(): void
    {
        $this->validate();

        $this->project->update(
            $this->all()
        );
    }
}
