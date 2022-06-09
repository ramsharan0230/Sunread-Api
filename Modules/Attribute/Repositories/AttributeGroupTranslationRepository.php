<?php


namespace Modules\Attribute\Repositories;

use Exception;
use Illuminate\Support\Facades\Event;
use Modules\Attribute\Entities\AttributeGroupTranslation;

class AttributeGroupTranslationRepository
{
    protected $model, $model_key;

    public function __construct(AttributeGroupTranslation $attributeGroupTranslation)
    {
        $this->model = $attributeGroupTranslation;
        $this->model_key = "catalog.attribute.attribute_group_translations";
    }

    public function updateOrCreate(?array $data, object $parent): void
    {
        if ( !is_array($data) ) return;

        Event::dispatch("{$this->model_key}.create.before");

        try
        {
            foreach ($data as $row){
                $check = [
                    "store_id" => $row["store_id"],
                    "attribute_group_id" => $parent->id
                ];
    
                $created = $this->model->firstorNew($check);
                $created->fill($row);
                $created->save();
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        Event::dispatch("{$this->model_key}.create.after", $created);
    }
}
