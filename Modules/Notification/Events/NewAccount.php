<?php

namespace Modules\Notification\Events;

use Illuminate\Queue\SerializesModels;

class NewAccount
{
    use SerializesModels;

    public object $user;

    public function __construct(object $user)
    {
        $this->user = $user;
    }

    public function broadcastOn(): array
    {
        return [];
    }
}
