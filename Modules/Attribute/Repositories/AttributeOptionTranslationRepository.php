<?php


namespace Modules\Attribute\Repositories;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Modules\Attribute\Entities\AttributeOptionTranslation;

class AttributeOptionTranslationRepository
{
    protected $model, $model_key;

    public function __construct(AttributeOptionTranslation $attribute_option_translation)
    {
        $this->model = $attribute_option_translation;
        $this->model_key = "catalog.attribute.options.translations";
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
                    "attribute_option_id" => $parent->id
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
}
