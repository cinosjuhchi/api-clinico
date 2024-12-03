<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClinicService;
use App\Models\MedicalRecord;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\CallPatientNotification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $user = Auth::user();
        $doctor = $user->doctor;
        $clinic = $doctor->clinic;

        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'blood_pressure' => 'required|string',
                'pulse_rate' => 'required|numeric',
                'temperature' => 'required|numeric',
                'weight' => 'required|numeric',
                'height' => 'required|numeric',
                'sp02' => 'required|numeric',
                'pain_score' => 'required|numeric',
                'respiratory_rate' => 'required|numeric',
                // History
                'patient_condition' => 'required|string',
                'consultation_note' => 'required|string',
                'examination' => 'nullable|string',
                // Diagnosis
                'diagnosis' => 'required|array',
                'diagnosis.*' => 'required|string',
                'plan' => 'required|string',
                // Treatment
                'investigations' => 'nullable|array',
                'investigations.*.investigation_type' => 'required|string',
                'investigations.*.remark' => 'nullable|string',
                'investigations.*.name' => 'required|string',
                'investigations.*.cost' => 'required|numeric',
                // Treatment
                'procedure' => 'nullable|array',
                'procedure.*.name' => 'required|string',
                'procedure.*.remark' => 'nullable|string',
                'procedure.*.cost' => 'required|numeric',

                'injection' => 'nullable|array',
                'injection.*.injection_id' => 'nullable|exists:injections,id',
                'injection.*.name' => 'required|string',
                'injection.*.price' => 'required|numeric',
                'injection.*.cost' => 'required|numeric',

                'medicine' => 'nullable|array',
                'medicine.*.medicine_id' => 'nullable|exists:medications,id',
                'medicine.*.name' => 'required|string',
                'medicine.*.unit' => 'required|string',
                'medicine.*.frequency' => 'nullable|string',
                'medicine.*.cost' => 'required|numeric',
                'medicine.*.medicine_qty' => 'nullable|integer',
                // Bill
                'total_cost' => 'required|numeric',
                'transaction_date' => 'required|date',
                'service_id' => 'required|exists:clinic_services,id',

            ]);

            $patient = $appointment->patient;
            $user = $patient->user_id;

            $bill = $appointment->bill()->create([
                'transaction_date' => $validated['transaction_date'],
                'total_cost' => $validated['total_cost'],
                'user_id' => $user,
                'clinic_id' => $clinic->id,
                'doctor_id' => $doctor->id,
            ]);

            $patient->physicalExaminations()->update([
                'blood_pressure' => $validated['blood_pressure'],
                'pulse_rate' => $validated['pulse_rate'],
                'temperature' => $validated['temperature'],
                'sp02' => $validated['sp02'],
                'weight' => $validated['weight'],
                'height' => $validated['height'],
            ]);
            $medicalRecord = $appointment->medicalRecord;
            if($medicalRecord) {
                $medicalRecord->update([
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
                    'clinic_service_id' => $validated['service_id'],
                ]);
            } else{
                  $medicalRecord = $appointment->medicalRecord()->create([
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
                    'clinic_service_id' => $validated['service_id'],
                ]);
            }

            $service = ClinicService::find($validated['service_id']);

            $serviceRecord = $medicalRecord->serviceRecord()->create([
                'name' => $service->name,
                'cost' => $service->price,
                'patient_id' => $appointment->patient_id,
                'billing_id' => $bill->id,
            ]);

            foreach ($validated['diagnosis'] as $diagnosis) {
                $medicalRecord->diagnosisRecord()->create([
                    'diagnosis' => $diagnosis,
                    'patient_id' => $appointment->patient_id,
                ]);
            }

            if (!empty($validated['investigations'])) {
                foreach ($validated['investigations'] as $investigation) {
                    $medicalRecord->investigationRecord()->create([
                        'type' => $investigation['investigation_type'],
                        'item' => $investigation['name'],
                        'cost' => $investigation['cost'],
                        'patient_id' => $appointment->patient_id,
                        'billing_id' => $bill->id,
                    ]);
                }
            }
            if (!empty($validated['procedure'])) {
                foreach ($validated['procedure'] as $procedure) {
                    $medicalRecord->procedureRecords()->create([
                        'name' => $procedure['name'],
                        'cost' => $procedure['cost'],
                        'patient_id' => $appointment->patient_id,
                        'billing_id' => $bill->id,
                    ]);
                }
            }
            if (!empty($validated['injection'])) {
                foreach ($validated['injection'] as $injection) {
                    $medicalRecord->injectionRecords()->create([
                        'injection_id' => $injection['injection_id'],
                        'name' => $injection['name'],
                        'price' => $injection['price'],
                        'cost' => $injection['cost'],
                        'patient_id' => $appointment->patient_id,
                        'billing_id' => $bill->id,
                    ]);
                }
            }

            if (!empty($validated['medicine'])) {
                foreach ($validated['medicine'] as $medicine) {
                    $medication = Medication::find($medicine['medicine_id']);
                    $price = $medication->price;
                    $medicalRecord->medicationRecords()->create([
                        'medication_id' => $medicine['medicine_id'],
                        'medicine' => $medicine['name'],
                        'frequency' => $medicine['frequency'],
                        'price' => $price,
                        'total_cost' => $medicine['cost'],
                        'qty' => $medicine['medicine_qty'],
                        'patient_id' => $appointment->patient_id,
                        'billing_id' => $bill->id,
                    ]);
                }
            }

            if (!empty($validated['medicine'])) {
                $appointment->update([
                    'status' => 'take-medicine',
                ]);
            } else {
                $appointment->update([
                    'status' => 'waiting-payment',
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

    public function dispensary(Request $request)
    {
        $user = Auth::user();

        // Determine the clinic based on user role with more explicit handling
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => optional($user->doctor)->clinic, // Use optional to handle potential null
            'staff' => optional($user->staff)->clinic, // Use optional to handle potential null
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        $query = $request->input('q');

        if (!$clinic) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Clinic not found for this user',
            ], 404);
        }

        $appointments = $clinic->consultationTakeMedicine()
            ->with([
                'patient',
                'doctor.category',
                'clinic',
                'service',
                'bill',
                'medicalRecord' => function ($query) {
                    // Ensure eager loading works correctly
                    $query->with([
                        'clinicService',
                        'serviceRecord',
                        'investigationRecord',
                        'medicationRecords.medication',
                        'procedureRecords',
                        'injectionRecords.injection',
                        'diagnosisRecord',
                    ]);
                },
            ])
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQuery) use ($query) {
                    $subQuery->where('waiting_number', 'like', "%{$query}%")
                        ->orWhereHas('patient.demographics', function ($categoryQuery) use ($query) {
                            $categoryQuery->where('name', 'like', "%{$query}%");
                        });
                });
            })
            ->latest()
            ->paginate(5);

        return response()->json($appointments);
    }

    public function consultationEntry(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        $query = $request->input('q');

        if (!$clinic) {
            $doctor = $user->doctor;
            if (!$doctor) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'user not found',
                ]);
            }
            $appointments = $doctor->consultationAppointments()->with(['patient', 'doctor.category', 'clinic', 'service', 'bill', 'medicalRecord', 'medicalRecord.clinicService', 'medicalRecord.serviceRecord', 'medicalRecord.investigationRecord', 'medicalRecord.medicationRecords', 'medicalRecord.procedureRecords', 'medicalRecord.injectionRecords', 'medicalRecord.diagnosisRecord'])->when($query, function ($q) use ($query) {
                $q->where(function ($subQuery) use ($query) {
                    $subQuery->where('waiting_number', 'like', "%{$query}%")
                        ->orWhereHas('patient.demographics', function ($categoryQuery) use ($query) {
                            $categoryQuery->where('name', 'like', "%{$query}%");
                        });
                });
            })->latest()->paginate(5);
            return response()->json($appointments);

        }
        $appointments = $clinic->consultationAppointments()->with(['patient', 'doctor.category', 'clinic', 'service', 'bill', 'medicalRecord', 'medicalRecord.clinicService', 'medicalRecord.serviceRecord', 'medicalRecord.investigationRecord', 'medicalRecord.medicationRecords', 'medicalRecord.procedureRecords', 'medicalRecord.injectionRecords', 'medicalRecord.diagnosisRecord'])->when($query, function ($q) use ($query) {
            $q->where(function ($subQuery) use ($query) {
                $subQuery->where('waiting_number', 'like', "%{$query}%")
                    ->orWhereHas('patient.demographics', function ($categoryQuery) use ($query) {
                        $categoryQuery->where('name', 'like', "%{$query}%");
                    });
            });
        })->latest()->paginate(5);
        return response()->json($appointments);

    }

    public function takeMedicine(Request $request, Appointment $appointment)
    {
        if ($appointment->status == 'consultation' || $appointment->status == 'cancelled' || $appointment->status == 'completed') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Appointment has been check-in!',
            ], 403);
        }
        $validated = $request->validate([
            'total_cost' => 'required|numeric',
            'type' => 'required|in:cash,panel,clinico',
            'medicine' => 'nullable|array',
            'medicine.*.medicine_id' => 'nullable|exists:medications,id',
            'medicine.*.name' => 'required|string',
            'medicine.*.unit' => 'required|string',
            'medicine.*.frequency' => 'nullable|string',
            'medicine.*.cost' => 'required|numeric',
            'medicine.*.medicine_qty' => 'nullable|integer',

            'injection' => 'nullable|array',
            'injection.*.injection_id' => 'nullable|exists:injections,id',
            'injection.*.name' => 'required|string',
            'injection.*.cost' => 'required|numeric',

            'procedure' => 'nullable|array',
            'procedure.*.name' => 'required|string',
            'procedure.*.remark' => 'nullable|string',
            'procedure.*.cost' => 'required|numeric',
        ]);
        $medicalRecord = $appointment->medicalRecord;
        try {
            DB::beginTransaction();
            $bill = $appointment->bill;
            if (!empty($validated['medicine'])) {
                $medicalRecord->medicationRecords()->delete();
                foreach ($validated['medicine'] as $medicine) {
                    $medication = Medication::find($medicine['medicine_id']);
                    $price = $medication->price;
                    $medicalRecord->medicationRecords()->create([
                        'medicine' => $medicine['name'],
                        'frequency' => $medicine['frequency'],
                        'price' => $price,
                        'total_cost' => $medicine['cost'],
                        'qty' => $medicine['medicine_qty'],
                        'patient_id' => $appointment->patient_id,
                        'billing_id' => $bill->id,
                    ]);
                }
            }
            if (!empty($validated['injection'])) {
                $medicalRecord->injectionRecords()->delete();
                foreach ($validated['injection'] as $injection) {
                    $medicalRecord->injectionRecords()->create([
                        'name' => $injection['name'],
                        'cost' => $injection['cost'],
                        'patient_id' => $appointment->patient_id,
                        'billing_id' => $bill->id,
                        'injection_id' => $injection['injection_id'],
                    ]);
                }
            }
            if (!empty($validated['procedure'])) {
                $medicalRecord->procedureRecords()->delete();
                foreach ($validated['procedure'] as $procedure) {
                    $medicalRecord->procedureRecords()->create([
                        'name' => $procedure['name'],
                        'cost' => $procedure['cost'],
                        'remark' => $procedure['remark'],
                        'patient_id' => $appointment->patient_id,
                        'billing_id' => $bill->id,
                    ]);
                }
            }

            $bill->total_cost = $validated['total_cost'];
            if($validated['type'] == 'cash' || $validated == 'panel')
            {
                $appointment->status = 'completed';
                $bill->type = $validated['type'];
                $bill->is_paid = true;
            } else{
                $appointment->status = 'waiting-payment';
            }

            $bill->save();
            $appointment->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Appointment in-progress successfully',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 'failed',
                'message' => 'Error something wrong happened',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function callPatient(Appointment $appointment)
    {
        if ($appointment->status == 'on-consultation') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Appointment has been called!',
            ], 403);
        }
        $patient = Patient::find($appointment->patient_id);
        $user = $patient->user;
        $room = $appointment->room;
        try {
            $user->notify(new CallPatientNotification($room, $appointment->waiting_number));
            $notification = $user->notifications()->latest()->first();
            $notification->update([
                'expired_at' => now()->addDay(),
            ]);
        } catch (\Exception $e) {
            Log::error('Notification error: ' . $e->getMessage());
        }
        $appointment->status = 'on-consultation';
        $appointment->save();
        return response()->json([
            'status' => 'success',
            'messaage' => 'Appointment on-consultation successfully!',
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
