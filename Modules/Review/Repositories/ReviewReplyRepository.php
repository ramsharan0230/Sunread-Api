<?php

namespace Modules\Review\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Review\Entities\ReviewReply;

class ReviewReplyRepository extends BaseRepository
{
    public function __construct(ReviewReply $review_reply)
    {
        $this->model = $review_reply;
        $this->model_key = "review.review_replies";
        $this->rules = [
            /* Foreign Keys */
            "review_id" => "required|exists:reviews,id",

            /* General */
            "description" => "required",
        ];
    }

}
