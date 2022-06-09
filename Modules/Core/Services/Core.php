<?php


namespace Modules\Core\Services;

use Illuminate\Support\Collection;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Locale;
use Modules\Core\Repositories\ChannelRepository;

class Core
{
    /** @var Channel */
    private static $channel;


    /**
     * Create a new instance.
     *
     *
     * @param ChannelRepository $channelRepository
     */
    public function __construct(ChannelRepository $channelRepository){
        $this->channelRepository = $channelRepository;
    }
    public static function getRelatedLocales($data):Collection
    {
        if (isset($data['locale']) && is_array($data['locale'])){
            return  Locale::where('code',$data['locale'])->get();
        }
        return Locale::all();
    }

    /**
     * Returns currenct channel models
     *
     * @return Channel
     */
    public function getCurrentChannel()
    {
        if (self::$channel) {
            return self::$channel;
        }

        self::$channel = $this->channelRepository->findWhereIn('hostname', [
            request()->getHttpHost(),
            'http://' . request()->getHttpHost(),
            'https://' . request()->getHttpHost(),
        ])->first();
        if (! self::$channel) {
            self::$channel = $this->channelRepository->first();
        }

        return self::$channel;
    }

    /**
     * Set the current channel
     *
     * @param Channel $channel
     */
    public function setCurrentChannel(Channel $channel): void
    {
        self::$channel = $channel;
    }


    /**
     * Returns currenct channel code
     *
     * @return \Webkul\Core\Contracts\Channel
     */
    public function getCurrentChannelCode(): string
    {
        static $channelCode;

        if ($channelCode) {
            return $channelCode;
        }

        return ($channel = $this->getCurrentChannel()) ? $channelCode = $channel->code : '';
    }

    /**
     * Returns default channel models
     *
     */
    public function getDefaultChannel(): ?Channel
    {
        static $channel;

        if ($channel) {
            return $channel;
        }

        $channel = $this->channelRepository->findOneByField('code', config('app.channel'));

        if ($channel) {
            return $channel;
        }

        return $channel = $this->channelRepository->first();
    }

    /**
     * Returns the default channel code configured in config/app.php
     *
     * @return string
     */
    public function getDefaultChannelCode(): string
    {
        static $channelCode;

        if ($channelCode) {
            return $channelCode;
        }

        return ($channel = $this->getDefaultChannel()) ? $channelCode = $channel->code : '';
    }

    /**
     * Returns all locales
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllLocales()
    {
        static $locales;

        if ($locales) {
            return $locales;
        }

        return $locales = $this->localeRepository->all();
    }
}
