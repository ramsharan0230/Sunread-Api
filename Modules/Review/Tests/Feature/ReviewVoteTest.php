<?php

namespace Modules\Review\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\Review\Entities\ReviewVote;

class ReviewVoteTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = ReviewVote::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Review Vote";
        $this->route_prefix = "admin.review_votes";
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "vote_type" => null
        ]);
    }

    public function getInvalidUpdateData(): array
    {
        return array_merge($this->getUpdateData(), [
            "customer_id" => null
        ]);
    }
}
