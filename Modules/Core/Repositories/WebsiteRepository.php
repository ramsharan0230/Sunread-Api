<?php

namespace Modules\Core\Repositories;

use Modules\Core\Entities\Website;
use Modules\Core\Repositories\BaseRepository;
use Modules\Core\Rules\FQDN;

class WebsiteRepository extends BaseRepository
{
    public function __construct(Website $website)
    {
        $this->model = $website;
        $this->model_name = "Website";
        $this->model_key = "core.website";
        $this->relationships = ["channels.default_store", "channels.stores"];
        $this->rules = [
            /* General */
            "code" => "required|unique:websites,code",
            "hostname" => [ "required", "unique:websites,hostname", "unique:channels,hostname", new FQDN()],
            "name" => "required",
            "description" => "nullable",
            "position" => "sometimes|numeric",
            "status" => "sometimes|boolean",
            "default_channel_id" => "nullable|exists:channels,id"
        ];

        $this->restrict_default_delete = true;
    }
}
