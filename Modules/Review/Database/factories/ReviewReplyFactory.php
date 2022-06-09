<?php
namespace Modules\Review\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Review\Entities\Review;

class ReviewReplyFactory extends Factory
{
    protected $model = \Modules\Review\Entities\ReviewReply::class;

    public function definition(): array
    {
        $review = Review::latest("id")->first();

        return [
            "review_id" => $review->id,
            "description" => $this->faker->paragraph()
        ];
    }
}

