<?php

namespace Modules\EmailTemplate\Observers;

use Modules\Core\Facades\Audit;
use Modules\EmailTemplate\Entities\EmailTemplate;

class EmailTemplateObservers
{
    public function created(EmailTemplate $email_template)
    {
        Audit::log($email_template, __FUNCTION__);
    }

    public function updated(EmailTemplate $email_template)
    {
        Audit::log($email_template, __FUNCTION__);
    }

    public function deleted(EmailTemplate $email_template)
    {
        Audit::log($email_template, __FUNCTION__);
    }
}
