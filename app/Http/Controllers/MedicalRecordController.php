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

        $medicalRecord->load(['patient', 'doctor', 'clinic', 'serviceRecord', 'investigationRecord', 'diagnosisRecord', 'procedureRecords', 'medicationRecords', 'injectionRecords', 'consultationPhotos', 'consultationDocuments', 'gestationalAge', 'allergies']); // Periksa konsistensi nama relasi

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetched medical data.',
            'data' => $medicalRecord,
        ]);
    }

    public function history(Request $request)
    {
        $medicalRecordQuery = MedicalRecord::with(
            'appointment',
            'patient.physicalExaminations',
            'clinic',
            'clinicService',
            'doctor.category',
            'diagnosisRecord',
            'procedureRecords',
            'injectionRecords',
            'medicationRecords',
            'riskFactors',
            'gestationalAge',
            'allergies'
        );

        $patientId = $request->input('patient_id');
        $doctorId = $request->input('doctor_id');
        $clinicId = $request->input('clinic_id');

        if ($patientId) {
            $medicalRecordQuery->where('patient_id', $patientId);
        }

        if ($doctorId) {
            $medicalRecordQuery->where('doctor_id', $doctorId);
        }

        if ($clinicId) {
            $medicalRecordQuery->where('clinic_id', $clinicId);
        }

        $medicalRecord = $medicalRecordQuery
                            ->orderBy('created_at', 'desc')
                            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetched medical data.',
            'data' => $medicalRecord,
        ]);
    }
}
