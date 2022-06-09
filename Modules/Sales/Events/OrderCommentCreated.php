<?php

namespace Modules\Sales\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCommentCreated
{
    use Dispatchable;
    use SerializesModels;
    use InteractsWithSockets;

    public object $comment;

    public function __construct(object $comment)
    {
        $this->comment = $comment;
    }

    public function broadcastOn(): array
    {
        return [];
    }
}
