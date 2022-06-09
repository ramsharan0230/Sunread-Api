<?php

namespace Modules\Sales\Repositories;

use Exception;
use Modules\Sales\Entities\OrderStatus;
use Modules\Core\Repositories\BaseRepository;
use Modules\Sales\Exceptions\NotAllowedException;
use Modules\Sales\Repositories\StoreFront\OrderRepository;

class OrderStatusUpdateRepository extends BaseRepository
{
    protected $orderRepository;

    public function __construct()
    {
        $this->model = new OrderStatus();
        $this->orderRepository = new OrderRepository();
        $this->model_key = "order_statuses";
        $this->rules = [
            "order_status_id" => "required|exists:order_statuses,id",
            "order_id" => "required|exists:orders,id"
        ];
    }

    public function validateOrderStatusState(int $order_status_id, object $order): object
    {
        try
        {
            $status = $this->fetch($order_status_id);
            $order_state_id = $order->order_status->state_id;
            if ($status->state_id < $order_state_id) {
                throw new NotAllowedException();
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $status;
    }
}
