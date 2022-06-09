<?php
namespace Modules\Review\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Customer\Entities\Customer;
use Modules\Review\Entities\Review;

class ReviewVoteFactory extends Factory
{
    protected $model = \Modules\Review\Entities\ReviewVote::class;

    public function definition(): array
    {
        $customer = Customer::latest("id")->first();
        $review = Review::factory()->create();

        return [
            "customer_id" => $customer->id,
            "review_id" => $review->id,
            "vote_type" => rand(0,1)
        ];
    }
}

