<?php

namespace Modules\Review\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Review\Entities\ReviewVote;

class ReviewVoteRepository extends BaseRepository
{
    public function __construct(ReviewVote $review_vote)
    {
        $this->model = $review_vote;
        $this->model_key = "review.review_votes";
        $this->rules = [
            /* Foreign Keys */
            "customer_id" => "required|exists:customers,id",
            "review_id" => "required|exists:reviews,id",

             /* General */
            "vote_type" => "required|boolean",
        ];
    }

}
