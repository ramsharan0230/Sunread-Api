<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Entities\Channel;
use Modules\Core\Tests\BaseTestCase;

class ChannelTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = Channel::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Channel";
        $this->route_prefix = "admin.channels";
        $this->hasStatusTest = true;
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "code" => null
        ]);
    }

    public function getNonMandatoryUpdateData(): array
    {
        return array_merge($this->getUpdateData(), [
            "description" => null
        ]);
    }
}
