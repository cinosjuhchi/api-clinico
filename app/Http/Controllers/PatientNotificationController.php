<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PatientNotificationController extends Controller
{
    public function getNotifications(Request $request)
    {
        $user = $request->user();
        // Mengambil semua notifikasi yang belum dibaca
        return response()->json($user->unreadNotifications);
    }

    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['message' => 'Notification marked as read'], 200);
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All notifications marked as read'], 200);
    }
}
