<?php

namespace Modules\Core\Repositories\Visitors;

use Modules\Core\Entities\Channel;
use Modules\Core\Repositories\BaseRepository;

class ChannelRepository extends BaseRepository
{
    public function __construct(Channel $channel)
    {
        $this->model = $channel;
        $this->model_key = "core.channel";
    }
}