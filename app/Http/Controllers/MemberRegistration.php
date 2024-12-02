<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MemberRegistration extends Controller
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
        return view('member.register');
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
            'gender' => 'required|max:255',
            'date_of_birth' => 'nullable',
            'phone_number' => 'required|max:13',

            'company_name' => 'required|unique:companies|max:255',
            'company_logo' => 'required|file|image|max:2048',
            'company_website' => 'nullable|url',
            'company_email' => 'nullable|email|max:255',
            'industry' => 'nullable|max:255',
            'telephone_number' => 'nullable|max:13',
            'company_address' => 'nullable|max:255',
            'expertise' => 'nullable|max:255',
            'specialised'   => 'nullable|max:255',
            'address'   => 'nullable|max:255',
            'address2'   => 'nullable|max:255',
            'established' => 'nullable|max:10',
            'number_of_employees' => 'nullable|int',
            'capital' => 'nullable|int',
            'dispatch_business' => 'nullable|bool',
            'dispatch_business_license' => 'required_if:dispatch_business,true|nullable',
            'company_information_disclose' => 'nullable|bool'

        ]);

        if ($request->hasFile('company_logo')) {
            $fileExt = $request->file('company_logo')->getClientOriginalExtension();
            $name = Str::slug($request->company_name);
            $fileNameToStore = "companies/{$name}.{$fileExt}";
            $request->file('company_logo')->storeAs('public/', $fileNameToStore);
        } else {
            $fileNameToStore = 'default-image.jpg';
        }

        $user = User::updateOrCreate([
            'email' => $request->email,
        ],
            [
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'password' => 'password',
            'username' => strstr($request->email, '@', true),
            'date_of_birth' => $request->date_of_birth ?? today()->subYears(18),
            'gender' => $request->gender,
            'nationality' => $request->nationality ?? 'Japanese',
            'nearest_station_prefecture' => $request->nearest_station_prefecture ?? '',
            'nearest_station_line' => $request->nearest_station_line ?? '',
            'nearest_station_name' => $request->nearest_station_name ?? '',
            'languages' => [$request->language ?? 1],
            'address' => $request->address ?? ''
        ]);

        $user->roles()->attach(2);

        $member = $user->company()->create([
            'company_name' => $request->company_name,
            'company_logo' => $fileNameToStore,
            'company_website' => $request->company_website,
            'company_email' => $request->company_email,
            'industry' => $request->industry,
            'telephone_number' => $request->telephone_number,
            'company_address' => $request->company_address,
            'expertise' => $request->expertise,
            'specialised' => $request->specialised,
            'established' => $request->established,
            'number_of_employees' => $request->number_of_employees,
            'capital' => $request->capital,
            'dispatch_business' => $request->dispatch_business,
            'dispatch_business_license' => $request->dispatch_business_license,
            'company_information_disclose' => $request->company_information_disclose ?? false,
        ]);

        return redirect(route('dashboard'))->with('success', "{$member->user->name} has Registered {$member->company_name} successful!");
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        //
    }
}
