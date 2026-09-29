<?php

namespace App\Http\Controllers;

use App\Enums\AffiliationEnum;
use App\Enums\CommercialFlow;
use App\Enums\ContractClassificationEnum;
use App\Enums\InterviewEnum;
use App\Enums\TalentStatusEnum;
use App\Enums\TradeClassification;
use App\Enums\WorkLocationEnum;
use App\Events\TalentInvitationEvent;
use App\Models\Category;
use App\Models\Feature;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
        // Only categories with something to tick — see Category::scopeSelectable().
        $categories = Category::selectable()->get();
        $features = Feature::all();

        return view('projects.create', compact('categories', 'features'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate($this->rules());

        $project = Project::create($this->attributesFrom($validated) + [
            "slug" => self::slugFor($validated['title']),
            "company_id" => auth()->user()->company->id ?? 0,
            "user_id" => auth()->user()->id ?? 0,
        ]);

        // The row exists now, so a title that romanised to nothing can take the
        // id as its URL key.
        self::backfillSlug($project);

        $project->subCategories()->attach($request->input('category_id'));
        $project->features()->attach($request->input("project_features") ?? []);
        $project->locations()->attach($request->input("locations") ?? []);

        return redirect()->route('project.index')->with('success', 'Project created successfully.');
    }

    /**
     * The rules the project form is validated against, for create and update.
     *
     * Shared because the two screens post the same fields — they render from one
     * partial — and a second copy of forty rules is a second thing to forget
     * when a field changes.
     *
     * @return array<string, mixed>
     */
    private function rules(?Project $project = null): array
    {
        return [
            /*
             * `unique:projects` has to ignore the row being edited, or saving a
             * project without renaming it fails on its own title.
             */
            "title" => ['required', Rule::unique('projects')->ignore($project)],
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
            "trade_classification" => ['required', Rule::enum(TradeClassification::class)],
            "contract_classification" => ['required', Rule::enum(ContractClassificationEnum::class)],
            "languages" => 'required',
            "workLocations" => 'nullable',
            "deadline" => 'nullable',
            "number_of_application" => 'nullable',
            /*
             * The four enum-cast columns are validated against their enum.
             *
             * Without this an unexpected value reaches Project::create() and
             * dies inside the cast — `InterviewEnum::from('')` is a TypeError,
             * which Laravel renders as a 500. A recruiter filling in a form
             * gets a blank error page and no idea which field to fix, which is
             * how "registration cannot be completed" was reported.
             *
             * `commercial_flow` is `required`, not `nullable`: the column is
             * NOT NULL and the form marks the field required, so nullable here
             * was the odd one out of the three.
             */
            "number_of_interviewers" => ['nullable', Rule::enum(InterviewEnum::class)],
            "commercial_flow" => ['required', Rule::enum(CommercialFlow::class)],
            "person_in_charge" => 'nullable',
            "eligibility" => 'nullable',
            "is_public" => 'nullable',
            "company_info_disclose" => 'nullable',
            "locations" => 'required'
        ];
    }

    /**
     * Validated input, as the columns want it.
     *
     * Shared by store() and update() so the two cannot disagree about what a
     * field means — the casts on this model are unforgiving, and every rule
     * about blanks and enums is written once here.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function attributesFrom(array $validated, ?Project $project = null): array
    {
        return [
            "title" => $validated["title"],
            "minimum_price" => $validated["minimum_price"],
            "maximum_price" => $validated["maximum_price"],
            "contract_start_date" => $validated["contract_start_date"],
            "contract_end_date" => $validated["contract_end_date"],
            "project_description" => $validated["project_description"],
            "personnel_requirement" => $validated["personnel_requirement"],
            "person_in_charge" => $validated["person_in_charge"] ?? auth()->user()->name,
            /*
             * The switches post `0` from a hidden field and `1` when ticked,
             * so an absent key now means the form was not this form at all —
             * not "unticked". They used to render `value=""`, which stored
             * false for every switch a recruiter turned on.
             */
            "skill_matching" => (bool) ($validated["skill_matching"] ?? false),
            "accept" => (bool) ($validated["accept"] ?? false),
            "remote_operation_possible" => (bool) ($validated["remote_operation_possible"] ?? false),
            "possible_to_continue" => (bool) ($validated["possible_to_continue"] ?? false),
            "project_finalized" => (bool) ($validated["project_finalized"] ?? false),
            "is_public" => (bool) ($validated["is_public"] ?? false),
            "company_info_disclose" => (bool) ($validated["company_info_disclose"] ?? false),
            /*
             * `trade_classification` was validated as required and then never
             * written. The column is NOT NULL, so every registration ended in
             * an integrity-constraint 500 — the form asked for the answer,
             * refused to continue without it, and threw it away.
             */
            "trade_classification" => $validated["trade_classification"],
            "contract_classification" => $validated["contract_classification"],
            "commercial_flow" => $validated["commercial_flow"],
            /*
             * Unanswered optional fields are stored as null, not ''.
             *
             * Every column below is cast to an enum, a date or an array, and
             * none of those casts accepts an empty string: '' reaches
             * `InterviewEnum::from('')` and throws. `?? ''` looked like a
             * harmless default and was why a project could not be registered
             * at all whenever the interview-count radios were left alone.
             */
            "deadline" => self::blankToNull($validated["deadline"] ?? null),
            "languages" => $validated["languages"] == 3 ? [1, 2] : [$validated["languages"]],
            /*
             * These two lists keep any value the form had no control for.
             *
             * AffiliationEnum has cases 1-4; 5 and 6 are commented out, and the
             * column holds them anyway. The form therefore draws four
             * checkboxes for a project storing [1, 5, 6] — so an edit that only
             * changed the price would post [1] and silently delete the other
             * two. An unticked box is a decision; a box that was never on the
             * page is not.
             */
            'work_location_prefer' => self::keepUnrepresentable(
                $validated["workLocations"] ?? null,
                $project?->work_location_prefer,
                array_column(WorkLocationEnum::cases(), 'value'),
            ),
            "affiliation" => self::keepUnrepresentable(
                $validated["eligibility"] ?? null,
                $project?->affiliation,
                array_column(AffiliationEnum::cases(), 'value'),
            ),
            "number_of_application" => self::blankToNull($validated["number_of_application"] ?? null),
            "number_of_interviewers" => self::blankToNull($validated["number_of_interviewers"] ?? null),
        ];
    }

    /**
     * An unanswered optional field, as the database should record it.
     *
     * "Not answered" is null. It is never '', because the columns these values
     * land in are cast — an enum, a date, a JSON list — and none of those casts
     * can read an empty string. Kept as one named helper rather than a `?:` at
     * each call site so the next optional field added to this form inherits the
     * rule instead of rediscovering it through a 500.
     */
    /**
     * A list column's new value, keeping whatever the form could not offer.
     *
     * The form renders one control per enum case, so a stored value whose case
     * has been removed has no checkbox — the recruiter could not have unticked
     * it, and reading its absence as "delete this" turns every unrelated edit
     * into silent data loss. Submitted values win; invisible ones are carried
     * through.
     *
     * @param  array<int, mixed>|null  $submitted  what the form posted
     * @param  array<int, mixed>|null  $stored     what the row already held
     * @param  array<int, int>         $offered    the values the form can show
     * @return array<int, int>|null
     */
    private static function keepUnrepresentable(?array $submitted, ?array $stored, array $offered): ?array
    {
        $submitted = array_map('intval', $submitted ?? []);
        $invisible = array_diff(array_map('intval', (array) ($stored ?? [])), $offered);

        $merged = array_values(array_unique([...$submitted, ...$invisible]));

        return $merged === [] ? null : $merged;
    }

    private static function blankToNull(mixed $value): mixed
    {
        return filled($value) ? $value : null;
    }

    /**
     * A URL key for this project that is never empty and never a duplicate.
     *
     * `slug` is Project::getRouteKeyName(), so it is what every project URL is
     * built from — and `Str::slug()` drops every character it cannot romanise.
     * A title written in Japanese, which on this site is every real title,
     * slugged to an empty string. Two of those collide, `route('project.show')`
     * throws UrlGenerationException for a missing parameter, and the project
     * list dies with a 500 while drawing a link.
     *
     * That was unreachable until now only because no project could be
     * registered at all; it surfaces the moment registration works.
     *
     * So: romanise where that means something, fall back to the id where it
     * does not, and add a counter for the case two different Latin titles
     * romanise to the same thing ("Web System" and "Web-System" both give
     * web-system, and `unique:projects` guards the title, not the slug).
     *
     * A reserved placeholder is stored first because the id does not exist
     * until the row does; {@see backfillSlug()} replaces it straight after.
     */
    private static function slugFor(string $title): string
    {
        $base = Str::slug($title, '-');

        if ($base === '') {
            return Str::random(16);   // replaced with the id once it is known
        }

        $slug = $base;

        for ($suffix = 2; Project::where('slug', $slug)->exists(); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }

    /**
     * Swap the placeholder slug for the project's id.
     *
     * Only for titles that romanise to nothing. The id is the shortest stable
     * key available and cannot collide; a Latin title keeps its readable slug.
     */
    private static function backfillSlug(Project $project): void
    {
        if (Str::slug($project->title, '-') === '') {
            $project->forceFill(['slug' => (string) $project->id])->save();
        }
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
        $this->authorize('update', $project);

        // The form partial draws these two lists; without them the edit screen
        // renders the category and feature panels empty.
        $categories = Category::selectable()->get();
        $features = Feature::all();

        // Eager-loaded because the partial reads each one to decide what is
        // ticked, and doing it lazily is three queries per render.
        $project->load(['locations:id', 'subCategories:id', 'features:id']);

        return view('projects.edit', compact('project', 'categories', 'features'));
    }

    /**
     * Update the specified resource in storage.
     *
     * The method was an empty body, so the Edit action opened a page that could
     * not save — and the page it opened had no form on it either. Both halves
     * are here now: the fields come from the same partial the create screen
     * uses, and the values go back through the same rules and the same mapping.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate($this->rules($project));

        /*
         * The slug is deliberately left alone.
         *
         * It is this project's route key, so rewriting it on every rename would
         * break links already sent out — interview invitations carry project
         * URLs, and a recruiter's bookmarks would 404 for a wording change.
         * `company_id` and `user_id` are left alone for the same reason they are
         * not on the form: editing a project does not hand it to someone else.
         */
        $project->update($this->attributesFrom($validated, $project));

        /*
         * sync(), not attach(): this is an edit, so unticking has to remove the
         * link. attach() only ever adds, which would leave a category the
         * recruiter had just cleared still attached — and attaching an id that
         * is already there would duplicate the pivot row.
         */
        $project->subCategories()->sync($request->input('category_id', []));
        $project->features()->sync($request->input('project_features', []));
        $project->locations()->sync($request->input('locations', []));

        return redirect()
            ->route('project.show', $project)
            ->with('success', __('projects/index.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        /*
         * The route sits behind `role:user,admin`, which says the caller is an
         * employer — not that this project is theirs. Without this line
         * `DELETE /project/{slug}` let any employer delete any company's
         * project, and ProjectPolicy::delete() was never reached at all.
         *
         * Added alongside the guard on edit(): that one protects a page you can
         * only look at, this one protects the destructive action next to it.
         */
        $this->authorize('delete', $project);

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
