<?php

namespace App\Livewire\Projects;

use App\Http\Traits\ProjectTalentMatchingTrait;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ApplyForProject extends Component
{
    use ProjectTalentMatchingTrait;
    public Project $project;
    public $talents = '';

    public function mount(Project $project): void
    {
        $this->project = $project;

        $myTalents = Auth::user()->company->talents;

        $talentsWithScores = $myTalents->map(function($talent) use ($project) {
            $talent->setAttribute('score', $this->calculateMatch($project, $talent));
            return $talent;
        })
            ->filter(function($talent) {
            return $talent->score >= 450; // Keep only those with a score of 45% or higher
        });

        // Sort talents by match score in descending order
        $this->talents = collect($talentsWithScores)->sortBy('score', 1, 'desc')->values()->all();
    }


    public function render()
    {
        return view('livewire.projects.apply-for-project');
    }
}
