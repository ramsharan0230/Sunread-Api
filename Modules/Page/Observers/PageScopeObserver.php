<?php

namespace Modules\Page\Observers;

use Modules\Core\Facades\Audit;
use Modules\Page\Entities\Page;

class PageScopeObserver
{
    public function created(Page $page)
    {
        Audit::log($page, __FUNCTION__);
    }

    public function updated(Page $page)
    {
        Audit::log($page, __FUNCTION__);
    }

    public function deleted(Page $page)
    {
        Audit::log($page, __FUNCTION__);
    }
}
