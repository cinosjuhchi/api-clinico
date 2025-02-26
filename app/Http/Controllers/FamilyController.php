<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Helpers\PatientCreateHelper;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\FamilyResource;
use App\Http\Requests\StoreFamilyRequest;
use App\Http\Requests\PatientStoreRequest;
use App\Http\Requests\UpdateFamilyRequest;

class FamilyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $id = Auth::user()->id;
        $families = Family::with([
            'patients.familyRelationship',
            'patients.demographics',
            ])->where('user_id', $id)
            ->firstOrFail();
        return response()->json([
            'status' => 'success',
            'message' => 'List of your families',
            'data' => new FamilyResource($families),
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:125',
            'address' => 'required|string',
            'family_id' => 'required|exists:families,id',
            'family_relationships_id' => 'required|exists:family_relationships,id'
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();            
            $family = Family::find($validated['family_id']);
            $patient = $family->patients()->create([
                "name" => $validated["name"],
                "address" => $validated["address"],
                "family_relationship_id" => $validated['family_relationships_id'],
                "user_id"=> $user->id,
                "is_offline" => false,
            ]);            
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create patient record: ' . $th->getMessage()], 500);
        }

        return response()->json(['message' => 'Patient record created successfully.', 'data' => $patient], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Family $family)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Family $family)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFamilyRequest $request, Family $family)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Family $family)
    {
        //
    }
}
