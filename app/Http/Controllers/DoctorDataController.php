<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class DoctorDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function consultationEntry(Request $request)
    {
        $user = Auth::user();
        $doctor = $user->doctor;

        if (!$doctor) {
            return response()->json([
                'status' => 'failed',
                'message' => 'User not found',
            ], 400);
        }

        $query = $request->input('q');

        // Mendapatkan antrian utama berdasarkan prioritas on-consultation
        $currentAppointment = $doctor->consultationAppointments()
            ->whereIn('status', ['consultation', 'on-consultation'])
            ->orderBy('waiting_number', 'asc') // Pastikan urut terkecil
            ->first();

        $currentWaitingNumber = $currentAppointment ? $currentAppointment->waiting_number : null;

        // Ambil daftar appointment dan urutkan dari waiting_number terkecil
        $appointments = $doctor->consultationAppointments()
            ->with(['patient', 'patient.demographics', 'doctor.category', 'clinic', 'service', 'medicalRecord'])
            ->whereIn('status', ['consultation', 'on-consultation'])
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQuery) use ($query) {
                    $subQuery->where('waiting_number', 'like', "%{$query}%")
                        ->orWhereHas('patient.demographics', function ($categoryQuery) use ($query) {
                            $categoryQuery->where('name', 'like', "%{$query}%");
                        });
                });
            })
            ->orderBy('waiting_number', 'asc') // Pastikan selalu urut terkecil
            ->paginate(15);

        // Menambahkan prediksi waktu tunggu
        $appointments->getCollection()->transform(function ($appointment) use ($currentWaitingNumber) {
            $waitingTime = 0;

            if ($currentWaitingNumber !== null) {
                $waitingTime = max(0, ($appointment->waiting_number - $currentWaitingNumber) * 20);
            }

            $appointment->waiting_time_prediction = $waitingTime . ' minutes';
            return $appointment;
        });

        return response()->json($appointments);
    }


    public function pendingEntry(Request $request)
    {
        $user = Auth::user();
        $doctor = $user->doctor;
        if (!$doctor) {
            return response()->json([
                'status' => 'failed',
                'message' => 'user not found',
            ]);
        }
        $query = $request->input('q');

        $appointments = $doctor->pendingAppointments()->with(['patient.demographics', 'doctor.category', 'clinic', 'service'])->when($query, function ($q) use ($query) {
            $q->where(function ($subQuery) use ($query) {
                $subQuery->where('waiting_number', 'like', "%{$query}%")
                    ->orWhereHas('patient.demographics', function ($categoryQuery) use ($query) {
                        $categoryQuery->where('name', 'like', "%{$query}%");
                    });
            });
        })->orderBy('waiting_number')->paginate(5);

        return response()->json($appointments);
    }
    public function completedEntry(Request $request)
    {
        $user = Auth::user();
        $doctor = $user->doctor;
        if (!$doctor) {
            return response()->json([
                'status' => 'failed',
                'message' => 'user not found',
            ]);
        }
        $query = $request->input('q');

        $appointments = $doctor->completedAppointments()->with(['patient', 'doctor.category', 'clinic', 'service'])->when($query, function ($q) use ($query) {
            $q->where(function ($subQuery) use ($query) {
                $subQuery->where('waiting_number', 'like', "%{$query}%")
                    ->orWhereHas('patient.demographics', function ($categoryQuery) use ($query) {
                        $categoryQuery->where('name', 'like', "%{$query}%");
                    });
            });
        })->latest()->paginate(5);

        return response()->json($appointments);
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
    public function show(Doctor $doctor)
    {
        //
    }

    public function showConsultation(string $slug)
    {
        $appointment = Appointment::with(
            [
                'doctor.category',
                'doctor.employmentInformation',
                'clinic',
                'patient.allergy',
                'patient.user',
                'patient.physicalExaminations',
                'patient.demographics',
                'patient.medicalRecords',
                'patient.occupation',
                'patient.chronics',
                'patient.medications',
                'patient.immunizations',
                'patient.occupation',
                'patient.emergencyContact.familyRelationship',
                'patient.parentChronic',
                'patient.familyRelationship',
                'service',
                'medicalRecord.medicationRecords',
                'medicalRecord.injectionRecords',
                'medicalRecord.procedureRecords',
                'medicalRecord.patient',
                'medicalRecord.doctor',
                'medicalRecord.clinic',
                'medicalRecord.clinicService',
                'medicalRecord.serviceRecord',
                'medicalRecord.investigationRecord',
                'medicalRecord.diagnosisRecord',
                'medicalRecord.gestationalAge',

            ]
        )->where('slug', $slug)->first();
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
    public function update(Request $request, Doctor $doctor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        //
    }
}
