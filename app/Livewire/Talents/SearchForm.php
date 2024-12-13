<?php

namespace App\Livewire\Talents;

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
    public ?array $age = [];
    public $gender = '';
    public ?array $nationality = [];
    public ?array $workLocation = [];
    public ?array $affiliation = [];
    public ?array $contract = [];
    public $min_salary = '';
    public $max_salary = '';
    public $category = [];
    public $categories = [];
    public $subcategories = [];
    public $work_mode = [];

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
            'affiliation' => $this->affiliation,
            'contract' => $this->contract,
            'min_salary' => $this->min_salary,
            'max_salary' => $this->max_salary,
            'category' => $this->category,
            'subcategories' => $this->subcategories,
            'work_mode' => $this->work_mode,
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
        $this->affiliation = array();
        $this->contract = array();
        $this->min_salary = '';
        $this->max_salary = '';
        $this->category = array();
        $this->subcategories = array();
        $this->work_mode = array();

        $this->updated();
    }

    /** Let's define nationality for the user. */
    public $nationalities = ['japanese', 'other'];

    public $ages = [
        '20-30',
        '30-40',
        '40-50',
        '50-60',
        '60+',
    ];

    public function mount(): void
    {
        $this->categories = Category::with('subcategories:id')->get();
    }

    public function render()
    {
        return view('livewire.talents.search-form');
    }
}
