<?php

namespace App\Livewire\Projects;

use App\Enums\TalentStatusEnum;
use App\Http\Traits\ProjectTalentMatchingTrait;
use App\Livewire\Forms\TalentInvitationForm;
use App\Models\Project;
use App\Models\Talent;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ApplyForProject extends Component
{
    use ProjectTalentMatchingTrait;
    public Project $project;
    public $talents = '';
    public TalentInvitationForm $invitation ;

    public $selectedTalents = [];

    public function mount(Project $project): void
    {
        $this->project = $project;

        $auth = Auth::user();

        $myTalents = $auth->hasRole('employer') ? $auth->company->talents : Talent::all();

        $talentsWithScores = $myTalents->map(function($talent) use ($project) {
            $match = $this->calculateMatch($project, $talent);
            // Add score to the talent using put method
            $talent->setAttribute('score', $match['score']);
            $talent->setAttribute('score_detail', $match['details']);

            return $talent;

        })
            ->filter(function($talent) {
            return $talent->score >= 45; // Keep only those with a score of 45% or higher
        });

        // Sort talents by match score in descending order
        $this->talents = collect($talentsWithScores)->sortBy('score', 1, 'desc')->values()->all();
    }

    public function inviteTalent(): void
    {
        $this->validate();
        $project = Project::find($this->project->id);
        $project->talents()->attach($this->selectedTalents,[
            'status' => TalentStatusEnum::invited,
            'interview_count' => 0,
            'remarks' => null
        ]);
        // Clear selected talents after submission
        $this->selectedTalents = [];

        session()->flash('message', 'Talents invited successfully!');
    }

    public function render()
    {
        return view('livewire.projects.apply-for-project');
    }
}
