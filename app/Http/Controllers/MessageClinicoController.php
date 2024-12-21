<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageClinicoRequest;
use App\Models\MessageClinico;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class MessageClinicoController extends Controller
{
    public function sendMessage(StoreMessageClinicoRequest $request)
    {
        $validated = $request->validated();

        $message = MessageClinico::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'message' => $validated['message'],
        ]);

        return response()->json($message, 201);
    }

    public function getMessages(User $user)
    {
        $messages = MessageClinico::where(function ($query) use ($user) {
            $query->where('sender_id', Auth::id())
                ->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', Auth::id());
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages, 201);
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
                'sender.roles:id,name',
                'receiver.roles:id,name',
            ])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                $senderRole = $message->sender->roles->first()->name ?? null;
                $receiverRole = $message->receiver->roles->first()->name ?? null;

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

        $userRole = $user->roles->first()->name ?? null;
        $chatInfo = [
            'user' => [
                'id' => $user->id,
                'name' => $this->getNameBasedOnRole($user, $userRole),
                'role' => $userRole,
                'is_online' => $user->is_online ?? false,
            ],
            'messages' => $messages,
        ];

        return response()->json($chatInfo);
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
                return $user->clinic?->clinic_name ?? 'Unknown Clinic';
            case 'doctor':
                return $user->doctor?->doctor_name ?? 'Unknown Doctor';
            default:
                return 'Unknown User';
        }
    }
}
