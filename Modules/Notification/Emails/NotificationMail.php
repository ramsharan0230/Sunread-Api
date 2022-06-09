<?php

namespace Modules\Notification\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details, $subject, $style, $sender_name, $sender_email;

    public function __construct(string $details, string $subject, string $sender_name, string $sender_email, string $style = null)
    {
        $this->details = $details;
        $this->subject = $subject;
        $this->style = $style;
        $this->sender_name = $sender_name;
        $this->sender_email = $sender_email;
    }

    public function getBody(): string
    {
        return $this->details;
    }

    public function build(): NotificationMail
    {
        return $this->subject($this->subject)
            ->from($this->sender_email, $this->sender_name)
            ->markdown('notification::emailNotification');
    }
}
