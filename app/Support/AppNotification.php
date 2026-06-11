<?php

namespace App\Support;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class AppNotification
{
    public static function send(
        User|int $recipient,
        string $type,
        string $message,
        string $link,
        ?User $actor = null
    ): void {
        $recipientId = $recipient instanceof User ? $recipient->id : $recipient;

        if ($actor && $actor->id === $recipientId) {
            return;
        }

        Notification::create([
            'user_id' => $recipientId,
            'type' => $type,
            'data' => [
                'actor' => $actor ? [
                    'id' => $actor->id,
                    'name' => $actor->name,
                    'username' => $actor->username,
                    'avatar_url' => $actor->avatar_path ? Storage::url($actor->avatar_path) : null,
                ] : null,
                'message' => $message,
                'link' => $link,
            ],
            'created_at' => now(),
        ]);
    }
}
