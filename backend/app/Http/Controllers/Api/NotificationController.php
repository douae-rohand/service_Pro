<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index()
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Assuming user uses the Notifiable trait which provides notifications() relationship
        // or directly accessing the table if manual
        
        // Let's use the DB table directly to be safe given my previous manual insert in InterventionController
        $notifications = \Illuminate\Support\Facades\DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', 'App\Models\User')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
            
        $unreadCount = \Illuminate\Support\Facades\DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', 'App\Models\User')
            ->whereNull('read_at')
            ->count();

        // Transform data
        $transformed = $notifications->map(function ($n) {
            $data = json_decode($n->data, true);
            return [
                'id' => $n->id,
                'type' => $n->type, // or custom type from data
                'message' => $data['message'] ?? 'Nouvelle notification',
                'data' => $data,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at,
            ];
        });

        return response()->json([
            'data' => $transformed,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        \Illuminate\Support\Facades\DB::table('notifications')
            ->where('id', $id)
            ->where('notifiable_id', $user->id)
            ->update(['read_at' => now()]);

        return response()->json(['status' => 'success']);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        \Illuminate\Support\Facades\DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['status' => 'success']);
    }
}
