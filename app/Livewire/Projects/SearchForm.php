<?php

namespace App\Livewire\Projects;

use App\Models\Category;
use Livewire\Component;

class SearchForm extends Component
{
    public ?string $search = '';
    public $startingDate = '';
    public $endDate = '';
    public ?bool $available_immediately = false;
    public ?bool $available_future = false;
    public ?bool $available_inquire = false;
    public ?array $availability = [];
    public ?array $commercial_flow = [];
    public ?array $age = [];
    public $gender = '';
    public ?array $nationality = [];
    public ?array $workLocation = [];
    public ?array $trade_classification = [];
    public ?array $contract_classification = [];
    public ?array $affiliation = [];
    public ?array $contract = [];
    public ?array $interview = [];
    public $min_salary = '';
    public $max_salary = '';
    public $category = [];
    public $categories = [];
    public ?string $location = '';

    protected $listeners = ['clearSearch' => 'clear'];

    public function updated(): void
    {
        $this->dispatch('filterUpdated', [
            'search' => $this->search,
            'starting_date' => $this->startingDate,
            'end_date' => $this->endDate,
            'available_immediately' => $this->available_immediately,
            'available_future' => $this->available_future,
            'available_inquire' => $this->available_inquire,
            'availability' => $this->availability,
            'age' => $this->age,
            'gender' => $this->gender,
            'nationality' => $this->nationality,
            'work_location' => $this->workLocation,
            'trade_classification' => $this->trade_classification,
            'contract_classification' => $this->contract_classification,
            'affiliation' => $this->affiliation,
            'commercial_flow' => $this->commercial_flow,
            'contract' => $this->contract,
            'interview' => $this->interview,
            'min_salary' => $this->min_salary,
            'max_salary' => $this->max_salary,
            'category' => $this->category,
            'location' => $this->location,
        ]);
    }

    public function clear(): void
    {
        $this->search = '';
        $this->startingDate = '';
        $this->endDate = '';
        $this->available_immediately = false;
        $this->available_future = false;
        $this->available_inquire = false;
        $this->availability = [];
        $this->gender = '';
        $this->age = array();
        $this->nationality = array();
        $this->workLocation = array();
        $this->trade_classification = array();
        $this->contract_classification = array();
        $this->affiliation = array();
        $this->commercial_flow = array();
        $this->contract = array();
        $this->interview = array();
        $this->min_salary = '';
        $this->max_salary = '';
        $this->category = array();
        $this->location = '';

        $this->updated();
    }

    /** Let's define nationality for the user. */
    public $nationalities = ['confirmed', 'unconfirmed'];

    public $interviews = [
        [1, 2],
        [3, 5],
    ];

    public function mount(): void
    {
        $this->categories = Category::all();
    }
    public function render()
    {
        return view('livewire.projects.search-form');
    }
}
