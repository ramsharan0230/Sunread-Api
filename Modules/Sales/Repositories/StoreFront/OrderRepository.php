<?php

namespace Modules\Sales\Repositories\StoreFront;

use Exception;
use Modules\GeoIp\Facades\GeoIp;
use Modules\Sales\Entities\Order;
use Illuminate\Support\Facades\DB;
use Modules\Core\Facades\SiteConfig;
use Illuminate\Support\Facades\Event;
use Modules\Core\Facades\PriceFormat;
use Modules\Tax\Facades\NewTaxPrices;
use Modules\Sales\Facades\OrderStatusHelper;
use Modules\Core\Repositories\BaseRepository;
use Modules\Sales\Rules\MethodValidationRule;
use Modules\Sales\Traits\HasOrderCalculation;
use Modules\Cart\Services\UserAuthCheckService;
use Modules\Sales\Traits\HasOrderProductDetail;
use Modules\Cart\Repositories\CartItemRepository;
use Modules\Inventory\Jobs\LogCatalogInventoryItem;
use Modules\Sales\Repositories\OrderMetaRepository;
use Modules\Sales\Repositories\OrderAddressRepository;
use Modules\CheckOutMethods\Services\BaseCheckOutMethods;
use Modules\PaymentAdyen\Exceptions\OrderNotFoundException;
use Modules\CheckOutMethods\Services\CheckOutProcessResolver;
use Modules\Country\Repositories\RegionRepository;
use Modules\Product\Repositories\ProductBaseRepository;
use Modules\Sales\Repositories\StoreFront\OrderItemRepository;

class OrderRepository extends BaseRepository
{
    use HasOrderProductDetail;
    use HasOrderCalculation;

    public array $relations = [
        "order_items",
        "order_items.order",
        "order_taxes.order_tax_items",
        "website",
        "billing_address",
        "shipping_address",
        "customer",
        "order_status.order_status_state",
        "order_metas"
    ];

    public $orderItemRepository;
    public $orderAddressRepository;
    public $cartItemRepository;
    public $productRepository;
    public $regionRepository;

    public function __construct()
    {
        $this->model = new Order();
        $this->orderItemRepository = new OrderItemRepository();
        $this->orderAddressRepository = new OrderAddressRepository();
        $this->orderMetaRepository = new OrderMetaRepository();
        $this->userAuthCheckService = new UserAuthCheckService();
        $this->check_out_method_helper = new BaseCheckOutMethods();
        $this->cartItemRepository = new CartItemRepository();
        $this->productRepository = new ProductBaseRepository();
        $this->regionRepository = new RegionRepository();

        $this->model_key = "orders";
        $this->rules = [
            "cart_id" => "required|exists:carts,id",
            "shipping_method" => "sometimes",
            "payment_method" => "sometimes",
            "create_account" => "sometimes|boolean",
        ];
    }

    public function store(object $request): mixed
    {
        DB::beginTransaction();
        Event::dispatch("{$this->model_key}.create.before");

        try
        {
            if (!$request->payment_method) {
                $resolver = new CheckOutProcessResolver($request);
                $payment_method = $resolver->getTopPriorityPaymentMethod();
                $request->merge(["payment_method" => $payment_method]);
                $enable_delivery_validation = $resolver->enableDeliveryValidation($request);
            }

            $this->validateRequestData($request);
            $coreCache = $this->getCoreCache($request);

            $match = [
                "cart_id" => $request->cart_id,
                "website_id" => $coreCache->website->id,
            ];
            $order = $this->createOrUpdate($match, $this->orderData($request));

            if (isset($enable_delivery_validation) && $enable_delivery_validation) {
                $this->validateFields($request);
                $this->orderAddressRepository->store($request, $order);
            }

            $items = $this->cartItemRepository->query(function ($item_row) use ($request) {
                return $item_row
                    ->where("cart_id", $request->cart_id)
                    ->select("product_id", "qty")
                    ->get()
                    ->toArray();
            });

            if ($order->order_items()->get()->count() > 0) {
                $order->order_items()->delete();
            }

            if ($order->order_taxes()->get()->count() > 0) {
                $order->order_taxes()->delete();
            }

            foreach ($items as $order_item) {
                $order_item_details = $this->getProductItemData($request, $order_item, $coreCache);
                $this->orderItemRepository->store($request, $order, $order_item_details);
                $this->createOrderTax($order, $order_item_details);
                $this->updateInventoryItem($order, $order_item, $coreCache);
            }
            $this->updateOrderTax($order, $request);
            $this->orderCalculationUpdate($order, $request, $coreCache);
            $log_data = [
                "causer_type" => self::class,
                "order_id" => $order->id,
                "title" => $request->payment_method,
                "data" => $request->all(),
                "action" => "{$this->model_key}.createOrUpdate",
            ];
            Event::dispatch("order.create.update.after", $log_data);
            Event::dispatch("order.comment.create.after", ["order" => $order]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        Event::dispatch("{$this->model_key}.create.after", $order);
        DB::commit();

        return $order;
    }

    private function validateFields(object $request)
    {
        $this->rules = array_merge($this->rules, [
            "shipping_method" => ["required", new MethodValidationRule($request)],
            "address" => "required|array",
            "address.shipping" => "required|array",
            "address.billing" => "required|array",
            "address.*.first_name" => "required",
            "address.*.last_name" => "required",
            "address.*.phone" => "required",
            "address.*.email" => "required|email",
            "address.*.address1" => "required",
            "address.*.postcode" => "required",
            "address.*.country_id" => "required|exists:countries,id",
            "address.*.region_id" => "sometimes|exists:regions,id",
            "address.*.city_id" => "sometimes|exists:cities,id",
        ]);
        $this->validateData($request);
    }

    private function getProductItemData(object $request, mixed $order_item, object $coreCache): mixed
    {
        try
        {
            $product_details = $this->getProductDetail($request, $order_item, function ($product) use (&$tax, $request, $order_item, $coreCache) {
                $tax = $this->calculateTax($request, $order_item, $coreCache);
                $product_options = $this->getProductOptions($request, $product);
                return array_merge($product_options, $tax, $order_item);
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $product_details;
    }

    private function validateRequestData(object $request, ?callable $callback = null): ?array
    {
        try
        {
            $validation = [
                "shipping_method" => [
                    "sometimes",
                    new MethodValidationRule($request),
                ],
                "payment_method" => [
                    "sometimes",
                    new MethodValidationRule($request),
                ],
            ];
            if ($callback) {
                $validation = array_merge($validation, $callback());
            }
            $data = $this->validateData($request, $validation);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function orderData(object $request, ?callable $callback = null): array
    {
        try
        {
            $coreCache = $this->getCoreCache($request);
            $currency_code = SiteConfig::fetch("channel_currency", "channel", $coreCache?->channel->id);
            $payment_method_label = SiteConfig::fetch("payment_methods_{$request->payment_method}_title", "channel", $coreCache?->channel->id);
            $order_status = OrderStatusHelper::getStateLatestOrderStatus("pending")->slug;
            $data = [
                "website_id" => $coreCache?->website->id,
                "store_id" => $coreCache?->store->id,
                "channel_id" => $coreCache?->channel->id,
                "customer_id" => auth("customer")->id(),
                "cart_id" => $request->cart_id,
                "website_name" => $coreCache?->website->name,
                "channel_name" => $coreCache?->channel->name,
                "store_name" => $coreCache?->store->name,
                "is_guest" => auth("customer")->id() ? 0 : 1,
                "currency_code" => $currency_code->code,
                "payment_method" => $request->payment_method ?? "",
                "payment_method_label" => $payment_method_label ?? "",
                "status" => $order_status,
                "sub_total" => 0.00,
                "sub_total_tax_amount" => 0.00,
                "tax_amount" => 0.00,
                "grand_total" => 0.00,
                "total_qty_ordered" => 0.00,
                "total_items_ordered" => 0.00,
                "customer_ip_address" => GeoIp::requestIp(),
            ];
            if ($callback) {
                $data = array_merge($data, $callback());
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function createOrderTax(object $order, object $order_item_details): void
    {
        try
        {
            $this->storeOrderTax($order, $order_item_details, function ($order_tax, $order_tax_item_details, $rule) {
                $this->storeOrderTaxItem($order_tax, $order_tax_item_details, $rule);
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function updateOrderTax(object $order, object $request): void
    {
        try
        {
            $order->order_taxes()->get()->map( function ($order_tax) {
                $order_tax_item_amount = $order_tax->order_tax_items()->get()->map( function ($order_item) {
                    return $order_item->amount;
                })->toArray();
                $order_tax->update(["amount" => array_sum($order_tax_item_amount)]);
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function getMethodList(object $request): mixed
    {
        try
        {
            $request->validate([
                "cart_id" => "required|exists:carts,id",
            ], [
                "cart_id.required" => "cart is empty. Add product on cart",
                "cart_id.exists" => "cart is not found",
            ]);
            $coreCache = $this->getCoreCache($request);
            $check_out_resolver = new CheckOutProcessResolver($request);
            $total_products_price = $this->getTotalCartProductsPrice($request, $coreCache);
            $check_out_methods = $check_out_resolver->getCheckOutMethods(callback: function ($methods, $check_out_method) use ($check_out_resolver, $coreCache, $request, $total_products_price) {
                $data = $methods;
                if ($check_out_method == "delivery_methods") {
                    foreach ($methods as $key => $method) {
                        $delivery_fee_per_order = 0;
                        if ($method["slug"] != "free_shipping") {
                            $delivery_fee_per_order = (SiteConfig::fetch("{$check_out_method}_{$method['slug']}_flat_type", "channel", $coreCache->channel?->id) == "per_order")
                                ? (SiteConfig::fetch("{$check_out_method}_{$method['slug']}_flat_price", "channel", $coreCache->channel?->id) ?? 0)
                                : 0;
                        }
                        $data[$key] = array_merge($data[$key], [
                            "delivery_fee" => (float) $delivery_fee_per_order,
                            "delivery_fee_formatted" => PriceFormat::get($delivery_fee_per_order, $coreCache->store?->id, "store"),
                        ]);
                    }
                }

                if ($check_out_method == "payment_methods") {
                    foreach ($methods as $key => $method) {
                        if (in_array($method["slug"], ["cash_on_delivery", "bank_transfer"])) {
                            $max_order_total = SiteConfig::fetch("{$check_out_method}_{$method['slug']}_maximum_total_order", "channel", $coreCache->channel?->id) ?? 0;
                            $min_order_total = SiteConfig::fetch("{$check_out_method}_{$method['slug']}_minimum_total_order", "channel", $coreCache->channel?->id) ?? 0;
                            if (!($min_order_total <= $total_products_price && $max_order_total >= $total_products_price)) {
                                unset($data[$key]);
                            }
                        }
                    }
                }
                return array_values($data);
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $check_out_methods;
    }

    private function getTotalCartProductsPrice(object $request, object $coreCache): mixed
    {
        try
        {
            $cart_items = $this->cartItemRepository->query(function ($query) use ($request) {
                return $query->whereCartId($request->cart_id)->get()->toArray();
            });

            $total_price = 0;
            foreach ($cart_items as $item) {
                $country_region_id = $this->validateChangedCountry($request, $coreCache);
                $calculateTax = NewTaxPrices::calculate(
                    request: $request,
                    product_id: $item["product_id"],
                    country_id: $country_region_id["country_id"],
                    region_id: $country_region_id["region_id"],
                );

                $final_amount = $calculateTax["final"];
                $updated_product_price = ($final_amount["amount"] * $item["qty"]) + ($final_amount["tax_amount"] * $item["qty"]);
                $total_price += $updated_product_price;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $total_price;
    }

    private function updateInventoryItem(object $order, array $order_item, object $coreCache): void
    {
        try
        {
            LogCatalogInventoryItem::dispatchSync([
                "product_id" => $order_item["product_id"],
                "website_id" => $coreCache?->website->id,
                "event" => "{$this->model_key}.deduction",
                "adjustment_type" => "deduction",
                "quantity" => $order_item["qty"],
                "order_id" => $order->id
            ]);

            $product = $this->productRepository->fetch($order_item["product_id"]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        Event::dispatch("catalog.products.update.after", $product);
    }

    public function fetchOrderDetail(object $request): object
    {
        try
        {
            $this->userAuthCheckService->validateUser($request);
            $order = $this->queryFetch(["cart_id" => $request->cart_id], $this->relations);
            if (!$order) {
                throw new OrderNotFoundException();
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $order;
    }
}
