<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'family_id' => 'required|exists:families,id',
            'family_relationships_id' => 'required|exists:family_relationships,id',
        ]);

        try {
            // Declare the variable outside of the transaction closure
            $patient = null;

            DB::transaction(function () use ($validated, &$patient) {
                // Create the patient within the transaction
                $patient = Patient::create($validated);
            });

            // Return success response after transaction
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully stored patient data.',
                'data' => $patient,
            ], 201);

        } catch (\Exception $e) {
            // Handle exception and return error response
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
