<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ImmunizationRecord;
use App\Models\Patient;
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
    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'vaccines' => 'required|array',
            'vaccines.*.vaccine_received' => 'required|string|max:125',
            'vaccines.*.date_administered' => 'required|date',            
        ]);

        DB::beginTransaction();
        try {
            $patient->immunizations()->delete();
            foreach ($validated as $item) {
                $patient->immunizations()->create([
                    'vaccine_received' => $item['vaccine_received'],
                    'date_administered' => $item['date_administered']
                ]);
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Success to update data.'
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Fail to update the data.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ImmunizationRecord $immunizationRecord)
    {
        //
    }
}
