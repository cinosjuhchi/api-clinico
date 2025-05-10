<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Routing\Controller;

class ClinicNotificationController extends Controller
{
    public function getNotifications(Request $request)
    {
        $user = $request->user();

        // Mengambil semua notifikasi
        $notifications = $user->notifications()
            ->where('expired_at', '>', Carbon::now())
            ->latest()
            ->get();

        // Menghitung jumlah notifikasi yang belum dibaca
        $unreadCount = $user->unreadNotifications
            ->where('expired_at', '>', Carbon::now())
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'success' => true,
        ]);

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
