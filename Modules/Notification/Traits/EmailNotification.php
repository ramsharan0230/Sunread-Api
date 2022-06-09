<?php

namespace Modules\Notification\Traits;

use stdClass;
use Exception;
use Modules\Core\Entities\Store;
use Modules\Core\Facades\SiteConfig;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Modules\Notification\Exceptions\EmailTemplateNotFoundException;

trait EmailNotification
{
    /**
     *  get email Content and subject data from email template
     */
    public function getData(object $entity, string $event, string $append_data = ""): object
    {
        try
        {
            /** get all email variables data by specific events */
            $variable_data = $this->getVariableData($event, $entity, $append_data);
            /**
             * get template from configurations according to scope, scope id and event code
             */
            $email_template = SiteConfig::fetch($event, "store", $variable_data->store_id);

            if (!$email_template) {
                throw new EmailTemplateNotFoundException();
            }

            /**
             * Set store_id as store (global variable) to get content from configuration
             */
            config(['store' => $variable_data->store_id]);

            $data = new stdClass();
            $data->content = htmlspecialchars_decode($this->render($email_template->content, $variable_data));
            $data->subject = htmlspecialchars_decode($this->render($email_template->subject, $variable_data));
            $data->template_id = $email_template->id;
            $data->to_email = $variable_data->customer_email_address;
            $data->style = $email_template->style;
            $data->sender_name = SiteConfig::fetch("email_sender_name", "store", $variable_data->store_id) ?? config("mail.from.name");
            $data->sender_email = SiteConfig::fetch("email_sender_email", "store", $variable_data->store_id) ?? config("mail.from.address");
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return $data;
    }

    /**
     * compile php variable and content to render in blade template
     */
    public function render(string $content, object $data = null): string
    {
        try
        {
            $php = Blade::compileString($content);
            ob_start();

            /** Extract template variables */
            extract((array)$data, EXTR_SKIP);

            /** replace template variables with defined variable data  */
            eval("?>" . $php);

            $content = ob_get_contents();
            ob_end_clean();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $content;
    }

    /**
     * get all template variable data according to email template code
     */
    public function getVariableData(string $event_code, object $entity, string $append_data): object
    {
        try
        {
            switch ($event_code) {
                case "forgot_password" :
                    $data = $this->forgotPassword($entity, $append_data);
                    break;

                case "new_account" :
                    $data = $this->newAccount($entity);
                    break;

                case "contact_form" :
                    $data = [];
                    break;

                case "welcome_email":
                case "confirmed_email":
                case "reset_password":
                    $data = $this->getCustomerData($entity);
                    break;

                case "new_order" :
                case "order_update" :
                case "new_guest_order" :
                case "order_update_guest" :
                    $data = $this->orderData($entity);
                    break;
                case "order_comment" :
                    $data = $this->orderCommentData($entity);
                    break;
                case "order_status_update" :
                    $data = $this->orderStatusData($entity);
                    break;
                case "guest_registration" :
                    $data = $this->getGuestData($entity, $append_data);
                    break;
            }
            $general = $this->getGeneralVariableData($data->store_id);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        $result = array_merge((array)$general, (array)$data);
        return (object) $result;
    }

    /**
     * get customer data by customer id
    */
    private function getCustomerData(object $customer): object
    {
        try
        {
            /** get store data by its id */
            if (empty($customer->store_id)) {
                $channel = SiteConfig::fetch("website_default_channel", "website", $customer->website_id);
                $store = Store::findOrFail($channel->default_store_id);
            } else {
                $store = Store::findOrFail($customer->store_id);
            }
            /** get channel by store */
            $channel = $store->channel;

            /** get store url from configuration */
            $store_front_baseurl = SiteConfig::fetch("storefront_base_urL", "store", $store->id);

            $storefront_url = "{$store_front_baseurl}/{$channel->code}/{$store->code}";

            $customer_dashboard_url = url("{$storefront_url}/account");

            $customer_data = [
                "customer_id" => $customer->id,
                "customer_name" => "{$customer->first_name} {$customer->middle_name} {$customer->last_name}",
                "customer_email_address" => $customer->email,
                "customer_dashboard_url" => $customer_dashboard_url,
                "store_id" => $customer->store_id,
                "channel_code" => $channel->code,
                "store_code" => $store->code,
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return (object)$customer_data;
    }

    /**
      * get all general variables data
    */
    private function getGeneralVariableData(int $store_id = 0): object
    {
        try
        {
            $paths = [
                "store_url" => "storefront_base_urL",
                "store_phone_number" => "store_phone_number",
                "store_state" => "store_region",
                "store_post_code" => "store_zip_code",
                "store_city" => "store_city",
                "store_address_line_1" => "store_street_address",
                "store_address_line_2" => "store_address_line2",
                "store_vat_number" => "store_vat_number",
                "store_email_address" => "store_email_address",
                "store_email_logo_url" => "store_email_logo_url",
            ];
            $data = [
                "store_name" => SiteConfig::fetch("store_name", "store", $store_id)?->name,
                "store_country" => SiteConfig::fetch("store_country", "store", $store_id)?->name,
            ];
            foreach ($paths as $key => $path) {
                $data[$key] = SiteConfig::fetch($path, "store", $store_id);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return (object) $data;
    }

    /**
        get forgot password variables data
    */
    private function forgotPassword(object $customer, string $append_data): object
    {
        try
        {
            $data = $this->getCustomerData($customer);
            $data->password_reset_url = url("{$data->channel_code}/{$data->store_code}/reset-password/{$append_data}");
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    
    /**
        get guest data
    */
    private function getGuestData(object $customer, string $append_data): object
    {
        try
        {
            $data = $this->getCustomerData($customer);
            $data->account_confirmation_url = url("{$data->channel_code}/{$data->store_code}/verify-account/{$customer->verification_token }");
            $data->password = $append_data;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }


    /**
        get new account variables data
    */
    private function newAccount(object $customer): object
    {
        try
        {
            $data = $this->getCustomerData($customer);
            $data->account_confirmation_url = url("{$data->channel_code}/{$data->store_code}/verify-account/{$customer->verification_token}");
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    /**
       * get order variables data
    */
    private function orderData(object $order): object
    {
        try
        {
            /** get order items detail by order  */
            $order_items = $this->getOrderItems($order);

            $billing = $this->getBillingAddress($order->id);
            $shipping = $this->getShippingAddress($order->id);
            $data = [
                "order_id" => $order->id,
                "customer_email_address" => $order->customer_email,
                "customer_name" => "{$order->customer_first_name} {$order->customer_middle_name} {$order->customer_last_name}",
                "order_items" => $order_items,
                "billing_address" => $billing,
                "shipping_address" => $shipping,
                "order" => $order,
                "store_id" => $order->store_id,
                "order_state" => $order->order_status->order_status_state?->name ?? null,
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return (object) $data;
    }

    /**
       * get order comments variables data
    */
    private function orderCommentData(object $comment): object
    {
        try
        {
            $order = $this->orderRepository->fetch($comment->order_id);
            $order = $this->orderData($order);
            $order->comment = $comment->comment;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $order;
    }

    /**
     * get Order comment data
    */
    private function getCommentData(int $comment_id): ?object
    {
        try
        {
            $comment = $this->orderCommentRepository->fetch($comment_id);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $comment;
    }

    /**
    * get order Items data in html
    */
    private function getOrderItems(object $order): ?string
    {
        try
        {
            $scope = [
                "scope" => "store",
                "scope_id" => $order->store_id,
            ];

            $order_items = $this->orderItemRepository->query(function ($query) use($order) {
               return  $query->whereOrderId($order->id)->get();
            });
            $order_items = $order_items->each(function ($order_item) use ($scope) {
                $product = $this->productRepository->fetch($order_item->product_id);
                $color = $product->value(array_merge($scope, [ "attribute_slug" => "color" ]));
                $size = $product->value(array_merge($scope, [ "attribute_slug" => "size" ]));
                $image = $product->images()->get()->filter(fn ($img) => $img->types()->where("slug", "base_image")->first())->first();

                $order_item["color"] = $color->name ?? null;
                $order_item["size"] = $size->name ?? null;
                $order_item["image_url"] = $image?->path
                    ? Storage::url($image->path)
                    : null;
            });
            /** render in blade file for order items */
            $items  = view('notification::orderItem', compact("order_items", "order"))->render();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $items;
    }

    /**
    * get customer billing address data
    */
    private function getBillingAddress(int $order_id): ?object
    {
        try
        {
            $address = $this->orderAddressRepository->queryFetch([
               "order_id" => $order_id,
               "address_type" => "billing",
            ]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $address;
    }

    /**
    * get customer shipping address data
    */
    private function getShippingAddress(int $order_id): ?object
    {
        try
        {
            $address = $this->orderAddressRepository->queryFetch([
                "order_id" => $order_id,
                "address_type" => "shipping",
            ]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $address;
    }

    /**
    * get customer shipping address data
    */
    private function orderStatusData(object $order): ?object
    {
        try
        {
            $data = [
                "order_id" => $order->id,
                "customer_email_address" => $order->customer_email,
                "customer_name" => "{$order->customer_first_name} {$order->customer_middle_name} {$order->customer_last_name}",
                "order" => $order,
                "store_id" => $order->store_id,
                "order_state" => $order->order_status->order_status_state?->name ?? null,
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return (object) $data;
    }
}
