<?php

namespace Modules\Core\Repositories;

use Modules\Core\Eloquent\Repository;
use Modules\Core\Entities\Locale;

class LocaleRepository extends Repository
{
    /**
     *
     * @return mixed
     */
    function model()
    {
        return Locale::class;
    }



}
