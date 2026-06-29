<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->take(50)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $notifications->whereNull('read_at')->count(),
        ]);
    }

    public function markRead(Request $request, $id)
    {
        Notification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Notification marquée comme lue.']);
    }

    public function markAllRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Toutes les notifications marquées comme lues.']);
    }

    public function destroy(Request $request, $id)
    {
        Notification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'Notification supprimée.']);
    }
}
