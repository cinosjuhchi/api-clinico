<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Minishlink\WebPush\WebPush;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\WaitingNumberHelper;
use App\Http\Controllers\Controller;
use Minishlink\WebPush\Subscription;
use App\Http\Requests\AppointmentRequest;
use App\Notifications\WaitingPatientNotification;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $patientId = $request->input('patient_id');
        $status = $request->input('status');
        $date = $request->input('date');

        if ($patientId) {
            $patient = $user->patients()->find($patientId);

            if (!$patient) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Patient not found or does not belong to the user',
                ], 404);
            }

            $appointmentsQuery = Appointment::where('patient_id', $patient->id)->orderBy('created_at', 'desc');
        } else {
            $patientIds = $user->patients()->pluck('id')->toArray();
            $appointmentsQuery = Appointment::whereIn('patient_id', $patientIds)->orderBy('created_at', 'desc');
        }

        if ($status) {
            $appointmentsQuery->where('status', $status);
        }

        if ($date) {
            $appointmentsQuery->where('appointment_date', $date);
        }

        $appointments = $appointmentsQuery->get();

        // Base relations yang selalu dimuat
        $baseRelations = [
            'patient:id,name',
            'doctor',
            'clinic.schedule',
            'bill',
            'medicalRecord.clinicService',
            'medicalRecord.serviceRecord',
            'medicalRecord.investigationRecord',
            'medicalRecord.medicationRecords.medication',
            'medicalRecord.procedureRecords',
            'medicalRecord.injectionRecords',
            'medicalRecord.allergies'
        ];

        // Jika status consultation, tambahkan relasi room.onConsultation
        if ($status === 'consultation' || $status === 'on-consultation') {
            $baseRelations[] = 'room.onConsultation';
        }

        // Load semua relasi yang diperlukan
        $appointments->load($baseRelations);

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
            // generate waiting number
            $waitingNumber = WaitingNumberHelper::generate(
                $validated["appointment_date"],
                $validated["doctor_id"],
                $validated["room_id"]
            );

            // Use transaction to maintain data consistency
            DB::transaction(function () use ($validated, $title, $slug, $clinic, $newVN, $patient, $waitingNumber) {
                Appointment::create([
                    'title' => $title,
                    'slug' => $slug,
                    'clinic_service_id' => $validated['visit_purpose'],
                    'current_condition' => $validated['current_condition'],
                    'status' => $patient->is_offline ? 'consultation' : 'pending',
                    'waiting_number' => $waitingNumber,
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
        // 1. Validate appointment status
        if (
            $appointment->status === 'consultation' ||
            $appointment->status === 'cancelled' ||
            $appointment->status === 'completed'
        ) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Appointment has been check-in!',
            ], 403);
        }

        // 2. Cek apakah ada janji temu lain pada hari yang sama yang masih aktif
        $existingConsultation = Appointment::where('doctor_id', $appointment->doctor_id)
            ->where('patient_id', $appointment->patient_id)
            ->where('appointment_date', $appointment->appointment_date)
            ->whereIn('status', ['consultation', 'take-medicine', 'waiting-payment', 'on-consultation'])
            ->exists();

        if ($existingConsultation) {
            return response()->json([
                'status' => 'failed',
                'message' => 'You already have an appointment that didn\'t complete yet.',
            ], 403);
        }

        // 3. Tentukan waiting number
        $waitingNumber = 1;

        $bookedConsultation = Appointment::where('appointment_date', $appointment->appointment_date)
            ->where('status', 'consultation')
            ->where('doctor_id', $appointment->doctor_id)
            ->where('room_id', $appointment->room_id)
            ->latest('updated_at')
            ->first();

        if ($bookedConsultation) {
            $waitingNumber = $bookedConsultation->waiting_number + 1;
        } else {
            $bookedOnConsultation = Appointment::where('appointment_date', $appointment->appointment_date)
                ->where('status', 'on-consultation')
                ->where('doctor_id', $appointment->doctor_id)
                ->where('room_id', $appointment->room_id)
                ->latest('updated_at')
                ->first();

            if ($bookedOnConsultation) {
                $waitingNumber = $bookedOnConsultation->waiting_number + 1;
            }
        }

        $time = now()->setTimezone('Asia/Jakarta')->toDateTimeString();

        // 4. Notifikasi ke clinic user dan staff
        $clinic = $appointment->clinic;
        $clinicUser = $clinic->user;
        $room = $appointment->room;
        $name = $appointment->patient->name;
        $message = "There are new patients waiting";

        try {
            // Laravel notification ke user utama
            $clinicUser->notify(new WaitingPatientNotification($room, $name, $message));
            $clinicUser->notifications()->latest()->first()?->update(['expired_at' => now()->addDay()]);

            // Laravel notification ke semua staff user
            foreach ($clinic->staffs as $staff) {
                if ($staff->user) {
                    $staff->user->notify(new WaitingPatientNotification($room, $name, $message));
                    $staff->user->notifications()->latest()->first()?->update(['expired_at' => now()->addDay()]);
                }
            }
            foreach ($clinic->doctors as $doctor) {
                if ($doctor->user) {
                    $doctor->user->notify(new WaitingPatientNotification($room, $name, $message));
                    $doctor->user->notifications()->latest()->first()?->update(['expired_at' => now()->addDay()]);
                }
            }
        } catch (Exception $e) {
            \Log::error('Notification error: ' . $e->getMessage());
        }

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject'    => env('APP_URL', 'https://clinico.site'),
                    'publicKey'  => env('VAPID_PUBLIC_KEY'),
                    'privateKey' => env('VAPID_PRIVATE_KEY'),
                ],
            ]);

            $payload = json_encode([
                'title' => 'There are new patient waiting',
                'body'  => 'Immediately check the patient on the waiting list',
                'icon'  => '/icon512_rounded.png',
                'data'  => [
                    'url' => 'https://clinic.clinico.site',
                ],
            ]);

            // Function helper untuk kirim WebPush ke user tertentu
            $sendWebPushToUser = function ($user) use ($webPush, $payload) {
                $subscriptions = $user->pushSubscriptions;
                if ($subscriptions->isNotEmpty()) {
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
                } else {
                    \Log::warning("No subscriptions found for user ID: {$user->id}");
                }
            };

            // WebPush untuk clinic user
            $sendWebPushToUser($clinicUser);

            // WebPush untuk semua staff
            foreach ($clinic->staffs as $staff) {
                if ($staff->user) {
                    $sendWebPushToUser($staff->user);
                }
            }
            foreach ($clinic->doctors as $doctor) {
                if ($doctor->user) {
                    $sendWebPushToUser($doctor->user);
                }
            }

            foreach ($webPush->flush() as $report) {
                $endpoint = $report->getRequest()->getUri()->__toString();
                if ($report->isSuccess()) {
                    \Log::info("Web Push sent successfully to {$endpoint}");
                } else {
                    \Log::error("Web Push failed to {$endpoint}: {$report->getReason()}");
                }
            }
        } catch (Exception $e) {
            \Log::error('Web Push error: ' . $e->getMessage());
        }

        // 5. Update appointment
        $appointment->update([
            'status' => 'consultation',
            'check_in_at' => $time,
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

}
