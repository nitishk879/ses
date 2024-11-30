<?php

namespace App\Http\Controllers;

use App\Models\Talent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TalentRegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('talentRegistration');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
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
            $fileNameToStore = "{$request->firstname}-{$request->lastname}.{$fileExt}";
            $request->file('resume')->storeAs('public/talents/', $fileNameToStore);
        } else {
            $fileNameToStore = 'default-image.jpg';
        }

        $user = User::updateOrCreate([
            'email' => $request->email,
        ], [
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'password' => 'password',
            'username' => strstr($request->email, '@', true),
            'date_of_birth' => $request->date_of_birth ?? today()->subYears(18),
            'gender' => $request->gender,
            'nationality' => $request->nationality,
            'nearest_station_prefecture' => $request->nearest_station_prefecture ?? '',
            'nearest_station_line' => $request->nearest_station_line ?? '',
            'nearest_station_name' => $request->nearest_station_name ?? '',
            'languages' => [$request->language],
            'address' => $request->address ?? ''
        ]);

        $user->roles()->attach(3);

        $talent = $user->talent()->create([
                'affiliation' => $request->affiliation,
                'availability' => $request->participation,
                'quasi_delegation_possible' => $request->contract_type == 'quasi_delegation_possible',
                'available_for_contract' => $request->contract_type == 'available_for_contract',
                'available_for_dispatch' => $request->contract_type == 'available_for_dispatch',
                'resume' => $fileNameToStore,
                'cover_letter' => $request->cover_letter,
                'address' => $request->address,
                'qualifications' => $request->education,
                'experience_pr' => $request->experience,
                'subcategory' => $request->subcategory,
                'min_monthly_price' => $request->min_monthly_price,
                'max_monthly_price' => $request->max_monthly_price,
                'work_location_prefer' => $request->workLocations ?? '', //[1, 2] / [1,3]
                'other_desire_conditions' => $request->other_desired_location ?? '',
                'privacy' => $request->privacy,
                'participation' => $request->participation,
                'joining_date' => $request->joining_date ?? null,
                'characteristics' => $request->characteristics ?? [],
                'company_id' => null
            ]);

        $talent->subcategories()->attach($request->subcategory);
        $talent->locations()->attach($request->input(['locations']));

        return redirect()->route('home');
    }

    /**
     * Display the specified resource.
     */
    public function show(Talent $talent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Talent $talent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Talent $talent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Talent $talent)
    {
        //
    }
}
