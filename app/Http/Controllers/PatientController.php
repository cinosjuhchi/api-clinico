<?php

namespace App\Http\Controllers;

use App\Helpers\PatientCreateHelper;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddVitalSignRequest;
use App\Http\Requests\PatientStoreRequest;
use App\Http\Requests\AddStatusAppointmentRequest;

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

    public function queue(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        try {
            // Ambil tanggal dari request, default ke hari ini jika tidak disediakan
            $date = $request->input('date', now()->toDateString());

            // Query rooms dengan relasi appointments dan hanya mengambil satu appointment terakhir (waiting_number terkecil dengan status consultation)
            $rooms = $clinic->rooms()
                ->with([
                    'appointments' => function ($query) use ($date) {
                        $query->where('status', 'consultation')
                            ->whereDate('appointment_date', $date)
                            ->orderBy('waiting_number', 'asc') // Change to 'asc' for smallest waiting_number
                            ->limit(1);
                    }
                ])
                ->get()
                ->each(function ($room) {
                    $appointment = $room->appointments->where('status', 'consultation')->first();
                    $room->status = $appointment ? $appointment->patient->name : 'no status';
                    $room->save();
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully retrieved rooms with queue',
                'data' => [
                    'rooms' => $rooms,
                    'requested_date' => $date,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve rooms and queue: ' . $e->getMessage(),
            ], 500);
        }
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
        })->orderBy('waiting_number')->paginate(15);

        return response()->json($appointments);

    }

    public function search(Request $request) {
        $nric = $request->input('nric');

        if (!$nric) {
            return response()->json([
                'status' => 'error',
                'message' => 'NRIC is required for search.',
            ], 400);
        }

        $patient = Patient::whereHas('demographics', function ($query) use ($nric) {
            $query->where('nric', $nric);
        })->with(['demographics'])->first();

        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'patient' => $patient,
        ]);
    }

    public function store(PatientStoreRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $patient = Patient::create([
                "name" => $validated["name"],
                "address" => $validated["address"],
                "is_offline" => true,
            ]);
            PatientCreateHelper::createDemographics($patient, $validated);
            PatientCreateHelper::createContactInfo($patient, $validated);
            PatientCreateHelper::createOccupation($patient, $validated);
            PatientCreateHelper::createEmergencyContact($patient, $validated);
            PatientCreateHelper::createChronicHealthRecord($patient, $validated); // multiple
            PatientCreateHelper::createParentChronic($patient, $validated);
            PatientCreateHelper::createMedication($patient, $validated); // multiple
            PatientCreateHelper::createAllergy($patient, $validated);
            PatientCreateHelper::createPhysicalExamination($patient, $validated);
            PatientCreateHelper::createImmunization($patient, $validated); // multiple
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create patient record: ' . $th->getMessage()], 500);
        }

        return response()->json(['message' => 'Patient record created successfully.'], 201);
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
