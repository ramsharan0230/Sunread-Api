<?php

namespace Modules\Core\Traits;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait Transformable
{
    public $transformer;

    public function collection(object $resource): ResourceCollection
    {
        return $this->transformer->collection($resource);
    }

    public function resource(object $resource): JsonResource
    {
        return $this->transformer->make($resource);
    }
}
