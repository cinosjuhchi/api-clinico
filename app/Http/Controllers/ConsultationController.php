<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Models\MedicalRecord;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

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
        DB::beginTransaction();

        try {
            $validated = $request->validate([                            
                'blood_pressure' => 'required|numeric',
                'pulse_rate' => 'required|numeric',
                'temperature' => 'required|numeric',
                'weight' => 'required|numeric',
                'height' => 'required|numeric',
                'sp02' => 'required|numeric',
                'pain_score' => 'required|numeric',
                // History
                'patient_condition' => 'required|string',
                'consultation_note' => 'required|string',
                'examination' => 'nullable|string',
                // Diagnosis
                'diagnosis' => 'required|array',
                'diagnosis.*' => 'required|string',
                'plan' => 'required|string',
                // Treatment
                'procedure' => 'required|array',
                'procedure.*.name' => 'required|string',            
                'procedure.*.cost' => 'required|numeric',

                'injection' => 'nullable|array',
                'injection.*.name' => 'required|string',
                'injection.*.price' => 'required|numeric',                        
                'injection.*.cost' => 'required|numeric',

                'medicine' => 'nullable|array',
                'medicine.*.name' => 'required|string',            
                'medicine.*.unit' => 'required|string',                    
                'medicine.*.frequency' => 'nullable|string',
                'medicine.*.cost' => 'required|numeric',
                // Bill
                'total_cost' => 'required|numeric',
                'transaction_date' => 'required|date',
            ]);

            $bill = Billing::create([
                'transaction_date' => $validated['transaction_date'],
                'total_cost' => $validated['total_cost'],
            ]);

            $medicalRecord = MedicalRecord::create([
                'patient_id' => $appointment->patient_id,
                'clinic_id' => $appointment->clinic_id,
                'doctor_id' => $appointment->doctor_id,
                'patient_condition' => $appointment->current_condition,
                'consultation_note' => $validated['consultation_note'],
                'physical_examination' => $validated['examination'],
                'blood_pressure' => $validated['blood_pressure'],
                'plan' => $validated['plan'],
                'sp02' => $validated['sp02'],
                'temperature' => $validated['temperature'],
                'pulse_rate' => $validated['pulse_rate'],            
                'pain_score' => $validated['pain_score'],
            ]);      

            foreach($validated['diagnosis'] as $diagnosis) {
                $medicalRecord->diagnosisRecord()->create([
                    'diagnosis' => $diagnosis
                ]);
            }

            foreach($validated['procedure'] as $procedure) {
                $medicalRecord->procedureRecords()->create([
                    'name' => $procedure['name'],
                    'cost' => $procedure['cost'],
                    'patient_id' => $appointment->patient_id,
                    'billing_id' => $bill->id
                ]);
            }
            if(!empty($validated['injection'])) {
                foreach($validated['injection'] as $injection) {
                    $medicalRecord->injectionRecords()->create([
                        'name' => $injection['name'],
                        'price' => $injection['price'],
                        'cost' => $injection['cost'],
                        'patient_id' => $appointment->patient_id,
                        'billing_id' => $bill->id
                    ]);
                }                
            }

            if(!empty($validated['medicine'])) {
                foreach($validated['medicine'] as $medicine) {
                    $medicalRecord->medicationRecords()->create([
                        'medicine' => $medicine['name'],                
                        'frequency' => $medicine['frequency'],
                        'price' => $medicine['cost'],
                        'patient_id' => $appointment->patient_id,
                        'billing_id' => $bill->id
                    ]);
                }
            }

            if(!empty($validated['medicine'])) {
                $appointment->update([
                    'status' => 'take-medicine'
                ]);
            } else {
                $appointment->update([
                    'status' => 'waiting-payment'
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Appointment completed successfully',
            ], 200);        

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while completing the appointment.',
                'error' => $e->getMessage(),
            ], 500);
        }
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
