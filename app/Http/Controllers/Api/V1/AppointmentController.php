<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        $status = $request->input('status'); // Ambil status dari request
        $date = $request->input('date'); // Ambil status dari request

        if ($patientId) {
            // Jika patient_id diberikan, pastikan pasien milik user yang sedang login
            $patient = $user->patients()->find($patientId);

            if (!$patient) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Patient not found or does not belong to the user',
                ], 404);
            }

            // Query appointments berdasarkan patient_id dan urutkan dari yang terbaru
            $appointmentsQuery = Appointment::where('patient_id', $patient->id)->orderBy('created_at', 'desc');
        } else {
            // Jika patient_id tidak diberikan, ambil semua pasien milik user
            $patientIds = $user->patients()->pluck('id')->toArray();

            // Query appointments berdasarkan semua pasien milik user dan urutkan dari yang terbaru
            $appointmentsQuery = Appointment::whereIn('patient_id', $patientIds)->orderBy('created_at', 'desc');
        }

        // Jika status diberikan, tambahkan filter status
        if ($status) {
            $appointmentsQuery->where('status', $status);
        }

        if ($date) {
            $appointmentsQuery->where('appointment_date', $date);
        }

        // Eksekusi query untuk mendapatkan appointments
        $appointments = $appointmentsQuery->get();

        // Muat relasi untuk appointments
        $appointments->load(['patient:id,name', 'doctor', 'clinic.schedule']);
        if ($appointments->status === 'waiting-payment') {
            $appointments->load(['bill', 'medicalRecord']);
        }

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
        $slug = Str::slug($title);

        // Cek apakah slug sudah ada di database
        $slugBase = $slug;
        $counter = 1;
    
        while (Appointment::where('slug', $slug)->exists()) {
            // Jika ada slug yang sama, tambahkan angka
            $slug = $slugBase . '-' . $counter;
            $counter++;
        }

        try {
            // Gunakan transaksi untuk menjaga konsistensi data
            DB::transaction(function () use ($validated, $title, $slug, $clinic) {
                Appointment::create([
                    'title' => $title,
                    'slug' => $slug,
                    'visit_purpose' => $validated['visit_purpose'],
                    'current_condition' => $validated['current_condition'],
                    'status' => 'pending',
                    'room_id' => $validated['room_id'],
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
        $appointment = Appointment::with(['doctor.category', 'clinic', 'patient'])->where('slug', $slug)->first();
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
    public function checkin(Appointment $appointment)
    {        
        if($appointment->status == 'consultation' || $appointment->status == 'cancelled' || $appointment->status == 'completed') 
        {
            return response()->json([
                'status' => 'failed',
                'message' => 'Appointment has been check-in!'                
            ], 403);
        }
        $booked = Appointment::where('appointment_date', $appointment->appointment_date)
        ->where('status', 'consultation')    
        ->where('doctor_id', $appointment->doctor_id)
        ->where('room_id', $appointment->room_id)
        ->latest('updated_at')->first();
        $waitingNumber = 1;
        if($booked) {
            $waitingNumber += $booked->waiting_number;
            $appointment->update([
                'status' => 'consultation',
                'waiting_number' => $waitingNumber,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Check-In successfully!',
                'data' => $booked
            ], 200);
        }
        $appointment->update([
            'status' => 'consultation',
            'waiting_number' => 1
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Check-In successfully!',
            'data' => $waitingNumber
        ], 200);
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
    public function callPatient(Appointment $appointment)
    {
        
    }    
}