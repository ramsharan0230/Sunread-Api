<?php

namespace Modules\Review\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\Review\Entities\ReviewReply;

class ReviewReplyTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = ReviewReply::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Review Reply";
        $this->route_prefix = "admin.review_replies";
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "review_id" => null
        ]);
    }

    public function getInvalidUpdateData(): array
    {
        return array_merge($this->getUpdateData(), [
            "description" => null
        ]);
    }
}
