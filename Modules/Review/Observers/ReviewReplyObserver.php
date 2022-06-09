<?php


namespace Modules\Review\Observers;

use Modules\Core\Facades\Audit;
use Modules\Review\Entities\ReviewReply;

class ReviewReplyObserver
{
    public function created(ReviewReply $review_reply)
    {
        Audit::log($review_reply, __FUNCTION__);
    }

    public function updated(ReviewReply $review_reply)
    {
        Audit::log($review_reply, __FUNCTION__);
    }

    public function deleted(ReviewReply $review_reply)
    {
        Audit::log($review_reply, __FUNCTION__);
    }
}
