<?php

namespace App\Http\Controllers;

use App\Enums\TalentStatusEnum;
use App\Events\TalentInvitationEvent;
use App\Models\Category;
use App\Models\Feature;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::all();

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::whereHas('subcategories')->orderBy('title')->get();
        $features = Feature::all();

        return view('projects.create', compact('categories', 'features'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            "title" => 'required|unique:projects',
            "minimum_price" => 'required|int',
            "maximum_price" => 'required|int',
            "skill_matching" => 'nullable',
            "accept" => 'nullable',
            "remote_operation_possible" => 'nullable',
            "contract_start_date" => 'required',
            "contract_end_date" => 'required',
            "possible_to_continue" => 'nullable',
            "project_description" => 'required',
            "personnel_requirement" => 'required',
            "project_finalized" => 'nullable',
            "trade_classification" => 'required|int',
            "contract_classification" => 'required',
            "languages" => 'required',
            "workLocations" => 'nullable',
            "deadline" => 'nullable',
            "experience" => 'nullable|array',
            "scoring" => 'nullable',
            "number_of_application" => 'nullable',
            "number_of_interviewers" => 'nullable',
            "commercial_flow" => 'nullable',
            "person_in_charge" => 'nullable',
            "eligibility" => 'nullable',
            "is_public" => 'nullable',
            "company_info_disclose" => 'nullable',
            "locations" => 'required'
        ]);

        $project = Project::create([
            "title" => $validated["title"] ?? '',
            "slug" => Str::slug($validated['title'], '-'),
            "minimum_price" => $validated["minimum_price"] ?? '',
            "maximum_price" => $validated["maximum_price"] ?? '',
            "skill_matching" => $validated["skill_matching"] ?? false,
            "accept" => $validated["accept"] ?? false,
            "remote_operation_possible" => $validated["remote_operation_possible"] ?? false,
            "contract_start_date" => $validated["contract_start_date"] ?? '',
            "contract_end_date" => $validated["contract_end_date"] ?? '',
            "possible_to_continue" => $validated["possible_to_continue"] ?? false,
            "project_description" => $validated["project_description"] ?? '',
            "personnel_requirement" => $validated["personnel_requirement"] ?? '',
            "person_in_charge" => $validated["person_in_charge"] ?? auth()->user()->name,
            "is_public" => $validated["is_public"] ?? false,
            "company_info_disclose" => $validated["company_info_disclose"] ?? false,
            "contract_classification" => $validated["contract_classification"] ?? '',
            "deadline" => $validated["deadline"] ?? '',
            "scoring" => $validated["scoring"] ?? [50],
            "languages" => $validated["languages"] == 3 ? [1,2] : [$validated["languages"]] ?? '',
            'work_location_prefer' => $validated["workLocations"] ?? '',
            "affiliation" => $validated["eligibility"] ?? '',
            "experience" => json_encode($validated["experience"]) ?? '',
            "trade_classification" => (int) $validated["trade_classification"] ?? '',
            "number_of_application" => $validated["number_of_application"] ?? '',
            "number_of_interviewers" => $validated["number_of_interviewers"] ?? '',
            "commercial_flow" => $validated["commercial_flow"] ?? '',
            "company_id" => auth()->user()->company->id ?? 0,
            "user_id" => auth()->user()->id ?? 0,
        ]);

        $project->subCategories()->attach($request->input('category_id'));

        $project->features()->attach($request->input("project_features") ?? []);

        $project->locations()->attach($request->input("locations") ?? []);

        return redirect()->route('project.index')->with('success', 'Project created successfully.');

    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('project.index')->with('success', 'Project deleted successfully.');
    }

    /**
     * Invite talents' to Project
    */

    public function invite(Request $request, Project $project)
    {
        $project->talents()->detach($request->input('talents'));

        $project->talents()->attach($request->input("talents"), [
                'status' => TalentStatusEnum::invited,
                'interview_count' => 0,
                'remarks' => $request->input("invitation_letter")
            ]
        );

        TalentInvitationEvent::dispatch($project);

        return redirect()->back()->with([
            'message' => 'Data saved successfully!',
            'type' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function chart($term)
    {
        $data = $this->generateDataForPeriod($term);

        return response()->json($data);
    }

    public function generateDataForPeriod($term)
    {
        switch ($term) {
            case 'week':
                // Group data by day of the week
                $entries = DB::table('projects')
                    ->select(DB::raw('DAYNAME(created_at) as day, COUNT(*) as count'))
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->groupBy('day')
                    ->orderBy('created_at', 'asc')
                    ->pluck('count', 'day');

                return [
                    'labels' => $entries->keys()->toArray(),
                    'data' => $entries->values()->toArray(),
                ];

            case 'month':
                // Group data by week of the month
                $entries = DB::table('projects')
                    ->select(DB::raw('WEEK(created_at) as week, COUNT(*) as count'))
                    ->whereMonth('created_at', now()->month)
                    ->groupBy('week')
                    ->orderBy('week', 'asc')
                    ->pluck('count', 'week');

                return [
                    'labels' => $entries->keys()->toArray(),
                    'data' => $entries->values()->toArray(),
                ];

            case 'year':
                // Group data by month
                $entries = DB::table('projects')
                    ->select(DB::raw('MONTHNAME(created_at) as month, COUNT(*) as count'))
                    ->whereYear('created_at', now()->year)
                    ->groupBy('month')
                    ->orderBy('created_at', 'asc')
                    ->pluck('count', 'month');

                return [
                    'labels' => $entries->keys()->toArray(),
                    'data' => $entries->values()->toArray(),
                ];

            default:
                return ['labels' => [], 'data' => []];
        }
    }
}
