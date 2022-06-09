<?php

namespace Modules\PaymentKlarna\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Modules\Sales\Entities\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Queue\SerializesModels;
use Modules\Sales\Events\OrderCreated;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Sales\Traits\HasCheckoutUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\PaymentKlarna\Traits\HasKlarnaOrderMigrator;
use Modules\Sales\Repositories\StoreFront\OrderRepository;

class KlarnaOrderPushJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use HasKlarnaOrderMigrator;
    use HasCheckoutUser;

    public $klarna_order;
    public object $klarna_response;
    public $orderRepository;

    public function __construct(object $klarna_order, object $klarna_response)
    {
        $this->klarna_order = $klarna_order;
        $this->klarna_response = $klarna_response;
        $this->orderRepository = new OrderRepository();
    }

    public function handle(): void
    {
        DB::beginTransaction();
        try
        {
            $orderData = $this->orderData();
            $match = [
                "cart_id" => $this->klarna_order->cart_id,
                "website_id" => $this->klarna_order->website_id,
            ];
            $order = Order::updateOrCreate($match, $orderData);

            $this->createOrderItem($order);
            $this->createOrderAddress($order);

            $this->createOrderTaxes($order);
            $this->updateFinalOrderCalculations($order);
            $this->updateKlarnaOrder($order);
            $this->createGuestUser($order);
            $order = $this->orderRepository->fetch($order->id);
            $log_data = [
                "causer_type" => self::class,
                "order_id" => $order->id,
                "title" => "klarna",
                "data" => $orderData,
                "action" => "order.createOrUpdate",
            ];
            $transaction_log = [
                "order" => $order,
                "request" => $orderData,
                "response" => $this->klarna_response,
            ];
            event(new OrderCreated($order));
            Event::dispatch("order.create.update.after", $log_data);
            Event::dispatch("order.transaction.create.update.after", $transaction_log);
        }
        catch ( Exception $exception )
        {
            DB::rollback();
            throw $exception;
        }

        DB::commit();
    }
}
