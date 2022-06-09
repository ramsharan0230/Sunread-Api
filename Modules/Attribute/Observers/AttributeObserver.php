<?php


namespace Modules\Attribute\Observers;

use Modules\Core\Facades\Audit;
use Modules\Attribute\Entities\Attribute;

class AttributeObserver
{
    public function created(Attribute $attribute)
    {
        Audit::log($attribute, __FUNCTION__);
    }

    public function updated(Attribute $attribute)
    {
        Audit::log($attribute, __FUNCTION__);
    }

    public function deleted(Attribute $attribute)
    {
        Audit::log($attribute, __FUNCTION__);
    }
}
