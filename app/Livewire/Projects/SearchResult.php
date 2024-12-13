<?php

namespace App\Livewire\Projects;

use App\Enums\InterviewEnum;
use App\Models\Category;
use App\Models\Project;
use App\Models\SubCategory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SearchResult extends Component
{
    use WithPagination;
    public Project $project;

    #[Url]
    public ?string $search = '';
    #[Url]
    public $startingDate = '';
    #[Url]
    public $endDate = '';
    #[Url]
    public bool $available_immediately = false;
    #[Url]
    public bool $available_future = false;
    #[Url]
    public bool $available_inquire = false;
    #[Url]
    public ?array $availability = [];
    #[Url]
    public $gender = '';
    #[Url]
    public ?array $nationality = [];
    #[Url]
    public ?array $age = [];
    #[Url]
    public ?array $workLocation = array();
    #[Url]
    public ?array $trade_classification = array();
    #[Url]
    public ?array $contract_classification = array();
    #[Url]
    public ?array $affiliation = array();
    #[Url]
    public ?array $commercial_flow = array();
    #[Url]
    public ?array $contract = array();
    #[Url]
    public ?array $interview = array();
    #[Url]
    public $min_salary = '';
    #[Url]
    public $max_salary = '';
    #[Url]
    public ?array $category = [];
    #[Url]
    public ?array $subcategories = [];
    #[Url]
    public ?array $work_mode = [];
    #[Url]
    public ?string $location = '';
    #[Url]
    public $sortBy = 'created_at';
    #[Url]
    public $sortDirection = 'asc';

    public ?int $id = null;
    public ?int $pages = 10;

    /**
     * Let's update values on change or on event occurrence
     *
    */
    protected $listeners = ['filterUpdated' => 'updateFilters'];

    /**
     * Following variables should update on changes
     *
     * @param $filters
     * @return void
     */
    public function updateFilters($filters): void
    {
        $this->search = $filters['search'];
        $this->startingDate = $filters['starting_date'];
        $this->endDate = $filters['end_date'];
        $this->available_immediately = $filters['available_immediately'];
        $this->available_future = $filters['available_future'];
        $this->available_inquire = $filters['available_inquire'];
        $this->availability = $filters['availability'];
        $this->age = $filters['age'];
        $this->gender = $filters['gender'];
        $this->nationality = $filters['nationality'];
        $this->workLocation = $filters['work_location'];
        $this->trade_classification = $filters['trade_classification'];
        $this->contract_classification = $filters['contract_classification'];
        $this->affiliation = $filters['affiliation'];
        $this->commercial_flow = $filters['commercial_flow'];
        $this->contract = $filters['contract'];
        $this->interview = $filters['interview'];
        $this->min_salary = $filters['min_salary'];
        $this->max_salary = $filters['max_salary'];
        $this->category = $filters['category'];
        $this->subcategories = $filters['subcategories'];
        $this->location = $filters['location'];
        $this->work_mode = $filters['work_mode'];

        $this->resetPage();
    }

    /**
     * Let's sort results
     *
     * @param $field
     * @return void
     */
    public function sortBy($field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortBy = $field;
    }

    /**
     * Let's render page view
     *
     * @return Factory|View|Application|\Illuminate\View\View
     */
    public function render(): Factory|Application|View|\Illuminate\View\View
    {
        // Build the query based on filters
        $query = Project::query()->with('locations');

        // Apply filters conditionally
        if (!empty($this->search)) {
            $query->where('title', 'like', '%' . $this->search . '%')
                ->orWhere('project_description', 'like', '%' . $this->search . '%')
                ->orWhere('personnel_requirement', 'like', '%' . $this->search . '%');
        }

        if (!empty($this->startingDate) && !empty($this->endDate)) {
            $query->where('contract_start_date', '<=', $this->startingDate)
                ->where('contract_end_date', '>=', $this->endDate);
        }

        if(!empty($this->trade_classification)){
            $query->whereIn('trade_classification', $this->trade_classification);
        }

        if(!empty($this->contract_classification)){
            $query->whereIn('contract_classification', $this->contract_classification);
        }

//        if(!empty($this->category)){
//            $subCategories = SubCategory::whereIn('category_id', $this->category)->pluck('id')->toArray();
//            $query->whereHas('subCategories', function (Builder $query) use ($subCategories) {
//                $query->whereIn('sub_category_id', $subCategories);
//            });
//        }

        if (!empty($this->subcategories)) {
            $query->whereHas('subcategories', function (Builder $query) {
                $query->whereIn('sub_category_id', $this->subcategories);
            });
        }

        if (!empty($this->work_mode)){
            $query->whereJsonContains('work_location_prefer', $this->work_mode);
        }

        // Filter by work location
        if (!empty($this->workLocation)) {
            $query->whereHas('locations', function ($q) {
                $q->whereIn('locations.id', $this->workLocation);
            });
        }

        if (!empty($this->location)) {
            $query->whereHas('locations', function ($q) {
                $q->where('locations.title', 'like', '%' . $this->location . '%');
            });
        }

        if (!empty($this->affiliation)) {
            $query->whereIn('affiliation', $this->affiliation);
        }

        if (!empty($this->commercial_flow)) {
            $query->whereIn('commercial_flow', $this->commercial_flow);
        }

        if (!empty($this->interview)) {
            $interviewCounts = [];

            // Check if $this->interview is an array
            if (is_array($this->interview)) {
                foreach ($this->interview as $interview) {
                    // Convert the interview value to its corresponding enum
                    $interviewEnum = InterviewEnum::tryFrom($interview);

                    // If the enum is valid, get its array representation
                    if ($interviewEnum) {
                        $interviewArray = InterviewEnum::toArray($interviewEnum->value);

                        // Merge the arrays, in case each InterviewEnum::toArray returns multiple values
                        $interviewCounts = array_merge($interviewCounts, $interviewArray);
                    }
                }
            }
            // Ensure interview counts are unique
            $interviewCounts = array_unique($interviewCounts);

            $query->whereIn('number_of_interviewers', $interviewCounts);
        }

        // Filter by salary range
        if (!empty($this->min_salary) && !empty($this->max_salary)) {
            $query->where('minimum_price', '>=', $this->min_salary)->where('maximum_price', '<=', $this->max_salary);
        }

        // Sort the results
        $query->orderBy($this->sortBy, $this->sortDirection);

        return view('livewire.projects.search-result', ['projects' => $query->paginate($this->pages)]);
    }
}
