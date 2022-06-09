<?php

namespace Modules\Product\Traits\ElasticSearch;

use Exception;

trait AttributeOptionHandler
{
    use HasIndexing;

    public function handlePartialModify(object $product, object $attribute, object $attribute_option, $method): void
    {
        try
        {
            $stores = $this->getStores($product);
            foreach($stores as $store)
            {
                $match = [
                    "scope" => "store",
                    "scope_id" => $store->id,
                    "attribute_id" => $attribute->id
                ];
                $value = $product->value($match);

                ($method == "update")
                ? $this->handleUpdate($value, $attribute_option, $attribute, $product, $store)
                : $this->handleDelete($value, $attribute_option, $attribute, $product, $store);
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function handleUpdate(mixed $value, object $attribute_option, object $attribute, object $product, object $store): void
    {
        try
        {
            $slug_values = [];

            if (is_array($value)) {
                if(in_array($attribute_option->id, $value)) {
                    $key = array_keys($value, $attribute_option->id);
                    $slug_values["{$attribute->slug}_{$key[0]}_value"] = $attribute_option->name;
                }
            }
            else $slug_values["{$attribute->slug}_value"] = $attribute_option->name;

            if(count($slug_values) > 0) $this->partialIndexing($product, $store, $slug_values);
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function handleDelete(mixed $value, object $attribute_option, object $attribute, object $product, object $store): void
    {
        // try
        // {
        //     $slug_values = [];
        //     $field_values = [];
        //     $r_ctx = "ctx._source.remove";
        //     $a_ctx = "ctx._source.tags.add(params";

        //     if (is_array($value)) {
        //         $j = 0;
        //         foreach($value as $i => $val)
        //         {
        //             if($attribute_option["id"] != $val)
        //             {
        //                 $add_values[] = "$a_ctx.{$attribute->slug}_{$j}_value)";
        //                 $params["params"][] = [ "{$attribute->slug}_{$j}_value" => $attribute_option["name"] ];
        //                 $final_value[] = $val;
        //                 $j++;
        //             }
        //             $field_values[] = "$r_ctx('{$attribute->slug}_{$i}_value')";
        //         }
        //         $field_values[] = "$r_ctx('{$attribute->slug}')";
        //         $add_values[] = "$a_ctx.{$attribute->slug})";
        //         $params["params"][] = [ $attribute->slug => $final_value ];
        //     }
        //     else $field_values = [ "$r_ctx('{$attribute->slug}_value')", "$r_ctx('{$attribute->slug}')" ];

        //     $this->partialRemoving($product, $store, $slug_values, $field_values);
        // }
        // catch(Exceptions $exception)
        // {
        //     throw $exception;
        // }
    }
}
