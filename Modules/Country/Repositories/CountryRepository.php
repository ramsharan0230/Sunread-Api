<?php

namespace Modules\Country\Repositories;

use Modules\Core\Facades\SiteConfig;
use Modules\Core\Repositories\BaseRepository;
use Modules\Country\Entities\Country;
use Exception;

class CountryRepository extends BaseRepository
{
    public function __construct(Country $country)
    {
        $this->model = $country;
        $this->model_key = "country";
    }

    public function getChannelCountry(int $channel_id): object
    {
        try
        {
            $allow = SiteConfig::fetch("allow_countries", "channel", $channel_id);
            $default[] = SiteConfig::fetch("default_country", "channel", $channel_id);

            $fetched = $allow->merge($default);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }
}
