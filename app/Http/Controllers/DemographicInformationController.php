<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\DemographicInformation;

class DemographicInformationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mrn' => 'nullable|string|max:255',
            'date_birth' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:255',
            'nric' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|integer',
            'patient_id' => 'required|exists:patients,id',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                DemographicInformation::create($validated);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data successfully stored.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DemographicInformation $demographicInformation)
    {
        $validated = $request->validate([
            'mrn' => 'required|string',
            'date_birth' => 'required|date|before:today',
            'gender' => 'in:male,female',
            'nric' => 'required|string',
            'address' => 'required|string|max:1000',
            'country' => 'required|string',
            'postal_code' => 'required|integer'
        ]);

        DB::beginTransaction();
        try {
            $demographicInformation->update([
                'mrn' => $validated['mrn'],
                'date_birth' => $validated['date_birth'],
                'gender' => $validated['gender'],
                'nric' => $validated['nric'],
                'address' => $validated['address'],
                'country' => $validated['country'],
                'postal_code' => $validated['postal_code']
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Success to update data.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Fail to update the data.'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
