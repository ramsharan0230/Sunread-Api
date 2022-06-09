<?php

namespace Modules\Notification\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResetPassword
{
    use Dispatchable, SerializesModels, InteractsWithSockets;

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
