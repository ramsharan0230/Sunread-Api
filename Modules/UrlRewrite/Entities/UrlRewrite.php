<?php

namespace Modules\UrlRewrite\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\HasFactory;

class UrlRewrite extends Model
{
    use HasFactory;

    protected $fillable = ["type", "type_attributes", "request_path", "target_path", "redirect_type"];

    protected $casts = ["type_attributes" => "array"];

    public const FORWARD = 0;

    public const PERMANENT = 1;

    public const TEMPORARY = 2;

    public function __construct(?array $attributes = [])
    {   
        parent::__construct($attributes);
    }

    public function isForward(): bool
    {
        return $this->redirect_type === static::FORWARD;
    }

    public function isRedirect(): bool
    {
        return $this->redirect_type !== static::FORWARD;
    }

    public function getRedirectType(): int
    {
        return $this->redirect_type === static::PERMANENT ? 301 : 302;
    }

    public function getByTypeAndAttributes(string $type, array $attributes)
    {
        $query = $this->where('type', $type);

        if(isset($attributes['parameter']))
        {
            foreach ($attributes['parameter'] as $key => $attribute) {
                $query = $query->where("type_attributes->parameter->$key", (string) $attribute);
            }
        }

        if(isset($attributes['extra_fields']))
        {
            foreach ($attributes['extra_fields'] as $key => $attribute) {
                $query = $query->where("type_attributes->extra_fields->$key", (string) $attribute);
            }
        }

        return $query;
    }

    public static function getPossibleTypesArray(): array
    {
        $array = [];

        foreach (array_keys(config('url-rewrite.types')) as $type) {
            $array[$type] = $type;
        }

        return $array;
    }
    
}
