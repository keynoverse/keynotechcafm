<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $buildings = Building::latest()->get();
        return view('buildings.index', compact('buildings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('buildings.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:buildings',
            'name' => 'required',
            'address' => 'required',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        Building::create($validated);

        return redirect()->route('buildings.index')
            ->with('success', 'Building created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Building $building)
    {
        return view('buildings.show', compact('building'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Building $building)
    {
        return view('buildings.edit', compact('building'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Building $building)
    {
        $validated = $request->validate([
            'code' => 'required|unique:buildings,code,' . $building->id,
            'name' => 'required',
            'address' => 'required',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        $building->update($validated);

        return redirect()->route('buildings.index')
            ->with('success', 'Building updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Building $building)
    {
        $building->delete();

        return redirect()->route('buildings.index')
            ->with('success', 'Building deleted successfully.');
    }
}
