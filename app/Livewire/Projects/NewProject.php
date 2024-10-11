<?php

namespace App\Livewire\Projects;

use App\Livewire\Forms\ProjectRegistrationForm;
use App\Models\Category;
use App\Models\Feature;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Component;

class NewProject extends Component
{
    public ?Project $project;
    public User $user;
    public ProjectRegistrationForm $form;

    public bool $confirmOpeningModal = false;

    public $categories = '';
    public $features = '';
    public array $project_features = [];
    public array $state = [];

    public $listeners = ['updateProjectDescription'];

    public function mount(Project $project): void
    {
        $this->categories = Category::limit(2)->get();
        $this->features = Feature::limit(15)->get();

        $this->project_features = $project->project_features ?? [];
        $this->form->setProject($project);
    }

    public function updateProjectDescription($value): void
    {
        $this->form->project_description = $value;
    }

    public function updateOrCreateProject(): void
    {
            $this->validate();

            $this->project ? $this->form->update() : $this->form->save();
    }

    public function render()
    {
        return view('livewire.projects.new-project');
    }
}
