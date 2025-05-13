<?php
namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\ChatDoctor;
use Illuminate\Http\Request;
use Minishlink\WebPush\WebPush;
use App\Models\OnlineConsultation;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Minishlink\WebPush\Subscription;
use App\Notifications\MessageNotification;

class TeleconsultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $onlineConsultation = $user->doctorOnlineConsultation()
            ->with(['patient.demographics', 'patient.user'])
            ->orderBy('created_at', 'desc') // Urutkan dari yang terbaru
            ->paginate(10);
    
        return response()->json([
            'status'  => 'success',
            'message' => 'Successfully get online consultation',
            'data'    => $onlineConsultation,
        ], 200);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(OnlineConsultation $onlineConsultation, Request $request)
    {
        $user = Auth::user();
        if ($onlineConsultation->doctorRelation->id != $user->id) {
            return response()->json([
                'status'  => 'unauthorize',
                'message' => 'Forbidden Access.',
            ], 403);
        }
        $validated = $request->validate([
            'message' => 'required|string',
        ]);
        $onlineConsultation->chats()->create([
            'sender_id'  => $onlineConsultation->doctorRelation->id,
            'receiver_id' => $onlineConsultation->patientRelation->id,
            'message' => $validated['message'],
        ]);
        $receiver = User::find($onlineConsultation->patientRelation->id);
        $message = $validated['message'];            
        try {
            $receiver->notify(new MessageNotification($receiver->email, $message));            
        } catch (Exception $e) {
            Log::error('Notification error: ' . $e->getMessage());
        }
        try {
            $subscriptions = $receiver->pushSubscriptions; // Ambil semua subscriptions untuk user
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
                    'title' => 'Message for you',
                    'body'  => $validated['message'],
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
        return response()->json($message, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(OnlineConsultation $onlineConsultation)
    {
        $user = Auth::user();

        // Pastikan user adalah dokter yang terkait dengan konsultasi ini
        if ($onlineConsultation->doctorRelation->id !== $user->id) {
            return response()->json([
                'status'  => 'Unauthorized',
                'message' => 'Forbidden access.',
            ], 403);
        }

        // Load relasi yang dibutuhkan untuk konsultasi
        $onlineConsultation->load(['patient.demographics', 'patient.chronics', 'patient.medications', 'patient.physicalExaminations', 'patient.immunizations', 'patient.occupation', 'patient.emergencyContact', 'patient.parentChronic', 'patient.medicalRecords', 'patient.allergy']);

        // Ambil pesan terkait konsultasi ini
        $messages = $onlineConsultation->chats()->orderBy('created_at', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Consultation retrieved successfully.',
            'consultation' => $onlineConsultation,
            'messages' => $messages
        ], 200);
    }


    public function complete(OnlineConsultation $onlineConsultation)
    {
        $onlineConsultation->update([
            'is_confirmed' => true
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully update status'
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChatDoctor $chatDoctor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChatDoctor $chatDoctor)
    {
        //
    }
}
