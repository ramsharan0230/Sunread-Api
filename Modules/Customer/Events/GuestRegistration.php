<?php

namespace Modules\Customer\Events;

use Illuminate\Queue\SerializesModels;

class GuestRegistration
{
    use SerializesModels;

    public object $user;
    public string $password;

    public function __construct(
        object $user,
        string $password
    ) {
        $this->user = $user;
        $this->password = $password;
    }

    public function broadcastOn(): array
    {
        return [];
    }
}
