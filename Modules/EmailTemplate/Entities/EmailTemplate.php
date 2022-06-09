<?php

namespace Modules\EmailTemplate\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\HasFactory;
use Modules\Core\Traits\Sluggable;

class EmailTemplate extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [ "name", "email_template_code", "subject", "content", "style", "is_system_defined" ];

    public static function models()
    {
        return [
            HeaderTemplate::class,
            FooterTemplate::class,
            ConfirmEmailTemplate::class,
            ContactTemplate::class,
            ForgotPasswordTemplate::class,
            NewAccountTemplate::class,
            NewOrderTemplate::class,
            NewGuestOrderTemplate::class,
            OrderUpdateTemplate::class,
            OrderUpdateGuestTemplate::class,
            ResetPasswordTemplate::class,
            WelcomeTemplate::class,
        ];
    }
}
