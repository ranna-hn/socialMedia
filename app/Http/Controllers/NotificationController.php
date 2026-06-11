<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->appNotifications()
            ->latest('created_at')
            ->limit(15)
            ->get()
            ->map(fn (Notification $notification): array => $this->serializeNotification($notification));

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $request->user()
                ->appNotifications()
                ->whereNull('read_at')
                ->count(),
        ]);
    }

    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->markAsRead();

        return response()->json([
            'notification' => $this->serializeNotification($notification->refresh()),
            'unread_count' => $request->user()
                ->appNotifications()
                ->whereNull('read_at')
                ->count(),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()
            ->appNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'unread_count' => 0,
        ]);
    }

    private function serializeNotification(Notification $notification): array
    {
        $data = $notification->data ?? [];

        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'message' => $data['message'] ?? '',
            'link' => $data['link'] ?? route('dashboard'),
            'actor' => $data['actor'] ?? null,
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString(),
            'created_at_human' => $notification->created_at?->diffForHumans(),
            'unread' => is_null($notification->read_at),
        ];
    }
}
