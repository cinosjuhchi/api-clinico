<?php

namespace App\Http\Controllers;

use App\Http\Resources\FamilyRelationshipResource;
use App\Models\FamilyRelationship;
use Illuminate\Routing\Controller;
use App\Http\Requests\StoreFamilyRelationshipRequest;
use App\Http\Requests\UpdateFamilyRelationshipRequest;

class FamilyRelationshipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $relationship = FamilyRelationship::all();
        return response()->json([
            'data' => FamilyRelationshipResource::collection($relationship),
        ]);
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
    public function store(StoreFamilyRelationshipRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(FamilyRelationship $familyRelationship)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FamilyRelationship $familyRelationship)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFamilyRelationshipRequest $request, FamilyRelationship $familyRelationship)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FamilyRelationship $familyRelationship)
    {
        //
    }
}
