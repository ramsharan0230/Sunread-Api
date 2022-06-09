<?php

namespace Modules\Sales\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Sales\Entities\OrderComment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class OrderCommentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $comment;

    public function __construct(array $comment)
    {
        $this->comment = $comment;
    }

    public function handle(): void
    {
        try
        {
            OrderComment::create([
                "order_id" => $this->comment['order_id'],
                "user_id" => $this->comment['user_id'],
                "is_visible_on_storefornt" => $this->comment['is_visible_on_storefornt'],
                "comment" => $this->comment['comment']
            ]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
