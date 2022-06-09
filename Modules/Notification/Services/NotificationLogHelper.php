<?php

namespace Modules\Notification\Services;

use Modules\Notification\Entities\EmailNotification;
use Exception;

class NotificationLogHelper
{
    private $emailNotification;

    public function __construct(EmailNotification $emailNotification)
    {
        $this->emailNotification = $emailNotification;
    }

    public function log(array $logs, bool $is_sent): void
    {
        try
        {
            $recipient_detail = $this->getRecipientUser();

            array_push($logs,[
                "is_sent" => $is_sent,
                "created_at" => now(),
            ]);

            $this->emailNotification->create(array_merge($logs, $recipient_detail));
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function getRecipientUser(): array
    {
        try
        {
            if (auth()->user()) {
                $user_id = auth()->user()->id;
                $type = "admin";
            } elseif (auth()->guard('customer')->user()) {
                $user_id = auth()->guard('customer')->user()->id;
                $type = "customer";
            } else {
                $user_id = null;
                $type = "guest";
            }

            $data = [
                "recipient_user_type" => $type,
                "recipient_user_id" => $user_id,
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }
}
