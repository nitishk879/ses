<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Contracts\View\View;

/** The CV matching screen for one project. */
class ProjectMatchController extends Controller
{
    public function index(Project $project): View
    {
        abort_unless($project->isMatchableBy(auth()->user()), 403);

        return view('projects.matches', ['project' => $project]);
    }
}
