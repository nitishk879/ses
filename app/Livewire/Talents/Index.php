<?php

namespace App\Livewire\Talents;

use App\Models\Category;
use App\Models\Project;
use App\Models\Talent;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    public Talent $talent;

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
    public ?array $affiliation = array();
    #[Url]
    public ?array $contract = array();
    #[Url]
    public $min_salary = '';
    #[Url]
    public $max_salary = '';
    #[Url]
    public ?array $category = [];
    #[Url]
    public $sortBy = 'created_at';
    #[Url]
    public $sortDirection = 'asc';

    public ?int $id = null;
    public ?int $pages = 10;

    protected $listeners = ['filterUpdated' => 'updateFilters'];

    /**
     * Indicates if user deletion is being confirmed.
     *
     * @var bool
     */
    public bool $confirmingDisplayingResume = false;

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
        $this->affiliation = $filters['affiliation'];
        $this->contract = $filters['contract'];
        $this->min_salary = $filters['min_salary'];
        $this->max_salary = $filters['max_salary'];
        $this->category = $filters['category'];

        $this->resetPage();
    }

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
     * Confirm that the user would like to delete their account.
     *
     * @param $id
     * @return void
     */
    public function confirmDisplayResume($id): void
    {
        $this->resetErrorBag();

        $this->talent = Talent::findOrFail($id);

//        $this->dispatchBrowserEvent('confirming-display-resume');

        $this->confirmingDisplayingResume = true;
    }
    public $ages = [
        '20-30',
        '30-40',
        '40-50',
        '50-60',
        '60',
    ];

    /**
     * Check if there is any filter or search keyword set for the list of talents
     *
     * @return Factory|View|Application|\Illuminate\View\View
     */
    public function render(): Factory|Application|View|\Illuminate\View\View
    {
        // Build the query based on filters
        $query = Talent::query()->with(['user', 'locations']);

        // Apply filters conditionally
        if (!empty($this->search)) {
            $query->where('experience_pr', 'like', '%' . $this->search . '%')
                ->orWhere('qualifications', 'like', '%' . $this->search . '%')
                ->orWhereHas('user', function ($query) {
                    $query->where('firstname', 'like', '%' . $this->search . '%')
                        ->orWhere('lastname', 'like', '%' . $this->search . '%');
                });
        }

        if (!empty($this->startingDate) && !empty($this->endDate)) {
            $query->whereBetween('available_date', [$this->startingDate, $this->endDate]);
        }

        if(!empty($this->availability)){
            $query->whereIn('availability', $this->availability);
        }

        if(!empty($this->category)){
            $subCategories = Category::whereIn('id', $this->category)->pluck('id')->toArray();
            $query->whereHas('subCategories', function (Builder $query) use ($subCategories) {
                $query->whereIn('sub_category_id', $subCategories);
            });
        }

        // Filter by nationality
        if (!empty($this->nationality)) {
            $query->whereHas('user', function (Builder $n){
                return $n->whereIn('nationality', $this->nationality);
            });
        }

        // Filter by age (range)
        if (!empty($this->age)) {
            $ageRanges = $this->age;
            $query->whereHas('user', function (Builder $q) use ($ageRanges) {
                $q->where(function($query) use ($ageRanges) {
                    foreach ($ageRanges as $range) {
                        if (str_contains($range, '+')) {
                            // Handle the "60+" case
                            $minAge = (int) rtrim($range, '+');
                            $query->orWhere('date_of_birth', '<=', now()->subYears($minAge)->format('Y-m-d'));
                        } else {
                            // Handle the other ranges like "20-30", "30-40"
                            [$minAge, $maxAge] = explode('-', $range);
                            $query->orWhereBetween('date_of_birth', [
                                now()->subYears($maxAge)->format('Y-m-d'),
                                now()->subYears($minAge)->format('Y-m-d')
                            ]);
                        }
                    }
                });
            });

        }

        // Filter by gender
        if (!empty($this->gender)) {
            $query->whereHas('user', function (Builder $q) {
                return $q->where('gender', $this->gender);
            });
        }

        // Filter by work location
        if (!empty($this->workLocation)) {
            $query->whereHas('locations', function ($q) {
                $q->whereIn('locations.id', $this->workLocation);
            });
        }

        if (!empty($this->affiliation)) {
            $query->whereIn('affiliation', $this->affiliation);
        }

        if (!empty($this->contract)) {
            $contractFields = [
                'quasi_delegation_possible' => 'quasi_delegation_possible', //quasi_delegation_possible
                'available_for_contract' => 'available_for_contract', //available_for_contract
                'available_for_dispatch' => 'available_for_dispatch' //available_for_dispatch
            ];

            foreach ($this->contract as $contractType){
                if (array_key_exists($contractType, $contractFields)) {
                    $query->where($contractFields[$contractType], '=', true);
                }
            }
        }

        // Filter by salary range
        if (!empty($this->min_salary) && !empty($this->max_salary)) {
            $query->where('min_monthly_price', '>=', $this->min_salary)->where('max_monthly_price', '<=', $this->max_salary);
        }

        // Sort the results
        $query->orderBy($this->sortBy, $this->sortDirection);

        return view('livewire.talents.index', ['talents' => $query->paginate($this->pages) ]);
    }
}
