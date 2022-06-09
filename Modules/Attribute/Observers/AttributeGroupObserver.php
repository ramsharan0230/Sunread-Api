<?php


namespace Modules\Attribute\Observers;

use Modules\Core\Facades\Audit;
use Modules\Attribute\Entities\AttributeGroup;

class AttributeGroupObserver
{
    public function created(AttributeGroup $attribute_group)
    {
        Audit::log($attribute_group, __FUNCTION__);
    }

    public function updated(AttributeGroup $attribute_group)
    {
        Audit::log($attribute_group, __FUNCTION__);
    }

    public function deleted(AttributeGroup $attribute_group)
    {
        Audit::log($attribute_group, __FUNCTION__);
    }
}
