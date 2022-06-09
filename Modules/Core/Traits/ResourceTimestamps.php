<?php

namespace Modules\Core\Traits;

trait ResourceTimestamps
{
    public function timestanps(): array
    {
        return [
            "created_at" => $this->created_at?->format("M d, Y H:i A"),
            "updated_at" => $this->updated_at?->format("M d, Y H:i A"),
            "created_at_human" => $this->created_at?->diffForHumans(),
            "updated_at_human" => $this->updated_at?->diffForHumans(),
        ];
    }

    public function convert(array $resource): array
    {
        return array_merge($resource, $this->timestanps());
    }
}
