<?php


namespace Modules\Attribute\Observers;

use Modules\Core\Facades\Audit;
use Modules\Attribute\Entities\AttributeSet;

class AttributeSetObserver
{
    public function created(AttributeSet $attribute_set)
    {
        Audit::log($attribute_set, __FUNCTION__);
    }

    public function updated(AttributeSet $attribute_set)
    {
        Audit::log($attribute_set, __FUNCTION__);
    }

    public function deleted(AttributeSet $attribute_set)
    {
        Audit::log($attribute_set, __FUNCTION__);
    }
}
