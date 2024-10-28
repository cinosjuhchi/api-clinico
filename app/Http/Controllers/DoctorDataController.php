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
        if(!$doctor)
        {
            return response()->json([
                'status' => 'failed',
                'message' => 'user not found'
            ]);
        }
        $appointments = $doctor->consultationAppointments()->with(['patient', 'doctor.category', 'clinic', 'service'])->orderBy('waiting_number')->paginate(5);

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
            'clinic', 
            'patient.allergy', 
            'patient.demographics',
            'patient.occupation',
            'service'
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
            'data' => $appointment
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
