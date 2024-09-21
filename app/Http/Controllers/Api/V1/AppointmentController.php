<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Helpers\SlugAppointmentHelper;
use App\Http\Requests\AppointmentRequest;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $patientId = $request->input('patient_id');

        // Pastikan pasien yang dipilih milik user yang sedang login
        $patient = $user->patients()->find($patientId);        

        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found or does not belong to the user',
            ], 404);
        }

        // Mendapatkan appointments untuk pasien yang dipilih
        $appointments = Appointment::where('patient_id', $patient->id)->get();
        $appointments->load(['patient:id,name', 'doctor:id,name', 'clinic:id,name']);
        if ($appointments->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No appointments found',
                'data' => []
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Appointments retrieved successfully',
            'data' => $appointments
        ], 200);
    }    

    /**
     * Store a newly created resource in storage.
     */
    public function store(AppointmentRequest $appointmentRequest): JsonResponse
    {
        $validated = $appointmentRequest->validated();

        // Pastikan Clinic, Patient, dan Doctor ada sebelum melanjutkan                
        $patient = Patient::find($validated['patient_id']);
        $doctor = Doctor::find($validated['doctor_id']);
        $clinic = Clinic::find($doctor->clinic_id);

        if (!$clinic || !$patient || !$doctor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Clinic, Patient, or Doctor not found',
            ], 404);
        }
        
        $title = "{$validated['visit_purpose']} on {$clinic->name}";
        $slug = SlugAppointmentHelper::generateSlug($title);

        try {
            // Gunakan transaksi untuk menjaga konsistensi data
            DB::transaction(function () use ($validated, $title, $slug, $clinic) {
                Appointment::create([
                    'title' => $title,
                    'slug' => $slug,
                    'visit_purpose' => $validated['visit_purpose'],
                    'current_condition' => $validated['current_condition'],
                    'status' => 'pending',
                    'patient_id' => $validated['patient_id'],
                    'doctor_id' => $validated['doctor_id'],
                    'clinic_id' => $clinic->id,
                    'appointment_date' => $validated['appointment_date'],
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Appointment created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create appointment: ' . $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        $appointment = Appointment::where('slug', $slug)->first();
        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Appointment retrieved successfully',
            'data' => $appointment
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $slug)
    {
        $appointment = Appointment::where('slug', $slug)->first();
        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
            ], 404);
        }
        $appointment->status = 'cancelled';
        $appointment->save(); 
        return response()->json([
            'status' => 'success',
            'message' => 'Appointment cancelled successfully',
        ], 200);
    }
}
