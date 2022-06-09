<?php

namespace Modules\Cart\Repositories;

use Exception;
use Modules\Cart\Entities\CartItem;
use Modules\Core\Repositories\BaseRepository;


class CartItemRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new CartItem();
        $this->model_key = "cart_items";
    }

    public function deleteCartItem(array $conditions): bool
    {
        try
        {
            $this->model->where($conditions)->delete();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return true;
    }

    public function updateItemWithConditions(array $conditions, array $data): bool
    {
        try
        {
            $this->model::where($conditions)->update($data);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return true;
    }
}
