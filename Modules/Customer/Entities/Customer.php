<?php

namespace Modules\Customer\Entities;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Entities\Store;
use Modules\Core\Entities\Website;
use Modules\Customer\Notifications\CustomerResetPassword;
use Modules\Notification\Events\ForgotPassword;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Modules\Core\Traits\HasFactory;

class Customer extends Authenticatable implements  JWTSubject
{
    use Notifiable, HasFactory;

    public static $SEARCHABLE =  [ "first_name", "last_name", "email" ];
    protected $fillable = [ "first_name", "middle_name", "last_name", "gender", "date_of_birth", "email", "tax_number", "password", "customer_group_id", "subscribed_to_news_letter", "status" , "profile_image", "website_id", "store_id", "is_lock", "last_login_at", "account_type", "verification_token", "is_email_verified", "phone" ];
    protected $hidden = [ "password", "api_token", "remember_token" ];
    protected $dates = [ 'last_login_at' ];

    public function getJWTIdentifier(): ?string
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getNameAttribute(): ?string
    {
        return ucwords(preg_replace('/\s+/', ' ', "{$this->first_name} {$this->middle_name} {$this->last_name}"));
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        return $this->profile_image ? Storage::url($this->profile_image) : null;
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, "customer_group_id");
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class ,"customer_id");
    }

    public function sendPasswordResetNotification($token): void
    {
        event(new ForgotPassword($this, $token));
    }

    public function getDefaultBillingAddressAttribute(): ?object
    {
        return $this->addresses->where("default_billing_address", 1)->first();
    }

    public function getDefaultShippingAddressAttribute(): ?object
    {
        return $this->addresses->where("default_shipping_address", 1)->first();
    }

    public function getCustomerTypeAttribute(): string
    {
        switch ($this->account_type) {
            case "company": 
                $type = "organization";
            break;

            case "personal": 
                $type = "person";
            break;

            default: $type = "person";
        }
        return $type;
    }
}
