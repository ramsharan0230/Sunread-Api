<?php

namespace Modules\Review\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Review\Entities\Review;

class ReviewRepository extends BaseRepository
{
    public function __construct(Review $review)
    {
        $this->model = $review;
        $this->model_key = "review.reviews";
        $this->rules = [
            /* Foreign Keys */
            "customer_id" => "required|exists:customers,id",
            "product_id" => "required|exists:products,id",

            /* General */
            "rating" => "required|numeric|min:1|max:5",
            "title" => "sometimes|nullable",
            "description" => "sometimes|nullable",
            "status" => "sometimes|boolean",
        ];
    }

}
