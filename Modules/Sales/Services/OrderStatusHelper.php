<?php

namespace Modules\Sales\Services;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Sales\Repositories\OrderStatusRepository;
use Modules\Sales\Repositories\OrderStatusStateRepository;

class OrderStatusHelper
{
    protected $orderStatusStateRepository;
    protected $orderStatusRepository;

    public function __construct()
    {
        $this->orderStatusRepository = new OrderStatusRepository();
        $this->orderStatusStateRepository = new OrderStatusStateRepository();
    }

    public function getStateLatestOrderStatus(string $order_state_slug, bool $get_all_status = false, ?callable $callback = null): ?object
    {
        try
        {
            $order_state = $this->orderStatusStateRepository->queryFetch(["state" => $order_state_slug], ["order_statuses"]);

            if (!$order_state) {
                throw new ModelNotFoundException();
            }

            if ($callback) {
                $callback($order_state);
            }

            $order_status = $get_all_status ? $order_state->order_statuses : $order_state->order_statuses()->latest()->firstOrFail();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $order_status;
    }

    public function getAllSiblingStatus(string $slug): ?object
    {
        try
        {
            $order_statuses = $this->orderStatusRepository->queryFetch(["slug" => $slug], ["status_siblings"])?->status_siblings;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $order_statuses;
    }
}