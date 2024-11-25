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
}
