<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MedicationRecord;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class MedicationRecordController extends Controller
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
            'patient_id' => 'required|exists:patients,id',
            'medicine' => 'required|string|max:255',
            'frequency' => 'required|string|max:255',            
        ]);

        try {
            DB::transaction(function () use ($validated) {
                MedicationRecord::create($validated);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data successfully stored.',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
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
    public function show(MedicationRecord $medicationRecord)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MedicationRecord $medicationRecord)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicationRecord $medicationRecord)
    {
        //
    }
}
