<?php


namespace Modules\Attribute\Repositories;

use Exception;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Modules\Attribute\Entities\AttributeTranslation;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Store;

class AttributeTranslationRepository
{
    protected $model, $model_key;

    public function __construct(AttributeTranslation $attribute_translation)
    {
        $this->model = $attribute_translation;
        $this->model_key = "catalog.attribute.translations";
    }

    public function updateOrCreate(?array $data, object $parent): void
    {
        Event::dispatch("{$this->model_key}.create.before");

        try
        {
            $translation_data = [];
            foreach ($data as $row){

                $check = [
                    "store_id" => $row["store_id"],
                    "attribute_id" => $parent->id
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
        $selected_stores = array_unique($this->model->whereAttributeId($id)->pluck('store_id')->toArray());
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

                $item["stores"][] = [
                   "id" => $store->id,
                   "name" => $store->name,
                   "value" => $this->model->whereAttributeId($id)->whereStoreId($store->id)->first()->name ?? null
                ];
            }
            $data["data"][] = $item;
        }
        $data["selected_channels"] = $selected_channels;
        return $data;
    }
}
