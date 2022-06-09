<?php

namespace Modules\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class InvitationNotification extends Notification
{
    use Queueable;
    private $invitation_token;
    private $role;

    public function __construct($invitation_token, $role)
    {
        $this->invitation_token = $invitation_token;
        $this->role = $role;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invitation')
            ->line('You are receiving this email because you are invited to '.config('name','Sunread').' for the '.$this->role. ' role.')
            ->action('Accept Invitation', route('admin.invitation-info', $this->invitation_token));
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
