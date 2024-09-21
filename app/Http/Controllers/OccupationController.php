<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OccupationRecord;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class OccupationController extends Controller
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
            'job_position' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'panel' => 'required|string|max:255',
            'patient_id' => 'required|exists:patients,id',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                OccupationRecord::create($validated);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully stored occupation record.',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store occupation record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(OccupationRecord $occupationRecord)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OccupationRecord $occupationRecord)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OccupationRecord $occupationRecord)
    {
        //
    }
}
