<?php


namespace Modules\Product\Repositories;


use Modules\Core\Eloquent\Repository;
use Modules\Product\Entities\ProductFlat;

class ProductFlatRepository extends Repository
{

    /**
     * @var \Closure
     */
    protected $scopeQuery = null;

    public function model()
    {
        return ProductFlat::class;
    }

    /**
     * Query Scope
     *
     * @param \Closure $scope
     *
     * @return $this
     */
    public function scopeQuery(\Closure $scope)
    {
        $this->scopeQuery = $scope;

        return $this;
    }

}
