<?php

namespace Modules\Product\Repositories;

use Illuminate\Support\Facades\Event;
use Exception;
use Illuminate\Support\Arr;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Store;
use Modules\Core\Repositories\BaseRepository;
use Modules\Product\Entities\FeatureTranslation;

class FeatureTranslationRepository extends BaseRepository
{
    public function __construct(FeatureTranslation $featureTranslation)
    {
        $this->model = $featureTranslation;
        $this->model_key = "catalog.features.translations";
    }

    public function updateOrCreate(?array $data, object $parent): void
    {
        Event::dispatch("{$this->model_key}.create.before");

        try
        {
            $translation_data = [];
            foreach ($data as $row) {
                $check = [
                    "store_id" => $row["store_id"],
                    "feature_id" => $parent->id
                ];

                $created = $this->model->firstorNew($check);
                $translation_data[] = $created->fill($row);
                $created->save();
            }
            $parent->translations()->whereNotIn('id', array_filter(Arr::pluck($translation_data, 'id')))->delete();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        if (isset($created)) Event::dispatch("{$this->model_key}.create.after", $created);
    }


    public function show(int $id): array
    {
        $selected_stores = array_unique($this->model->whereFeatureId($id)->pluck('store_id')->toArray());
        $selected_channels = array_unique(Store::whereIn('id', $selected_stores)->pluck('channel_id')->toArray());

        $data = [];
        foreach($selected_channels as $selected_channel)
        {
            $channel = Channel::find($selected_channel);

            $item = [];
            $item = [
                "id" =>  $channel->id,
                "name" => $channel->name
            ];

            foreach($channel->stores as $store)
            {
                if(!isset($store)) continue;
                $feature = $this->model->whereFeatureId($id)->whereStoreId($store->id)->first();

                $item["stores"][] = [
                    "id" => $store->id,
                    "name" => $store->name,
                    "feature_name" => $feature?->name,
                    "feature_description" => $feature?->description
                ];
            }
            $data["data"][] = $item;
        }
        return $data;
    }
}
