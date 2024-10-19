<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function complete(Appointment $appointment, Request $request)
    {
        MedicalRecord::factory()->create([            
            'doctor_id' => $request->doctor_id,
            'patient_id' => $request->patient_id,
            'clinic_id' => $request->clinic_id
        ]);

        $appointment->update([
            'status' => 'completed'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment completed successfully',
        ], 200);        
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        //
    }
}
