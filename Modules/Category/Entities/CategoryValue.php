<?php

namespace Modules\Category\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\UrlRewrite\Traits\HasUrlRewrite;

class CategoryValue extends Model
{
    use HasUrlRewrite;

    public $timestamps = true;
    protected $fillable = [ "scope", "scope_id", "category_id", "attribute", "value" ];
    protected $casts = [ "value" => "array" ];

    //protected $appends = ['url'];

    public function __construct(?array $attributes = [])
    {
        parent::__construct($attributes);
        $this->urlRewriteRoute = "admin.catalog.categories.show";
        $this->urlRewriteParameter = ["category_id"];
        $this->urlRewriteExtraFields = ["store_id"];
        $this->urlRewriteParameterKey = ["category"];
        $this->urlRewriteType = "Modules\Category\Entities\Category";

    }

    public function getUrlRewriteRequestPathAttribute(): string
    {
       return (isset($this->store->slug) ? $this->store->slug . "/" : "") . $this->name;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
