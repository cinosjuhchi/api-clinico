<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ImmunizationRecord;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class ImmunizationController extends Controller
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
            'vaccine_received' => 'required|string|max:125',
            'date_administered' => 'required|date',
            'patient_id' => 'required|exists:patients,id',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                ImmunizationRecord::create($validated);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully stored immunization record.',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store immunization record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ImmunizationRecord $immunizationRecord)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ImmunizationRecord $immunizationRecord)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ImmunizationRecord $immunizationRecord)
    {
        //
    }
}
