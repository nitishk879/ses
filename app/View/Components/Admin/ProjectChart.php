<?php

namespace App\View\Components\Admin;

use App\Models\Project;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProjectChart extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $projects = Project::selectRaw('COUNT(id) as count, MONTH(created_at) as month')
            ->groupBy('month')
            ->pluck('count', 'month');

        // Labels (e.g., months) and data (e.g., number of projects per month)
        $labels = $projects->keys()->map(function($month) {
            return date('F', mktime(0, 0, 0, $month, 1)); // Get month name
        });

        $datasets = $projects->values();
        return view('components.admin.project-chart', compact('projects', 'labels', 'datasets'));
    }
}
