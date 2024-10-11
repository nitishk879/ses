<?php

namespace App\Livewire\Search;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class SearchResult extends Component
{
    use WithPagination;

    public ?string $search = '';
    public $email = '';
    public $role = '';
    public ?int $pages = 10;
    public $sortBy = 'id';
    public $sortDirection = 'asc';

    protected $listeners = ['filterUpdated' => 'updateFilters'];

    public function updateFilters($filters): void
    {
        $this->search = $filters['search'];
        $this->email = $filters['email'];
        $this->role = $filters['role'];
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
    public function render()
    {
        if($this->search){
            $users = User::query()
                ->when($this->search, function($query) {
                    return $query->where('firstname', 'like', '%' . $this->search . '%')
                        ->orWhere('lastname', 'like', '%' . $this->search . '%');
                })
                ->when($this->email, function($query) {
                    return $query->where('email', 'like', '%' . $this->email . '%');
                })
                ->when($this->role, function($query) {
                    return $query->where('country', $this->role);
                })
                ->orderBy($this->sortBy, $this->sortDirection)
                ->paginate($this->pages);
        }
        else{
            $users = User::paginate($this->pages);
        }

        return view('livewire.search.search-result', ['users' => $users]);
    }
}
