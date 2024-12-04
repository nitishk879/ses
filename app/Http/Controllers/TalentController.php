<?php

namespace App\Http\Controllers;

use App\Enums\LangEnum;
use App\Enums\WorkLocationEnum;
use App\Models\Talent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TalentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $talents = Talent::all();

        return view('talents.index', compact('talents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('talents.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'email' => 'required|email|unique:users|max:255',
            'affiliation' => 'required|int',
            'contract_type' => 'required|max:255',
            'nationality' => 'required|max:255',
            'gender' => 'required|max:255',
            'date_of_birth' => 'required',
            'language' => 'required',
            'address' => 'required|max:255',
            'cover_letter' => 'required|min:64',
            'resume' => 'required|file|mimes:doc,docs,pdf|max:2048',
            'education' => 'required|min:3',
            'experience' => 'required|min:3',
            'work_experience' => 'required',
            'workLocations' => 'array',
            'work_location.*' => 'integer|in:' . implode(',', array_keys(WorkLocationEnum::cases())),
            'subcategory' => 'required|array',
            'min_monthly_price' => 'required',
            'max_monthly_price' => 'required',
            'nearest_station_prefecture' => 'nullable',
            'nearest_station_line' => 'nullable',
            'nearest_station_name' => 'nullable',
            'privacy' => 'required',
            'participation' => 'required',
            'joining_date' => 'required_if:participation,FUTURE|required_if:participation,FROM_DATE',
            'characteristics' => 'nullable|array'
        ]);

        if ($request->hasFile('resume')) {
            $fileExt = $request->file('resume')->getClientOriginalExtension();
            $fileNameToStore = "{$validated['firstname']}-{$validated['lastname']}.{$fileExt}";
            $request->file('resume')->storeAs('public/talents/', $fileNameToStore);
        } else {
            $fileNameToStore = 'default-image.jpg';
        }

        $user = User::updateOrCreate([
            'email' => $validated['email'],
        ], [
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'password' => 'password',
            'username' => strstr($validated['email'], '@', true),
            'date_of_birth' => $validated['date_of_birth'] ?? today()->subYears(18),
            'gender' => $validated['gender'],
            'nationality' => $validated['nationality'],
            'nearest_station_prefecture' => $validated['nearest_station_prefecture'] ?? '',
            'nearest_station_line' => $validated['nearest_station_line'] ?? '',
            'nearest_station_name' => $validated['nearest_station_name'] ?? '',
            'languages' => [$validated['language']],
            'address' => $validated['address'] ?? ''
        ]);

        $user->roles()->attach(3);

        $talent = $user->talent()->updateOrCreate([
            'user_id' => $user->id,
        ],
            [
                'affiliation' => $validated['affiliation'],
                'availability' => $validated['participation'],
                'quasi_delegation_possible' => $validated['contract_type'] == 'quasi_delegation_possible',
                'available_for_contract' => $validated['contract_type'] == 'available_for_contract',
                'available_for_dispatch' => $validated['contract_type'] == 'available_for_dispatch',
                'resume' => $fileNameToStore,
                'cover_letter' => $validated['cover_letter'],
                'address' => $validated['address'],
                'qualifications' => $validated['education'],
                'experience_pr' => $validated['experience'],
                'subcategory' => $validated['subcategory'],
                'min_monthly_price' => $validated['min_monthly_price'],
                'max_monthly_price' => $validated['max_monthly_price'],
                'work_location_prefer' => $validated['workLocations'] ?? '', //[1, 2] / [1,3]
                'other_desire_conditions' => $validated['other_desired_location'] ?? '',
                'privacy' => $validated['privacy'],
                'participation' => $validated['participation'],
//            'experience' => $validated['work_experience'] ?? '',
                'joining_date' => $validated['joining_date'] ?? null,
                'characteristics' => $validated['characteristics'] ?? [],
                'company_id' => Auth::user()->company->id
            ]);

        $talent->subcategories()->attach($validated['subcategory']);
        $talent->locations()->attach($request->input(['locations']));

        return redirect()->route('talents.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('talents.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Talent $talent)
    {
        return view('talents.edit', compact('talent'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Talent $talent)
    {
        $request->validate([
            'languages' => 'required|array',
            'languages.*' => 'integer|in:' . implode(',', array_keys(LangEnum::cases())),
        ]);

        if ($request->hasFile('resume')) {
            $fileExt = $request->file('resume')->getClientOriginalExtension();
            $fileNameToStore = "{$talent->user->firstname}-{$talent->user->lastname}.{$fileExt}";
            $request->file('resume')->storeAs('public/talents/', $fileNameToStore);
        } else {
            $fileNameToStore = $talent->resume ?? 'default-image.jpg';
        }

        $user = User::updateOrCreate([
            'email' => $talent->user->email,
        ], [
            'firstname' => $talent->user->firstname,
            'lastname' => $talent->user->lastname,
            'password' => 'password',
            'username' => strstr($talent->user->email, '@', true),
            'date_of_birth' => $talent->user->date_of_birth ?? today()->subYears(18),
            'gender' => $talent->user->gender,
            'address' => $request['address'] ?? '',
            'nationality' => $talent->user->nationality,
            'nearest_station_prefecture' => $talent->user->nearest_station_prefecture ?? '',
            'nearest_station_line' => $talent->user->nearest_station_line ?? '',
            'nearest_station_name' => $talent->user->nearest_station_name ?? '',
            'languages' => [$request->language] ?? $talent->user->languages,
        ]);

        $user->roles()->sync([3]);

        $talent = $user->talent->updateOrCreate([
            'user_id' => $user->id,
            'company_id' => Auth::user()->company->id
        ],
            [
                'affiliation' => $request['affiliation'] ?? $talent->affiliation,
                'availability' => $request['participation'] ?? $talent->availability,
                'quasi_delegation_possible' => $request['contract_type'] == 'quasi_delegation_possible',
                'available_for_contract' => $request['contract_type'] == 'available_for_contract',
                'available_for_dispatch' => $request['contract_type'] == 'available_for_dispatch',
                'resume' => $fileNameToStore,
                'cover_letter' => $request['cover_letter'] ?? $talent->cover_letter,
                'qualifications' => $request['education'] ?? $talent->qualifications,
                'experience_pr' => $request['experience'] ?? $talent->experience_pr,
                'subcategory' => $request['subcategory'] ?? $talent->subcategory,
                'min_monthly_price' => $request['min_monthly_price'] ?? $talent->min_monthly_price,
                'max_monthly_price' => $request['max_monthly_price'] ?? $talent->max_monthly_price,
                'work_location_prefer' => $request['workLocations'] ?? $talent->work_location_prefer, //[1, 2] / [1,3]
                'other_desire_conditions' => $request['other_desired_location'] ?? $talent->other_desire_conditions,
                'privacy' => $request['privacy'] ?? $talent->privacy,
                'participation' => $request['participation'] ?? $talent->participation,
                'joining_date' => $request['joining_date'] ?? $talent->joining_date,
                'characteristics' => $request['characteristics'] ?? $talent->characteristics,
                'company_id' => $request['company_id'] ?? Auth::user()->company->id
            ]);

        if (!empty($request->input('subcategories'))) {
            $talent->subcategories()->sync($request['subcategory']);
        }
        if (!empty($request->input('locations'))) {
            $talent->locations()->sync($request->input(['locations']));
        }

        return redirect()->route('talents.index')->with('success', __("talents/registration.talent_updated"));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Talent $talent)
    {
        $talent->delete();
        return redirect()->route('job-applicants')->with('success', __('talents/registration.talent_deleted'));
    }
}
