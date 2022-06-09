<?php

namespace Modules\PaymentKlarna\Repositories;

use Exception;
use Illuminate\Support\Arr;
use Modules\Sales\Entities\Order;
use Illuminate\Support\Collection;
use Modules\Core\Facades\SiteConfig;
use Illuminate\Support\Facades\Event;
use Modules\Sales\Repositories\OrderMetaRepository;
use Modules\CheckOutMethods\Contracts\PaymentMethodInterface;
use Modules\CheckOutMethods\Repositories\BasePaymentMethodRepository;

class KlarnaRepository extends BasePaymentMethodRepository implements PaymentMethodInterface
{
    protected object $request;
    protected object $parameter;
    protected string $method_key;
    protected mixed $urls;
    public string $base_url;
    public string $user_name, $password;
    public mixed $base_data;
    public array $relations;
    public $orderMetaRepository;

    public function __construct(object $request, object $parameter)
    {
        $this->request = $request;
        $this->method_key = "klarna";
        $this->parameter = $parameter;

        parent::__construct($this->request, $this->method_key, $this->parameter);
        $this->method_detail = array_merge($this->method_detail, $this->createBaseData());
        $this->urls = $this->getApiUrl();
        $this->base_url = $this->getBaseUrl();
        $this->orderMetaRepository = new OrderMetaRepository();
    }

    public function getModel(): object
    {
        return Order::query();
    }

    private function getApiUrl(): Collection
    {
        return $this->collection([
            [
                "type" => "production",
                "urls" => [
                    [
                        "name" => "Europe",
                        "slug" => "europe",
                        "url" => "https://api.klarna.com/"
                    ],
                    [
                        "name" => "North America:",
                        "slug" => "north-america",
                        "url" => "https://api-na.klarna.com/"
                    ],
                    [
                        "name" => "Oceania",
                        "slug" => "oceania",
                        "url" => "https://api-oc.klarna.com/"
                    ],
                ]
            ],
            [
                "type" => "playground",
                "urls" => [
                    [
                        "name" => "Europe",
                        "slug" => "europe",
                        "url" => "https://api.playground.klarna.com/"
                    ],
                    [
                        "name" => "North America:",
                        "slug" => "north-america",
                        "url" => "https://api-na.playground.klarna.com/"
                    ],
                    [
                        "name" => "Oceania",
                        "slug" => "oceania",
                        "url" => "https://api-oc.playground.klarna.com/"
                    ],
                ]
            ],
        ]);
    }

    private function getBaseUrl(): string
    {
        try
        {
            $data = $this->methodDetail();
            $api_endpoint_data = $this->urls->where("type", $data->api_mode)->map(function ($mode) use ($data) {
                $end_point_data = $this->collection($mode["urls"])->where("slug", $data->api_endpoint)->first();
                return $this->object($end_point_data);
            })->first()->url;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $api_endpoint_data;
    }

    private function createBaseData(): array
    {
        try
        {
            $this->user_name = SiteConfig::fetch("payment_methods_klarna_api_config_username", "channel", $this->coreCache->channel?->id);
            $this->password = SiteConfig::fetch("payment_methods_klarna_api_config_password", "channel", $this->coreCache->channel?->id);
            $data = [
                "user_name" => $this->user_name,
                "password" =>  $this->password,
            ];

            $paths = [
                "api_mode" => "payment_methods_klarna_api_config_mode",
                "api_endpoint" => "payment_methods_klarna_api_config_endpoint",
                "color_button" => "payment_methods_klarna_design_color",
                "color_button_text" => "payment_methods_klarna_design_text_color",
                "color_checkbox" => "payment_methods_klarna_design_color_checkbox",
                "color_checkbox_checkmark" => "payment_methods_klarna_design_color_checkbox_checkmark",
                "color_header" => "payment_methods_klarna_design_color_header",
                "color_link" => "payment_methods_klarna_design_color_link",
                "purchase_country" => "default_country",
                "terms" => "payment_methods_klarna_api_config_terms_page",
                "checkout" => "payment_methods_klarna_api_config_checkout_page",
                "confirmation" => "payment_methods_klarna_api_config_confirmation_page",
                "push" => "payment_methods_klarna_api_config_push_notify",
            ];

            foreach ($paths as $key => $path) {
                $data[$key] = SiteConfig::fetch($path, "channel", $this->coreCache->channel?->id);
            }
            $data = array_merge($data, [
                "locale" => SiteConfig::fetch("store_locale", "store", $this->coreCache->store?->id),
                "base_url" => SiteConfig::fetch("storefront_base_urL", "website", $this->coreCache?->website?->id),
            ]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function get(): mixed
    {
        try
        {
            $data = $this->getPostData(function ($order, $shipping_address) {
                $order->update(["base_url" => $this->base_url]);
                return [
                    "merchant_urls" => $this->getMerchantUrl(),
                    "merchant_data" => $order->id,
                    "merchant_requested" => [
                        "order_id" => $order->id,
                        "cart_id" => $order->cart_id,
                    ],
                    "options" => $this->getDesignOption(),
                ];
            });
            $response = $this->postClient("checkout/v3/orders", $data);
            $data["merchant_urls"] = $this->getMerchantUrl("?identifier={$response['order_id']}");
            $response = $this->postClient("checkout/v3/orders/{$response['order_id']}", $data);
            $match = [
                "order_id" => $this->parameter->order->id,
                "meta_key" => "{$this->method_key}_response",
            ];
            $data = array_merge($match, [
                "meta_value" => [
                        "klarna_api_order_id" => $response['order_id'],
                        "base_url" => $this->base_url,
                        "status" => $response['status'],
                        "klarna_response" => $response,
                    ]
                ]);
            $this->orderMetaRepository->createOrUpdate($match, $data);
            $transaction_log = [
                "order" => $this->parameter->order,
                "request" => $this->request->all(),
                "response" => $response,
            ];
            Event::dispatch("order.transaction.create.update.after", $transaction_log);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $this->object($response);
    }

    private function getPostData(?callable $callback = null): array
    {
        try
        {
            $order = $this->orderModel->whereId($this->parameter->order->id)->first();
            $shipping_address = ($order->customer_id) ? $this->getShippingDetail($order->customer?->addresses, "default_shipping_address") : [];
            $data = [
                "purchase_country" => $this->base_data->purchase_country?->iso_2_code,
                "purchase_currency" => $order?->currency_code,
                "locale" => $this->base_data->locale?->code,
                "merchant_reference2" => $order->cart_id,
            ];
            $customer_data = [
                "billing_address" => $this->getShippingDetail($order->customer?->addresses, "default_billing_address"),
                "shipping_address" => $shipping_address,
                "customer" => $this->getCustomer($order),
            ];
            $customer = $order->customer_id ? $customer_data : [];
            if ($callback) {
                $data = array_merge($data, $callback($order, $shipping_address), $this->getOrderLine($order), $customer);
            }
            if (!$order->customer_id) {
                Arr::forget($data, "customer");
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    private function getOrderLine(object $order): array
    {
        try
        {
            $sum_tax_amount = 0;
            $sum_total_amount = 0;
            $data = [
                "order_lines" => $order->order_items->map(function ($order_item) use (&$sum_tax_amount, &$sum_total_amount) {
                    $unit_price = (float) ($order_item->price + $order_item->tax_amount) * 100;
                    $tax_rate = (float) ($order_item->tax_percent * 100);
                    $total_discount_amount = (float) ($order_item->discount_amount_tax * 100);
                    $total_amount = (float) ($unit_price * $order_item->qty) - $total_discount_amount;

                    $total_tax_amount = (float) ($order_item->qty * $order_item->tax_amount) * 100;
                    $sum_tax_amount += $total_tax_amount;
                    $sum_total_amount += $total_amount;

                    return [
                        "type" => "physical",
                        "reference" => $order_item->sku,
                        "name" => $order_item->name,
                        "quantity" => (float) $order_item->qty,
                        "quantity_unit" => "pcs",
                        "unit_price" => $unit_price,
                        "tax_rate" => $tax_rate,
                        "total_amount" => $total_amount,
                        "total_discount_amount" => $total_discount_amount,
                        "total_tax_amount" => $total_tax_amount,
                    ];
                })->toArray(),
                "order_amount" => $sum_total_amount,
                "order_tax_amount" => $sum_tax_amount,
            ];
        }

        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;

    }

    private function getShippingDetail(mixed $order_addresses, string $address_type): array
    {
        try
        {
            if (!$order_addresses) return [];
            $address = $order_addresses->where($address_type, 1)->first();
            $city = !empty($address->city_id) ? $address->city->name : $address?->city_name;
            $region = !empty($address->region_id) ? $address->region->name : $address?->region_name;

            $address_data = [
                "given_name" => $address?->first_name,
                "family_name" => $address?->last_name,
                "email" => $this->parameter->order->customer?->email,
                "street_address" => $address?->address_line_1,
                "street_address2" => $address?->address_line_2,
                "postal_code" => $address?->postcode,
                "city" => $city,
                "region" => $region,
                "phone" => $address?->phone,
                "country" => $address?->country?->iso_2_code,
                "reference" => $address?->id,
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $address_data;
    }

    private function getDesignOption(): array
    {
        return [
            "color_button" => $this->base_data->color_button,
            "color_button_text" => $this->base_data->color_button_text,
            "color_checkbox" => $this->base_data->color_checkbox,
            "color_checkbox_checkmark" => $this->base_data->color_checkbox_checkmark,
            "color_header" => $this->base_data->color_header,
            "color_link" => $this->base_data->color_link,
            "allow_separate_shipping_address" => true
        ];
    }

    private function getMerchantUrl(string $klarna_order_id = ""): array
    {
        $base_url_api = config("core.api_base_url");
        return [
            "terms" => "{$this->base_data->base_url}{$this->base_data->terms}",
            "checkout" => "{$this->base_data->base_url}{$this->base_data->checkout}{$klarna_order_id}",
            "confirmation" => "{$this->base_data->base_url}{$this->coreCache->channel?->code}/{$this->coreCache->store?->code}/checkout/confirmation{$klarna_order_id}",
            "push" => "{$base_url_api}/api/{$this->base_data->push}{$klarna_order_id}",
        ];
    }
    private function getCustomer(object $order): array
    {
        $customer = [
            "date_of_birth" => $order->customer?->date_of_birth,
            "type" => $order->customer?->customer_type,
            "gender" => $order->customer?->gender
        ];

        return $order->customer_id ? $customer : [];
    }
}
