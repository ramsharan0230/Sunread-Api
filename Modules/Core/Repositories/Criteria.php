<?php namespace Modules\Core\Repositories;

use Modules\Core\Contracts\RepositoryInterface;
use Modules\Core\Eloquent\Repository;

abstract class Criteria {

    /**
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public abstract function apply($model, RepositoryInterface $repository);
}
