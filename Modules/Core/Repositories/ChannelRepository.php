<?php

namespace Modules\Core\Repositories;

use Exception;
use Illuminate\Validation\ValidationException;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Store;
use Modules\Core\Repositories\BaseRepository;
use Modules\Core\Rules\FQDN;

class ChannelRepository extends BaseRepository
{
    protected $store;

    public function __construct(Channel $channel, Store $store)
    {
        $this->model = $channel;
        $this->model_name = "Channel";
        $this->store = $store;
        $this->model_key = "core.channel";
        $this->rules = [
            /* Foreign Keys */
            "default_store_id" => "nullable|exists:stores,id",
            "website_id" => "required|exists:websites,id",

            /* General */
            "code" => "required|unique:channels,code",
            "hostname" => [ "nullable", "unique:websites,hostname", "unique:channels,hostname", new FQDN()],
            "name" => "required",
            "description" => "nullable",
            "status" => "sometimes|boolean"
        ];
        $this->restrict_default_delete = true;
    }

    public function defaultStoreValidation(array $data, int $id): void
    {
        try
        {
            if($this->store->find($data['default_store_id'])->channel->id != $id)
            throw ValidationException::withMessages([ "default_store_id" =>  __("core::app.response.store_does_not_belong", ["name" => $data['name']]) ]);
        }
        catch( Exception $exception )
        {
            throw $exception;
        }     
    }
}
