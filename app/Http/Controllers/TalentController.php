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
//        dd($request->all());
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
            $fileNameWithExt = $request->file('resume')->getClientOriginalName();
            $fileName       = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
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
            'qualifications' => $validated['education'],
            'experience_pr' => $validated['experience'],
            'subcategory' => $validated['subcategory'],
            'min_monthly_price' => $validated['min_monthly_price'],
            'max_monthly_price' => $validated['max_monthly_price'],
            'work_location_prefer' => $validated['workLocations'] ?? '', //[1, 2] / [1,3]
            'other_desire_conditions' => $validated['other_desired_location'] ?? '',
            'privacy' => $validated['privacy'],
            'participation' => $validated['participation'],
            'experience' => $validated['work_experience'] ?? '',
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
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
