<?php


namespace Modules\Review\Observers;

use Modules\Core\Facades\Audit;
use Modules\Review\Entities\ReviewVote;

class ReviewVoteObserver
{
    public function created(ReviewVote $review_vote)
    {
        Audit::log($review_vote, __FUNCTION__);
    }

    public function updated(ReviewVote $review_vote)
    {
        Audit::log($review_vote, __FUNCTION__);
    }

    public function deleted(ReviewVote $review_vote)
    {
        Audit::log($review_vote, __FUNCTION__);
    }
}
