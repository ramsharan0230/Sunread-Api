<?php

namespace Modules\Notification\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\HasFactory;

class EmailNotification extends Model
{
    use HasFactory;

    protected $fillable = [ "name", "subject", "html_content", "recipient_email_address", "recipient_user_type", "recipient_user_id", "email_template_id", "email_template_code", "is_sent" ];
}
