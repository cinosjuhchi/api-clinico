<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddStatusAppointmentRequest;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddVitalSignRequest;
use App\Http\Requests\PatientStoreRequest;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        if (!$clinic) {
            return response()->json([
                'status' => 'error',
                'message' => 'Clinic not found.',
            ], 404);
        }

        $query = $request->input('q');

        $appointments = $clinic->appointments()->with(['patient.demographics', 'doctor.category', 'clinic', 'service', 'patient.user'])->when($query, function ($q) use ($query) {
            $q->where(function ($subQuery) use ($query) {
                $subQuery->where('waiting_number', 'like', "%{$query}%")
                    ->orWhereHas('patient.demographics', function ($categoryQuery) use ($query) {
                        $categoryQuery->where('name', 'like', "%{$query}%");
                    });
            });
        })->paginate(5);

        return response()->json($appointments);
    }


    public function addStatus(AddStatusAppointmentRequest $request, Appointment $appointment)
    {
        $validated = $request->validated();
        try{
            DB::beginTransaction();
            $appointment->update([
                'status_patient' => $validated['status_patient']
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'patient_status' => $appointment->status_patient
            ], 200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'error something wrong happened'
            ], 500);
        }
    }

    public function addVitalSign(AddVitalSignRequest $request, Patient $patient)
    {
        $validated = $request->validated();
        $appointment = Appointment::find($validated['appointment_id']);
        try {
            DB::beginTransaction();
            $medicalRecord = $appointment->medicalRecord()->create([
                'patient_id' => $appointment->patient_id,
                'clinic_id' => $appointment->clinic_id,
                'doctor_id' => $appointment->doctor_id,
                'patient_condition' => $appointment->current_condition,
                'blood_pressure' => $validated['blood_pressure'],
                'sp02' => $validated['sp02'],
                'temperature' => $validated['temperature'],
                'pulse_rate' => $validated['pulse_rate'],
                'pain_score' => $validated['pain_score'],
            ]);

            $patient->physicalExaminations()->update([
                'height' => $validated['height'],
                'weight' => $validated['weight'],
                'blood_pressure' => $validated['blood_pressure'],
                'sp02' => $validated['sp02'],
                'respiratory_rate' => $validated['respiratory_rate'],
                'temperature' => $validated['temperature'],
                'pulse_rate' => $validated['pulse_rate'],
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'medical_record' => $medicalRecord,
                'message' => 'Add vital sign successfully add',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function waitingPatient(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        if (!$clinic) {
            return response()->json([
                'status' => 'error',
                'message' => 'Clinic not found.',
            ], 404);
        }

        $query = $request->input('q');

        $appointments = $clinic->consultationAppointments()->with(['patient.demographics', 'doctor.category', 'clinic', 'service'])->when($query, function ($q) use ($query) {
            $q->where(function ($subQuery) use ($query) {
                $subQuery->where('waiting_number', 'like', "%{$query}%")
                    ->orWhereHas('patient.demographics', function ($categoryQuery) use ($query) {
                        $categoryQuery->where('name', 'like', "%{$query}%");
                    });
            });
        })->orderBy('waiting_number')->paginate(5);

        return response()->json($appointments);

    }
    public function bookingPatient(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        if (!$clinic) {
            return response()->json([
                'status' => 'error',
                'message' => 'Clinic not found.',
            ], 404);
        }

        $query = $request->input('q');

        $appointments = $clinic->pendingAppointments()->with(['patient.demographics', 'doctor.category', 'clinic', 'service'])->when($query, function ($q) use ($query) {
            $q->where(function ($subQuery) use ($query) {
                $subQuery->where('waiting_number', 'like', "%{$query}%")
                    ->orWhereHas('patient.demographics', function ($categoryQuery) use ($query) {
                        $categoryQuery->where('name', 'like', "%{$query}%");
                    });
            });
        })->get();

        return response()->json($appointments);

    }

    public function completedPatient(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        if (!$clinic) {
            return response()->json([
                'status' => 'error',
                'message' => 'Clinic not found.',
            ], 404);
        }
        $query = $request->input('q');

        $appointments = $clinic->completedAppointments()->with(['patient.demographics', 'doctor.category', 'clinic', 'service', 'bill'])->when($query, function ($q) use ($query) {
            $q->where(function ($subQuery) use ($query) {
                $subQuery->where('waiting_number', 'like', "%{$query}%")
                    ->orWhereHas('patient.demographics', function ($categoryQuery) use ($query) {
                        $categoryQuery->where('name', 'like', "%{$query}%");
                    });
            });
        })->orderBy('waiting_number')->paginate(5);

        return response()->json($appointments);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PatientStoreRequest $request)
    {
        try {
            $validated = $request->validated();
            if ($validated['payment_type'] === 'CASH') {
                $validated['user_id'] = null;
            }

            $patient = null;

            DB::transaction(function () use ($validated, &$patient) {
                $patient = Patient::create($validated);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully stored patient data.',
                'data' => $patient,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store patient data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        try {
            // Hapus pasien dari database
            $patient->delete();

            // Mengembalikan respons sukses
            return response()->json([
                'success' => true,
                'message' => 'Patient deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            // Menangani kesalahan yang mungkin terjadi saat penghapusan
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the patient: ' . $e->getMessage(),
            ], 500);
        }
    }

}
