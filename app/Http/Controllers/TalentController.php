<?php

namespace App\Http\Controllers;

use App\Enums\WorkLocationEnum;
use App\Models\Talent;
use App\Models\User;
use Illuminate\Http\Request;

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
//            'experience' => 'required|min:3',
            'work_experience' => 'required',
            'workLocations' => 'array',
//            'work_location.*' => 'integer|in:' . implode(',', array_keys(WorkLocation::options())),
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

        if ($request->hasFile('resume')){
            $fileExt        = $request->file('resume')->getClientOriginalExtension();
            $fileNameToStore = "{$validated['firstname']}-{$validated['lastname']}.{$fileExt}";
            $request->file('resume')->storeAs('public/talents/', $fileNameToStore);
        }else{
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
            'languages' => $validated['language'] == 3 ? [1,2] : [$validated['language']],
        ]);


        $talent = $user->talent()->create([
            'affiliation' => $validated['affiliation'],
            'availability' => $validated['participation'],
            'quasi_delegation_possible' => $validated['contract_type'] == 'quasi_delegation_possible',
            'available_for_contract' => $validated['contract_type'] == 'available_for_contract',
            'available_for_dispatch' => $validated['contract_type'] == 'available_for_dispatch',
            'resume' => $fileNameToStore,
            'cover_letter' => $validated['cover_letter'],
            'address'   => $validated['address'],
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
            'joining_date'  => $validated['joining_date'] ?? null,
            'characteristics' => $validated['characteristics'] ?? [],
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
        if ($request->hasFile('resume')){
            $fileExt        = $request->file('resume')->getClientOriginalExtension();
            $fileNameToStore = "{$talent->user->firstname}-{$talent->user->lastname}.{$fileExt}";
            $request->file('resume')->storeAs('public/talents/', $fileNameToStore);
        }else{
            $fileNameToStore = 'default-image.jpg';
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
//            'address'   => $request['address'] ?? '',
            'nationality' => $talent->user->nationality,
            'nearest_station_prefecture' => $talent->user->nearest_station_prefecture ?? '',
            'nearest_station_line' => $talent->user->nearest_station_line ?? '',
            'nearest_station_name' => $talent->user->nearest_station_name ?? '',
            'languages' => $talent->user->language == 3 ? [1,2] : [$talent->user->language],
        ]);


        $talent = $user->talent()->create([
            'affiliation' => $request['affiliation'],
            'availability' => $request['participation'],
            'quasi_delegation_possible' => $request['contract_type'] == 'quasi_delegation_possible',
            'available_for_contract' => $request['contract_type'] == 'available_for_contract',
            'available_for_dispatch' => $request['contract_type'] == 'available_for_dispatch',
            'resume' => $fileNameToStore,
            'cover_letter' => $request['cover_letter'],
            'address'   => $request['address'] ?? '',
            'qualifications' => $request['education'],
            'experience_pr' => $request['experience'],
            'subcategory' => $request['subcategory'],
            'min_monthly_price' => $request['min_monthly_price'],
            'max_monthly_price' => $request['max_monthly_price'],
            'work_location_prefer' => $request['workLocations'] ?? '', //[1, 2] / [1,3]
            'other_desire_conditions' => $request['other_desired_location'] ?? '',
            'privacy' => $request['privacy'],
            'participation' => $request['participation'],
            'joining_date'  => $request['joining_date'] ?? null,
            'characteristics' => $request['characteristics'] ?? [],
        ]);

        if ($request->input('subcategories')){
            $talent->subcategories()->sync($request['subcategory']);
        }
        if ($request->input('locations')){
            $talent->locations()->attach($request->input(['locations']));
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
