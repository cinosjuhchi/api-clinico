<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentRequest;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        $appointments->load(['patient:id,name', 'doctor', 'clinic.schedule', 'bill', 'medicalRecord.clinicService', 'medicalRecord.serviceRecord', 'medicalRecord.investigationRecord', 'medicalRecord.medicationRecords', 'medicalRecord.procedureRecords', 'medicalRecord.injectionRecords']);

        if ($appointments->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No appointments found',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Appointments retrieved successfully',
            'data' => $appointments,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AppointmentRequest $appointmentRequest)
    {
        $validated = $appointmentRequest->validated();

        // Ensure Clinic, Patient, and Doctor exist before proceeding
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

        // Check if slug already exists in the database
        $slugBase = $slug;
        $counter = 1;

        while (Appointment::where('slug', $slug)->exists()) {
            // If there's a duplicate slug, append a number
            $slug = $slugBase . '-' . $counter;
            $counter++;
        }

        // Generate the next visit number (VN)
        $currentDate = now();
        $currentYear = $currentDate->year;

        // Check the last VN and increment or reset
        $lastAppointment = Appointment::whereYear('created_at', $currentYear)
            ->orderBy('visit_number', 'desc')
            ->first();

        if ($lastAppointment) {
            $lastVN = $lastAppointment->visit_number;
            $lastVNNumber = (int) substr($lastVN, 2); // Strip "VN" prefix and convert to number

            if ($lastVNNumber >= 999999) {
                $newVN = 'VN000001';
            } else {
                $newVN = 'VN' . str_pad($lastVNNumber + 1, 6, '0', STR_PAD_LEFT);
            }
        } else {
            $newVN = 'VN000001';
        }

        try {
            // Use transaction to maintain data consistency
            DB::transaction(function () use ($validated, $title, $slug, $clinic, $newVN) {
                Appointment::create([
                    'title' => $title,
                    'slug' => $slug,
                    'clinic_service_id' => $validated['visit_purpose'],
                    'current_condition' => $validated['current_condition'],
                    'status' => 'pending',
                    'room_id' => $validated['room_id'],
                    'patient_id' => $validated['patient_id'],
                    'doctor_id' => $validated['doctor_id'],
                    'clinic_id' => $clinic->id,
                    'appointment_date' => $validated['appointment_date'],
                    'visit_number' => $newVN, // Assign the generated visit number
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

    public function takeMedicine(Appointment $appointment)
    {
        if ($appointment->status == 'consultation' || $appointment->status == 'cancelled' || $appointment->status == 'completed') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Appointment has been check-in!',
            ], 403);
        }
        $appointment->status = 'waiting-payment';
        $appointment->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Appointment in-progress successfully',
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        $appointment = Appointment::with(['doctor.category', 'clinic', 'patient', 'service'])->where('slug', $slug)->first();
        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Appointment retrieved successfully',
            'data' => $appointment,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function checkin(Appointment $appointment)
    {
        if ($appointment->status == 'consultation' || $appointment->status == 'cancelled' || $appointment->status == 'completed') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Appointment has been check-in!',
            ], 403);
        }

        // Cek appointment dengan status consultation terlebih dahulu
        $bookedConsultation = Appointment::where('appointment_date', $appointment->appointment_date)
            ->where('status', 'consultation')
            ->where('doctor_id', $appointment->doctor_id)
            ->where('room_id', $appointment->room_id)
            ->latest('updated_at')
            ->first();

        // Jika tidak ada status consultation, cek status on-consultation
        if (!$bookedConsultation) {
            $bookedOnConsultation = Appointment::where('appointment_date', $appointment->appointment_date)
                ->where('status', 'on-consultation')
                ->where('doctor_id', $appointment->doctor_id)
                ->where('room_id', $appointment->room_id)
                ->latest('updated_at')
                ->first();
        }

        // Tentukan waiting number berdasarkan hasil pengecekan
        $waitingNumber = 1;
        if ($bookedConsultation) {
            $waitingNumber = $bookedConsultation->waiting_number + 1;
        } elseif (isset($bookedOnConsultation) && $bookedOnConsultation) {
            $waitingNumber = $bookedOnConsultation->waiting_number + 1;
        }

        // Update appointment
        $appointment->update([
            'status' => 'consultation',
            'waiting_number' => $waitingNumber,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Check-In successfully!',
            'data' => $waitingNumber,
        ], 200);
    }

    public function waitingNumber(Appointment $appointment)
    {
        $roomWaitingNumber = Appointment::where('appointment_date', $appointment->appointment_date)
            ->where('status', 'consultation')
            ->where('doctor_id', $appointment->doctor_id)
            ->where('room_id', $appointment->room_id)
            ->oldest('updated_at')->first();

        return response()->json($roomWaitingNumber);

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
