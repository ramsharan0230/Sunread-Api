<?php

namespace Modules\Product\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\Resource;
use Modules\Attribute\Entities\Attribute;


class ProductResource extends Resource
{
    /**
     * Create a new resource instance.
     *
     * @return void
     */
    public function __construct($resource)
    {
        // $this->productImageHelper = app('Webkul\Product\Helpers\ProductImage');

        // $this->productReviewHelper = app('Webkul\Product\Helpers\Review');

        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $product = $this->product ? $this->product : $this;

        return [
            'id' => $product->id,
            'type' => $product->type,
            'name' => $this->name,
            "slug" => $product->slug,
            'price' => $product->getTypeInstance()->getMinimalPrice(),
            'short_description' => $this->short_description,
            'description' => $this->description,
            'sku' => $this->sku,
            'variants' => Self::collection($this->variants),
            'special_price' => $this->when(
                $product->getTypeInstance()->haveSpecialPrice(),
                $product->getTypeInstance()->getSpecialPrice()
            ),
            "special_price_from" => $this->special_price_from,
            "special_price_to" => $this->special_price_to,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            "new" => $this->new,
            "featured" => $this->featured,
            "status" => $this->status,
            "cost" => $this->cost,
            "weight" => $this->weight,
            "color_label" => $this->color_label,
            "size_label" => $this->size_label,
            "locale" => $this->locale,
            "parent_id" => $this->parent_id,
            "meta_title" => $this->meta_title,
            "meta_keywords" => $this->meta_keywords,
            "meta_description" => $this->descripion,
            "width" => $this->width,
            "height" => $this->heigt,
            "depth" => $this->depth
        ];
    }
}
