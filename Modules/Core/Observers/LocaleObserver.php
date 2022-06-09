<?php


namespace Modules\Core\Observers;

use Modules\Core\Entities\Locale;
use Modules\Core\Facades\Audit;

class LocaleObserver
{
    public function created(Locale $locale)
    {
        Audit::log($locale, __FUNCTION__);
    }

    public function updated(Locale $locale)
    {
        Audit::log($locale, __FUNCTION__);
    }

    public function deleted(Locale $locale)
    {
        Audit::log($locale, __FUNCTION__);
    }
}
