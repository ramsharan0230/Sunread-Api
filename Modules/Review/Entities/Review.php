<?php

namespace Modules\Review\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Traits\HasFactory;
use Modules\Customer\Entities\Customer;
use Modules\Product\Entities\Product;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [ "customer_id", "product_id", "rating", "title", "description", "status" ];

    protected $appends = ["positive_vote_count", "negative_vote_count", "visible_vote_count"];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function review_votes(): HasMany
    {
        return $this->hasMany(ReviewVote::class);
    }

    public function review_replies(): HasMany
    {
        return $this->hasMany(ReviewReply::class);
    }

    public function getPositiveVoteCountAttribute()
    {
        return Cache::rememberForever("positive_vote_count-".$this->id, function(){
            return $this->review_votes->where("vote_type", 0)->count();
        });
    }

    public function getNegativeVoteCountAttribute()
    {
        return Cache::rememberForever("negative_vote_count-".$this->id, function(){
            return $this->review_votes->where("vote_type", 1)->count();
        });
    }

    public function getVisibleVoteCountAttribute()
    {
        return $this->positive_vote_count - $this->negative_vote_count;
    }
}