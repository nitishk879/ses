<?php

namespace App\Livewire\Companies;

use App\Models\Company;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public ?string $search = '';

    public function search(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.companies.index', [
            'companies' => Company::where('company_name', 'like', '%'.$this->search.'%')->paginate(10)
        ]);
    }
}
