<?php

namespace App\Notifications;

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GroupInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Group $group,
        public User $inviter,
        public GroupUser $membership,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('econature.notifications.group_invitation_subject', ['group' => $this->group->name]))
            ->greeting(__('econature.notifications.hello', ['name' => $notifiable->name]))
            ->line(__('econature.notifications.group_invitation_line', [
                'inviter' => $this->inviter->name,
                'group' => $this->group->name,
            ]))
            ->action(__('econature.notifications.view_group'), route('groups.show', $this->group))
            ->line(__('econature.notifications.thanks'));
    }
}
