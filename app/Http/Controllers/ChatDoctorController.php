<?php
namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\ChatDoctor;
use Minishlink\WebPush\WebPush;
use App\Models\OnlineConsultation;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Minishlink\WebPush\Subscription;
use App\Notifications\MessageNotification;
use App\Http\Requests\StoreChatDoctorRequest;
use App\Http\Requests\UpdateChatDoctorRequest;

class ChatDoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OnlineConsultation $onlineConsultation, StoreChatDoctorRequest $request)
    {
        $user = Auth::user();
        if ($onlineConsultation->patientRelation->id !== $user->id) {
            return response()->json([
                'status'  => 'Unauthorize',
                'message' => 'Forbidden access.',
            ], 403);
        }
        $validated = $request->validated();
        $onlineConsultation->chats()->create([
            'message' => $validated['message'],
            'sender_id' => $onlineConsultation->patientRelation->id,
            'receiver_id'  => $onlineConsultation->doctorRelation->id,
        ]);
        $receiver = User::find($onlineConsultation->doctorRelation->id);
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
        return response()->json([
            'status' => 'success',
            'message' => 'Success to send message'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ChatDoctor $chatDoctor)
    {
        //
    }

    /**
    * Show the form for editing the specified resource.
     */
    public function edit(ChatDoctor $chatDoctor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatDoctorRequest $request, ChatDoctor $chatDoctor)
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
