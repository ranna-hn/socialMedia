<?php

namespace App\Notifications;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GroupMembershipRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public Group $group)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('econature.notifications.group_rejected_subject', ['group' => $this->group->name]))
            ->greeting(__('econature.notifications.hello', ['name' => $notifiable->name]))
            ->line(__('econature.notifications.group_rejected_line', ['group' => $this->group->name]))
            ->line(__('econature.notifications.thanks'));
    }
}
