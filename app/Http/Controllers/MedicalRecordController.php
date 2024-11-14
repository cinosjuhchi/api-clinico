<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class MedicalRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $medicalRecord = $user->medicalRecords()->with(['patient', 'doctor', 'clinic', 'serviceRecord', 'investigationRecord', 'diagnosisRecord'])->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Appointments retrieved successfully',
            'data' => $medicalRecord,
        ], 200);

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
    public function show(MedicalRecord $medicalRecord)
    {
        if (!$medicalRecord) {
            return response()->json([
                'status' => 'error',
                'message' => 'Medical record not found.',
            ], 404);
        }

        $medicalRecord->load(['patient', 'doctor', 'clinic', 'serviceRecord', 'investigationRecord', 'diagnosisRecord', 'procedureRecords', 'medicationRecords', 'injectionRecords']); // Periksa konsistensi nama relasi

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetched medical data.',
            'data' => $medicalRecord,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MedicalRecord $medicalRecord)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicalRecord $medicalRecord)
    {
        //
    }
}
