<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\MessageClinico;
use Illuminate\Support\Carbon;
use Minishlink\WebPush\WebPush;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Minishlink\WebPush\Subscription;
use App\Notifications\MessageNotification;
use App\Http\Requests\StoreMessageClinicoRequest;

class MessageClinicoController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        $clinic->load(['doctors.user', 'staffs.user', 'doctors.employmentInformation', 'staffs.employmentInformation']);
        return response()->json([
            'member' => $clinic
        ]);

    }

    public function sendMessage(StoreMessageClinicoRequest $request)
    {
        $validated = $request->validated();

        $message = MessageClinico::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'message' => $validated['message'],
        ]);

        $user = User::find($validated['receiver_id']);

        try {
            $message = $validated['message'];            
            $user->notify(new MessageNotification($user->email, $message));            
        } catch (Exception $e) {
            Log::error('Notification error: ' . $e->getMessage());
        }

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

    public function getMessages(User $user)
    {
        // Update unread messages from the sender to mark them as read
        MessageClinico::where('sender_id', $user->id)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        // Ambil semua pesan antara user yang sedang login dan user yang dimaksud
        $messages = MessageClinico::where(function ($query) use ($user) {
            $query->where('sender_id', Auth::id())
                ->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', Auth::id());
        })->orderBy('created_at', 'asc')->get();
        
        return response()->json($messages, 200);
    }


    public function getChatHistory(User $user)
    {
        $messages = MessageClinico::where(function ($query) use ($user) {
            $query->where('sender_id', Auth::id())
                ->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', Auth::id());
        })
            ->with([
                'sender.clinic:id,user_id,name as clinic_name',
                'sender.doctor:id,user_id,name as doctor_name',
                'receiver.clinic:id,user_id,name as clinic_name',
                'receiver.doctor:id,user_id,name as doctor_name',
                'sender',
                'receiver',
            ])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                $senderRole = $message->sender->role ?? null;
                $receiverRole = $message->receiver->role ?? null;

                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'is_sender' => $message->sender_id === Auth::id(),
                    'timestamp' => $message->created_at,
                    'formatted_time' => $message->created_at->format('H:i'),
                    'date' => $message->created_at->format('Y-m-d'),
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $this->getNameBasedOnRole($message->sender, $senderRole),
                        'role' => $senderRole,
                    ],
                    'receiver' => [
                        'id' => $message->receiver->id,
                        'name' => $this->getNameBasedOnRole($message->receiver, $receiverRole),
                        'role' => $receiverRole,
                    ],
                ];
            })
            ->groupBy('date')
            ->map(function ($groupedMessages, $date) {
                return [
                    'date' => $date,
                    'formatted_date' => $this->formatDate(Carbon::parse($date)),
                    'messages' => $groupedMessages,
                ];
            })
            ->values();

        // Hitung jumlah pesan yang belum dibaca dari user tersebut ke Auth::user()
        $unreadCount = MessageClinico::where('sender_id', $user->id)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();

        $userRole = $user->role ?? null;
        $chatInfo = [
            'user' => [
                'id' => $user->id,
                'name' => $this->getNameBasedOnRole($user, $userRole),
                'role' => $userRole,
                'is_online' => $user->is_online ?? false,
            ],
            'unread_count' => $unreadCount,
            'messages' => $messages,
        ];

        return response()->json($chatInfo);
    }

    public function getTotalUnreadMessages()
    {
        $unreadCount = MessageClinico::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();

            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount
            ], 200);
    }



    private function formatDate(Carbon $date): string
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        if ($date->isSameDay($today)) {
            return 'Today';
        }
        if ($date->isSameDay($yesterday)) {
            return 'Yesterday';
        }
        if ($date->isCurrentYear()) {
            return $date->format('j F');
        }
        return $date->format('j F Y');
    }

    private function getNameBasedOnRole(?User $user, ?string $role): string
    {
        if (!$user) {
            return 'Unknown User';
        }

        switch ($role) {
            case 'clinic':
                return $user->clinic?->name ?? 'Unknown Clinic';
            case 'doctor':
                return $user->doctor?->name ?? 'Unknown Doctor';
            case 'staff':
                return $user->staff?->name ?? 'Unknown Doctor';
            default:
                return 'Unknown User';
        }
    }
}
