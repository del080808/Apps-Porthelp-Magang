<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /api/notifications — ambil notifikasi milik user
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        $unreadCount = $notifications->where('is_read', false)->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount
        ]);
    }

    // PUT /api/notifications/{id}/read — tandai 1 notif dibaca
    public function markAsRead(Request $request, $id)
    {
        $notif = Notification::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notif->update(['is_read' => true]);

        return response()->json(['message' => 'Notifikasi ditandai dibaca']);
    }

    // PUT /api/notifications/read-all — tandai semua dibaca
    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Semua notifikasi ditandai dibaca']);
    }
}