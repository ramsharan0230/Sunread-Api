<?php

namespace Modules\EmailTemplate\Tests\Feature;

use Illuminate\Support\Arr;
use Modules\Core\Tests\BaseTestCase;
use Modules\EmailTemplate\Entities\EmailTemplate;

class EmailTemplateTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = EmailTemplate::class;

        parent::setUp();

        $this->admin = $this->createAdmin();
        $this->model_name = "Email Template";
        $this->route_prefix = "admin.email-templates";
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "email_template_code" => null,
            "name" => null,
            "content"=>null
        ]);
    }

    public function getNonMandatoryCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "style" => null
        ]);
    }

    public function testAdminCanFetchTemplateGroupList()
    {
        $response = $this->withHeaders($this->headers)->get($this->getRoute("groups"));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", [ "name" => "Template Group" ])
        ]);
    }

    public function testAdminCanFetchTemplateVariableList()
    {
        $template = Arr::random(config("email_template"));
        $template_code = [
            "email_template_code" => $template["code"]
        ];
        $response = $this->withHeaders($this->headers)->get($this->getRoute("variables", $template_code));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", [ "name" => "Template Variable" ])
        ]);
    }

    public function testAdminCanFetchTemplateContent()
    {
        $response = $this->withHeaders($this->headers)->get($this->getRoute("content", [$this->default_resource_id]));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success", [ "name" => "Email Template" ])
        ]);
    }
}
