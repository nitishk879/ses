<?php

namespace App\Livewire\Search;

use Livewire\Component;

class SearchForm extends Component
{
    public ?string $search = '';
    public $email = '';
    public $role = '';

    protected $listeners = ['clearSearch' => 'clear'];

    public function updated(): void
    {
        $this->dispatch('filterUpdated', [
            'search' => $this->search,
            'email' => $this->email,
            'role' => $this->role,
        ]);
    }

    public function clear(): void
    {
        $this->search = '';
        $this->email = '';
        $this->role = '';
        $this->updated();
    }

    public function render()
    {
        return view('livewire.search.search-form');
    }
}
