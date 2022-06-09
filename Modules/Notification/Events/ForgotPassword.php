<?php

namespace Modules\Notification\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ForgotPassword
{
    use Dispatchable, SerializesModels, InteractsWithSockets;

    public object $user;
    public string $token;

    public function __construct(
        object $user,
        string $token
    ) {
        $this->user = $user;
        $this->token = $token;
    }

    public function broadcastOn(): array
    {
        return [];
    }
}
