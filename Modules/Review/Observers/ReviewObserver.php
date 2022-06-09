<?php


namespace Modules\Review\Observers;

use Modules\Core\Facades\Audit;
use Modules\Review\Entities\Review;

class ReviewObserver
{
    public function created(Review $review)
    {
        Audit::log($review, __FUNCTION__);
    }

    public function updated(Review $review)
    {
        Audit::log($review, __FUNCTION__);
    }

    public function deleted(Review $review)
    {
        Audit::log($review, __FUNCTION__);
    }
}
