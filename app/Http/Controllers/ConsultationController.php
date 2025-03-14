<?php
namespace App\Http\Controllers;

use App\Http\Requests\CompleteAppointmentRequest;
use App\Models\Appointment;
use App\Models\ClinicService;
use App\Models\Injection;
use App\Models\MedicalRecord;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\CallPatientNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class ConsultationController extends Controller
{
    public function complete(Appointment $appointment, CompleteAppointmentRequest $request)
    {
        $user   = Auth::user();
        $doctor = $user->doctor;
        $clinic = $doctor->clinic;

        DB::beginTransaction();

        try {
            $validated = $request->validated();

            $patient = $appointment->patient;
            $user    = $patient->user_id;

            $bill = $appointment->bill()->create([
                'transaction_date' => $validated['transaction_date'],
                'total_cost'       => $validated['total_cost'],
                'user_id'          => $user ? $user : null,
                'patient_id'       => $patient->id,
                'clinic_id'        => $clinic->id,
                'doctor_id'        => $doctor->id,
            ]);

            $patient->physicalExaminations()->update([
                'blood_pressure' => $validated['blood_pressure'],
                'pulse_rate'     => $validated['pulse_rate'],
                'temperature'    => $validated['temperature'],
                'sp02'           => $validated['sp02'],
                'weight'         => $validated['weight'],
                'height'         => $validated['height'],
            ]);
            if ($validated['allergy']) {
                $patient->allergy()->delete();
                $patient->allergy()->create([
                    'name' => $validated['allergy']
                ]);
            } else {
                $patient->allergy()->delete();
            }            
            $medicalRecord = $appointment->medicalRecord;
            if ($medicalRecord) {
                $medicalRecord->update([
                    'patient_id'           => $appointment->patient_id,
                    'clinic_id'            => $appointment->clinic_id,
                    'doctor_id'            => $appointment->doctor_id,
                    'patient_condition'    => $appointment->current_condition,
                    'consultation_note'    => $validated['consultation_note'],
                    'physical_examination' => $validated['examination'],
                    'blood_pressure'       => $validated['blood_pressure'],
                    'plan'                 => $validated['plan'],
                    'sp02'                 => $validated['sp02'],
                    'temperature'          => $validated['temperature'],
                    'pulse_rate'           => $validated['pulse_rate'],
                    'pain_score'           => $validated['pain_score'],
                    'clinic_service_id'    => $validated['service_id'],
                    'current_history'      => $validated['current_history'],
                    'follow_up_date'       => $validated['follow_up_date'],
                    'follow_up_remark'     => $validated['follow_up_remark'],
                    'timer'                => $validated['timer'],

                ]);
            } else {
                $medicalRecord = $appointment->medicalRecord()->create([
                    'patient_id'           => $appointment->patient_id,
                    'clinic_id'            => $appointment->clinic_id,
                    'doctor_id'            => $appointment->doctor_id,
                    'patient_condition'    => $appointment->current_condition,
                    'consultation_note'    => $validated['consultation_note'],
                    'physical_examination' => $validated['examination'],
                    'blood_pressure'       => $validated['blood_pressure'],
                    'plan'                 => $validated['plan'],
                    'sp02'                 => $validated['sp02'],
                    'temperature'          => $validated['temperature'],
                    'pulse_rate'           => $validated['pulse_rate'],
                    'pain_score'           => $validated['pain_score'],
                    'clinic_service_id'    => $validated['service_id'],
                    'current_history'      => $validated['current_history'],
                    'follow_up_date'       => $validated['follow_up_date'],
                    'follow_up_remark'     => $validated['follow_up_remark'],
                    'timer'                => $validated['timer'],
                ]);
            }

            $service = ClinicService::find($validated['service_id']);

            $serviceRecord = $medicalRecord->serviceRecord()->create([
                'name'       => $service->name,
                'cost'       => $service->price,
                'patient_id' => $appointment->patient_id,
                'billing_id' => $bill->id,
            ]);

            foreach ($validated['diagnosis'] as $diagnosis) {
                $medicalRecord->diagnosisRecord()->create([
                    'diagnosis'  => $diagnosis,
                    'patient_id' => $appointment->patient_id,
                ]);
            }

            if (!empty($validated['investigations'])) {
                foreach ($validated['investigations'] as $investigation) {
                    $medicalRecord->investigationRecord()->create([
                        'type'       => $investigation['investigation_type'],
                        'item'       => $investigation['name'],
                        'cost'       => $investigation['cost'],
                        'patient_id' => $appointment->patient_id,
                        'billing_id' => $bill->id,
                    ]);
                }
            }
            if (!empty($validated['procedure'])) {
                foreach ($validated['procedure'] as $procedure) {
                    $medicalRecord->procedureRecords()->create([
                        'name'       => $procedure['name'],
                        'cost'       => $procedure['cost'],
                        'patient_id' => $appointment->patient_id,
                        'billing_id' => $bill->id,
                    ]);
                }
            }
            if (!empty($validated['injection'])) {
                foreach ($validated['injection'] as $injection) {
                    $medicalRecord->injectionRecords()->create([
                        'injection_id' => $injection['injection_id'],
                        'name'         => $injection['name'],
                        'price'        => $injection['price'],
                        'cost'         => $injection['cost'],
                        'patient_id'   => $appointment->patient_id,
                        'billing_id'   => $bill->id,
                    ]);
                }
            }

            if (!empty($validated['medicine'])) {
                foreach ($validated['medicine'] as $medicine) {
                    $medication = Medication::find($medicine['medicine_id']);
                    $price      = $medication->price;
                    $medicalRecord->medicationRecords()->create([
                        'medication_id' => $medicine['medicine_id'],
                        'medicine'      => $medicine['name'],
                        'frequency'     => $medicine['frequency'],
                        'price'         => $price,
                        'total_cost'    => $medicine['cost'],
                        'qty'           => $medicine['medicine_qty'],
                        'patient_id'    => $appointment->patient_id,
                        'billing_id'    => $bill->id,
                    ]);
                }
            }

            if (!empty($validated['risk_factors'])) {
                foreach ($validated['risk_factors'] as $risk) {
                    $medicalRecord->riskFactors()->create([
                        'name' => $risk,
                    ]);
                }
            }

            if(!empty($validated['gestational_age'])){
                $medicalRecord->gestationalAge()->updateOrCreate(                    
                    [
                        'plus' => $validated['gestational_age']['plus'],
                        'para' => $validated['gestational_age']['para'],
                        'gravida' => $validated['gestational_age']['gravida'],
                        'menstruation_date' => $validated['gestational_age']['menstruation_date']                    
                    ]
                );
            }

            if (!empty($validated['images'])) {
                foreach ($validated['images'] as $index => $image) {
                    // Store the image
                    $imagePath = $image->store('consultation_image');

                    $medicalRecord->consultationPhotos()->create([
                        'image_path' => $imagePath,
                    ]);

                }

            }
            if (!empty($validated['documents'])) {
                foreach ($validated['documents'] as $index => $document) {
                    // Store the image
                    $documentPath = $document->store('consultation_document');

                    $medicalRecord->consultationDocuments()->create([
                        'document_path' => $documentPath,
                        'type'          => 'document',
                    ]);

                }
            }
            if (!empty($validated['reports'])) {
                foreach ($validated['reports'] as $index => $report) {
                    // Store the image
                    $reportPath = $report->store('consultation_report');

                    $medicalRecord->consultationDocuments()->create([
                        'document_path' => $reportPath,
                        'type'          => 'report',
                    ]);

                }
            }
            if (!empty($validated['certificate'])) {
                $certificatePath = $validated['certificate']->store('certificate_document');
                $medicalRecord->consultationDocuments()->create([
                    'document_path' => $certificatePath,
                    'type'          => 'certificate',
                ]);
                
            }

            if($validated['alert'])
            {
                $appointment->update([
                    'alert' => $validated['alert']
                ]);
            }
            
            $status = $patient->is_offline ? 'completed' : 'waiting-payment';
            if (!empty($validated['medicine'])) {
                $appointment->update([
                    'status' => 'take-medicine',
                ]);
            } else {
                $appointment->update([
                    'status' => $status,
                ]);
            }
            
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Appointment completed successfully',
            ], 200);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'status'  => 'error',
                'message' => 'An error occurred while completing the appointment.',
                'error'   => $e->getMessage(),
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
            'staff' => optional($user->staff)->clinic,   // Use optional to handle potential null
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        $query = $request->input('q');

        if (! $clinic) {
            return response()->json([
                'status'  => 'failed',
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
                        'gestationalAge',
                        'consultationPhotos',
                        'consultationDocuments'
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
            'staff'  => $user->staff->clinic,
            default  => abort(401, 'Unauthorized access. Invalid role.'),
        };

        $query = $request->input('q');

        if (!$clinic) {
            $doctor = $user->doctor;
            if (!$doctor) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'User not found',
                ]);
            }

            // Menentukan antrian patokan (on-consultation > consultation)
            $currentAppointment = $doctor->consultationAppointments()
                ->whereIn('status', ['on-consultation', 'consultation'])
                ->orderByRaw("FIELD(status, 'on-consultation', 'consultation')")
                ->orderBy('waiting_number')
                ->first();

            $currentWaitingNumber = $currentAppointment?->waiting_number ?? null;

            $appointments = $doctor->consultationAppointments()
                ->with(['patient', 'doctor.category', 'clinic', 'service', 'bill', 'medicalRecord', 
                    'medicalRecord.clinicService', 'medicalRecord.serviceRecord', 
                    'medicalRecord.investigationRecord', 'medicalRecord.medicationRecords', 
                    'medicalRecord.procedureRecords', 'medicalRecord.injectionRecords', 
                    'medicalRecord.diagnosisRecord'])
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

            // Menambahkan waiting_time_prediction
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

        // Menentukan antrian patokan (on-consultation > consultation) untuk klinik
        $currentAppointment = $clinic->consultationAppointments()
            ->whereIn('status', ['on-consultation', 'consultation'])
            ->orderByRaw("FIELD(status, 'on-consultation', 'consultation')")
            ->orderBy('waiting_number')
            ->first();

        $currentWaitingNumber = $currentAppointment?->waiting_number ?? null;

        $appointments = $clinic->consultationAppointments()
            ->with(['patient', 'doctor.category', 'clinic', 'service', 'bill', 'medicalRecord', 
                'medicalRecord.clinicService', 'medicalRecord.serviceRecord', 
                'medicalRecord.investigationRecord', 'medicalRecord.medicationRecords', 
                'medicalRecord.procedureRecords', 'medicalRecord.injectionRecords', 
                'medicalRecord.diagnosisRecord'])
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

        // Menambahkan waiting_time_prediction
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


    public function takeMedicine(Request $request, Appointment $appointment)
    {
        if ($appointment->status == 'consultation' || $appointment->status == 'cancelled' || $appointment->status == 'completed') {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Appointment has been check-in!',
            ], 403);
        }
        $validated = $request->validate([
            'total_cost'               => 'required|numeric',
            'type'                     => 'required|in:cash,panel,clinico',
            'medicine'                 => 'nullable|array',
            'medicine.*.medicine_id'   => 'nullable|exists:medications,id',
            'medicine.*.name'          => 'required|string',
            'medicine.*.unit'          => 'required|string',
            'medicine.*.frequency'     => 'nullable|string',
            'medicine.*.cost'          => 'required|numeric',
            'medicine.*.medicine_qty'  => 'nullable|integer',

            'injection'                => 'nullable|array',
            'injection.*.injection_id' => 'nullable|exists:injections,id',
            'injection.*.name'         => 'required|string',
            'injection.*.cost'         => 'required|numeric',

            'procedure'                => 'nullable|array',
            'procedure.*.name'         => 'required|string',
            'procedure.*.remark'       => 'nullable|string',
            'procedure.*.cost'         => 'required|numeric',
        ]);
        $medicalRecord = $appointment->medicalRecord;
        try {
            DB::beginTransaction();
            $bill = $appointment->bill;
            if (! empty($validated['medicine'])) {
                $medicalRecord->medicationRecords()->delete();
                foreach ($validated['medicine'] as $medicine) {
                    $medication = Medication::find($medicine['medicine_id']);
                    $price      = $medication->price;
                    $medicalRecord->medicationRecords()->create([
                        'medicine'   => $medicine['name'],
                        'frequency'  => $medicine['frequency'],
                        'price'      => $price,
                        'total_cost' => $medicine['cost'],
                        'qty'        => $medicine['medicine_qty'],
                        'patient_id' => $appointment->patient_id,
                        'billing_id' => $bill->id,
                    ]);

                    $medication->total_amount -= $medicine['medicine_qty'];
                    $medication->save();
                }
            }
            if (! empty($validated['injection'])) {
                $medicalRecord->injectionRecords()->delete();
                foreach ($validated['injection'] as $injection) {
                    $medicalRecord->injectionRecords()->create([
                        'name'         => $injection['name'],
                        'cost'         => $injection['cost'],
                        'patient_id'   => $appointment->patient_id,
                        'billing_id'   => $bill->id,
                        'injection_id' => $injection['injection_id'],
                    ]);

                    $injection = Injection::find($injection['injection_id']);

                    if (! $injection) {
                        return response()->json([
                            'status'  => 'failed',
                            'message' => 'Injection not found',
                        ], 404);
                    }

                    $injection->total_amount -= 1;
                    $injection->save();
                }
            }
            if (! empty($validated['procedure'])) {
                $medicalRecord->procedureRecords()->delete();
                foreach ($validated['procedure'] as $procedure) {
                    $medicalRecord->procedureRecords()->create([
                        'name'       => $procedure['name'],
                        'cost'       => $procedure['cost'],
                        'remark'     => $procedure['remark'],
                        'patient_id' => $appointment->patient_id,
                        'billing_id' => $bill->id,
                    ]);
                }
            }

            $bill->total_cost = $validated['total_cost'];
            if ($validated['type'] == 'cash' || $validated == 'panel') {
                $appointment->status = 'completed';
                $bill->type          = $validated['type'];
                $bill->is_paid       = true;
            } else {
                $appointment->status = 'waiting-payment';
                if ($appointment->patient->is_offline) {
                    $appointment->status = 'completed';
                }
            }

            $bill->save();
            $appointment->save();
            DB::commit();
            return response()->json([
                'status'  => 'success',
                'message' => 'Appointment in-progress successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status'  => 'failed',
                'message' => 'Error something wrong happened',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function callPatient(Appointment $appointment)
    {
        $patient = Patient::find($appointment->patient_id);
        $user    = $patient->user;
        $room    = $appointment->room;

        // Kirim notifikasi Laravel
        try {
            $message = "Your number {$appointment->waiting_number} is calling. Please proceed to {$room->name} now for consultation. Thank you.";
            $user->notify(new CallPatientNotification($room, $appointment->waiting_number, 'Enter the Room', $message));
            $notification = $user->notifications()->latest()->first();
            $notification->update([
                'expired_at' => now()->addDay(),
            ]);
        } catch (Exception $e) {
            Log::error('Notification error: ' . $e->getMessage());
        }

        // Kirim Web Push Notification
        try {
            $subscriptions = $user->pushSubscriptions; // Ambil semua subscriptions untuk user
            if ($subscriptions->isNotEmpty()) {
                $webPush = new WebPush([
                    'VAPID' => [
                        'subject'    => env('APP_URL', 'https://clinico.site'),
                        'publicKey'  => env('VAPID_PUBLIC_KEY'),
                        'privateKey' => env('VAPID_PRIVATE_KEY'),
                    ],
                ]);

                // Payload data untuk notifikasi
                $payload = json_encode([
                    'title' => 'The doctor calling you',
                    'body'  => "Your number {$appointment->waiting_number} is calling. Please proceed to {$room->name} now for consultation. Thank you.",
                    'icon'  => '/icon512_rounded.png',
                    'data'  => [
                        'url' => env('WEB_CLINICO_URL'),
                    ],
                ]);

                // Kirim notifikasi ke semua subscriptions
                foreach ($subscriptions as $subscription) {
                    $webPush->queueNotification(
                        Subscription::create([
                            'endpoint' => $subscription->endpoint,
                            'keys'     => [
                                'p256dh' => $subscription->p256dh,
                                'auth'   => $subscription->auth,
                            ],
                        ]),
                        $payload
                    );
                }

                // Flush semua notifikasi dan log hasilnya
                foreach ($webPush->flush() as $report) {
                    $endpoint = $report->getRequest()->getUri()->__toString();
                    if ($report->isSuccess()) {
                        Log::info("Web Push sent successfully to {$endpoint}");
                    } else {
                        Log::error("Web Push failed to {$endpoint}: {$report->getReason()}");
                    }
                }
            } else {
                Log::error('Web Push error: No subscriptions found for user.');
            }
        } catch (Exception $e) {
            Log::error('Web Push error: ' . $e->getMessage());
        }

        // Periksa apakah ini panggilan pertama
        if ($appointment->status != 'on-consultation') {
            try {
                DB::beginTransaction();
                $appointment->status = 'on-consultation';
                $appointment->save();
                DB::commit();
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Appointment on-consultation successfully!',
                ], 200);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 'failed',
                    'error'  => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Notification sent successfully!',
        ], 200);
    }
    public function callPatientVitalSign(Appointment $appointment)
    {
        $patient = Patient::find($appointment->patient_id);
        $user    = $patient->user;
        $room    = $appointment->room;

        // Kirim notifikasi Laravel
        try {
            $message = "Your number {$appointment->waiting_number} is calling. Please proceed to Triage now for vital signs taking. Thank you.";
            $user->notify(new CallPatientNotification($room, $appointment->waiting_number, 'Vital Checks', $message));
            $notification = $user->notifications()->latest()->first();
            $notification->update([
                'expired_at' => now()->addDay(),
            ]);
        } catch (Exception $e) {
            Log::error('Notification error: ' . $e->getMessage());
        }

        // Kirim Web Push Notification
        try {
            $subscriptions = $user->pushSubscriptions; // Ambil semua subscriptions untuk user
            if ($subscriptions->isNotEmpty()) {
                $webPush = new WebPush([
                    'VAPID' => [
                        'subject'    => env('APP_URL', 'https://clinico.site'),
                        'publicKey'  => env('VAPID_PUBLIC_KEY'),
                        'privateKey' => env('VAPID_PRIVATE_KEY'),
                    ],
                ]);

                // Payload data untuk notifikasi
                $payload = json_encode([
                    'title' => 'Vital checks calling you',
                    'body'  => "Your number {$appointment->waiting_number} is calling. Please proceed to Triage now for vital signs taking. Thank you.",
                    'icon'  => '/icon512_rounded.png',
                    'data'  => [
                        'url' => env('WEB_CLINICO_URL'),
                    ],
                ]);

                // Kirim notifikasi ke semua subscriptions
                foreach ($subscriptions as $subscription) {
                    $webPush->queueNotification(
                        Subscription::create([
                            'endpoint' => $subscription->endpoint,
                            'keys'     => [
                                'p256dh' => $subscription->p256dh,
                                'auth'   => $subscription->auth,
                            ],
                        ]),
                        $payload
                    );
                }

                // Flush semua notifikasi dan log hasilnya
                foreach ($webPush->flush() as $report) {
                    $endpoint = $report->getRequest()->getUri()->__toString();
                    if ($report->isSuccess()) {
                        Log::info("Web Push sent successfully to {$endpoint}");
                    } else {
                        Log::error("Web Push failed to {$endpoint}: {$report->getReason()}");
                    }
                }
            } else {
                Log::error('Web Push error: No subscriptions found for user.');
            }
        } catch (Exception $e) {
            Log::error('Web Push error: ' . $e->getMessage());
        }        

        return response()->json([
            'status'  => 'success',
            'message' => 'Notification sent successfully!',
        ], 200);
    }
    public function callPatientDispensary(Appointment $appointment)
    {
        $patient = Patient::find($appointment->patient_id);
        $user    = $patient->user;
        $room    = $appointment->room;

        // Kirim notifikasi Laravel
        try {
            $message = "Your number {$appointment->waiting_number} is calling. Please proceed to Dispensary now to take the medicine. Thank you.";
            $user->notify(new CallPatientNotification($room, $appointment->waiting_number, 'Dispensary', $message));
            $notification = $user->notifications()->latest()->first();
            $notification->update([
                'expired_at' => now()->addDay(),
            ]);
        } catch (Exception $e) {
            Log::error('Notification error: ' . $e->getMessage());
        }

        // Kirim Web Push Notification
        try {
            $subscriptions = $user->pushSubscriptions; // Ambil semua subscriptions untuk user
            if ($subscriptions->isNotEmpty()) {
                $webPush = new WebPush([
                    'VAPID' => [
                        'subject'    => env('APP_URL', 'https://clinico.site'),
                        'publicKey'  => env('VAPID_PUBLIC_KEY'),
                        'privateKey' => env('VAPID_PRIVATE_KEY'),
                    ],
                ]);

                // Payload data untuk notifikasi
                $payload = json_encode([
                    'title' => 'Dispensary calling you',
                    'body'  => "Your number {$appointment->waiting_number} is calling. Please proceed to Dispensary now to take the medicine. Thank you.",
                    'icon'  => '/icon512_rounded.png',
                    'data'  => [
                        'url' => env('WEB_CLINICO_URL'),
                    ],
                ]);

                // Kirim notifikasi ke semua subscriptions
                foreach ($subscriptions as $subscription) {
                    $webPush->queueNotification(
                        Subscription::create([
                            'endpoint' => $subscription->endpoint,
                            'keys'     => [
                                'p256dh' => $subscription->p256dh,
                                'auth'   => $subscription->auth,
                            ],
                        ]),
                        $payload
                    );
                }

                // Flush semua notifikasi dan log hasilnya
                foreach ($webPush->flush() as $report) {
                    $endpoint = $report->getRequest()->getUri()->__toString();
                    if ($report->isSuccess()) {
                        Log::info("Web Push sent successfully to {$endpoint}");
                    } else {
                        Log::error("Web Push failed to {$endpoint}: {$report->getReason()}");
                    }
                }
            } else {
                Log::error('Web Push error: No subscriptions found for user.');
            }
        } catch (Exception $e) {
            Log::error('Web Push error: ' . $e->getMessage());
        }        

        return response()->json([
            'status'  => 'success',
            'message' => 'Notification sent successfully!',
        ], 200);
    }

    public function getPreviousConsultation(Request $request)
    {
        // param required: id_clinic, patient_id
        $clinicId = $request->input("clinic_id");
        if (! $clinicId) {
            return response()->json([
                "status"  => "failed",
                "message" => "parameter clinic_id is required",
            ], 400);
        }

        $patientId = $request->input("patient_id");
        if (! $patientId) {
            return response()->json([
                "status"  => "failed",
                "message" => "parameter patient_id is required",
            ], 400);
        }

        // get prev cons by clinic_id, patient_id
        $appointment = Appointment::with('clinic', 'patient')
            ->where("clinic_id", $clinicId)
            ->where("patient_id", $patientId)
            ->where("status", "completed")
            ->orderBy('appointment_date', 'desc')
            ->first();

        // jika data kosong, maka return error empty
        if (! $appointment) {
            return response()->json(['error' => 'No completed consultations found.'], 404);
        }

        return response()->json([
            "status" => "success",
            "data"   => $appointment,
        ]);
    }
}
