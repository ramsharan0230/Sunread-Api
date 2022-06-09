<?php

namespace Modules\Attribute\Repositories;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Core\Repositories\BaseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Modules\Attribute\Entities\AttributeOptionTranslation;
use Modules\Attribute\Jobs\ProductRemoveOnAttributeOptionDeletion;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Store;
use Modules\Product\Entities\AttributeOptionsChildProduct;
use Modules\Product\Repositories\AttributeOptionsChildProductRepository;

class AttributeOptionRepository extends BaseRepository
{
    protected $translation;
    protected $translation_model;
    protected $attributeOptionsChildProductRepository;

    public function __construct(
        AttributeOption $attribute_option, 
        AttributeOptionTranslation $attribute_option_translation, 
        AttributeOptionTranslationRepository $attributeOptionTranslationRepository,
        AttributeOptionsChildProductRepository $attributeOptionsChildProductRepository
    ) {
        $this->model = $attribute_option;
        $this->translation = $attributeOptionTranslationRepository;
        $this->translation_model = $attribute_option_translation;
        $this->attributeOptionsChildProductRepository = $attributeOptionsChildProductRepository;
        $this->model_key = "catalog.attribute.options";
        $this->rules = [
            "name" => "required",
            "position" => "sometimes|numeric",
            "code" => "nullable",
            "translations" => "nullable|array"
        ];
    }

    public function updateOrCreate(?array $data, object $parent, $method=null): void
    {
        if ( count($data) == 0 && $parent->checkOption()) return;

        DB::beginTransaction();
        Event::dispatch("{$this->model_key}.create.before");
        $items = [];
        try
        {
            if ($method == "update") {
                $batch = Bus::batch([])->onQueue("index")->dispatch();
                $parent->attribute_options()->with(["attribute"])->whereNotIn('id', array_filter(Arr::pluck($data, 'id')))->get()->map( function ($attribute_option) use ($batch) {
                    $products = $this->attributeOptionsChildProductRepository->query(function ($query) use($attribute_option) {
                        return $query->where(["attribute_option_id" => $attribute_option->id])->get();
                    })->pluck("product_id")->toArray();
                    $attribute_option->delete();

                    $batch->add(new ProductRemoveOnAttributeOptionDeletion($products));
                });
            }

            foreach ($data as $row){
                $this->validateData(new Request($row), isset($row["id"]) ? [
                    "id" => "exists:attribute_options,id"
                ] : []);

                $row['attribute_id'] = $parent->id;
                $created = !isset($row["id"]) ? $this->create($row) : $this->update($row, $row["id"]);

                $this->translation->updateOrCreate($row["translations"], $created);
                $items[] = $created;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        Event::dispatch("{$this->model_key}.create.after", $items);
        DB::commit();
    }

    public function show(int $id): array
    {
        $input = [];
        $attribute_options = $this->model->whereAttributeId($id)->get();

        if($attribute_options->count() == 0) return $input;

        foreach($attribute_options as $attribute_option)
        {
            $data = [];
            $data = [
                "id" => $attribute_option->id,
                "name" => $attribute_option->name,
                "position" => $attribute_option->position,
                "is_default" => $attribute_option->is_default,
                "code" => $attribute_option->code
            ];
            $selected_stores = array_unique($this->translation_model->whereAttributeOptionId($attribute_option->id)->pluck('store_id')->toArray());
            $selected_channels = array_unique(Store::whereIn('id', $selected_stores)->pluck('channel_id')->toArray());
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
                       "value" => $this->translation_model->whereAttributeOptionId($attribute_option->id)->whereStoreId($store->id)->first()->name ?? null
                    ];
                }
                $data["translations"]["data"][] = $item;
            }
            if(count($selected_channels)>0) $data["translations"]["selected_channels"] = $selected_channels;
            $input[] = $data;
        }
        return $input;
    }
}
