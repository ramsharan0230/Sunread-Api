<?php

namespace Modules\Product\Jobs;

use Elasticsearch\ClientBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Product\Traits\ElasticSearch\AttributeOptionHandler;

class PartialModifyAttributeOption implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AttributeOptionHandler;

    public $product_attributes, $attribute, $attribute_option, $method;

    public function __construct(object $product_attributes, object $attribute, object $attribute_option, string $method)
    {
        $this->product_attributes = $product_attributes;
        $this->attribute = $attribute;
        $this->attribute_option = $attribute_option;
        $this->method = $method;
    }

    public function handle(): void
    {
        $this->product_attributes->map(function ($product_attribute) {
            $product = $product_attribute->product;
            $this->handlePartialModify($product, $this->attribute, $this->attribute_option, $this->method);       
        });
    }
}
