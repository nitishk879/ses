<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->roles()->count() >=1)
        {
            return redirect('/')->with('errors', 'You do not have permission to access this page.');
        }

        return view('home');
    }

    public function profile()
    {

        return view('profile', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'address' => 'required',
            'country' => 'required',
            'nationality' => 'required',
            'date_of_birth' => 'required',
            'gender' => 'required',
            'language' => 'required',
        ]);

        $user = Auth::user();
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->phone  = $request->phone;
        $user->date_of_birth = $request->date_of_birth;
        $user->gender = $request->gender;
        $user->nationality = $request->nationality;
        $user->country = $request->country;
        $user->languages = $request->language == 3 ? [1, 2] : [$request->language];
        $user->address = $request->address;
        $user->save();


        return redirect('/profile')->with('success', __("users/form.success_message"));
    }
}
