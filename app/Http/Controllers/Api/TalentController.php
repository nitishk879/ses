<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TalentResource;
use App\Models\Talent;
use Illuminate\Http\Request;

class TalentController extends Controller
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Talent $talent)
    {
        return response()->json($talent->with('user')->first());

//        return view('modals.TalentModal', compact('talent'));
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
