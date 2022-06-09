<?php

namespace Modules\Review\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\Review\Entities\Review;

class ReviewTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = Review::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Review";
        $this->route_prefix = "admin.reviews";
        $this->hasStatusTest = true;
    }

    public function getNonMandatoryCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "description" => null
        ]);
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "rating" => null
        ]);
    }

    public function getNonMandatoryUpdateData(): array
    {
        return array_merge($this->getUpdateData(), [
            "title" => null
        ]);
    }

    public function getInvalidUpdateData(): array
    {
        return array_merge($this->getUpdateData(), [
            "customer_id" => null
        ]);
    }

    /**
     * Tests
     */

     public function testAdminCanVerifyResource()
    {
        $response = $this->withHeaders($this->headers)->get($this->getRoute("verify", [$this->default_resource_id]));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => "Review verified successfully."
        ]);
    }

    public function testAdminCanFetchPendingResources()
    {
        $this->model::factory($this->factory_count)->create();
        $response = $this->withHeaders($this->headers)->get($this->getRoute("pending"));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => "Pending review list fetched successfully."
        ]);
    }
}
