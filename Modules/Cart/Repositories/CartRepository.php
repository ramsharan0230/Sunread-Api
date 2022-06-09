<?php

namespace Modules\Cart\Repositories;

use Exception;
use Modules\Cart\Entities\Cart;
use Modules\Core\Entities\Store;
use Illuminate\Support\Facades\DB;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Website;
use Modules\Cart\Entities\CartItem;
use Modules\Core\Facades\SiteConfig;
use Illuminate\Support\Facades\Event;
use Modules\Core\Facades\PriceFormat;
use Modules\Product\Entities\Product;
use Modules\Tax\Facades\NewTaxPrices;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Repositories\BaseRepository;
use Modules\Sales\Traits\HasOrderCalculation;
use Illuminate\Validation\ValidationException;
use Modules\Cart\Services\UserAuthCheckService;
use Modules\Cart\Exceptions\OutOfStockException;
use Modules\Cart\Repositories\CartItemRepository;
use Modules\Country\Repositories\RegionRepository;
use Modules\Cart\Exceptions\CartHashIdNotFoundException;
use Elasticsearch\Common\Exceptions\Forbidden403Exception;
use Modules\Product\Repositories\StoreFront\ProductRepository;
use Modules\Product\Exceptions\ProductNotFoundIndividuallyException;

class CartRepository extends BaseRepository
{
    use HasOrderCalculation;

    protected $cart;
    protected $userAuthCheckService;
    protected $cartItem;
    protected $product;
    protected $store;
    protected array $cartStatus = [];
    protected $website;
    protected $channel;
    protected $cartItemRepo;
    protected $regionRepository;
    protected array $responseData = [];
    protected $productRepository;

    public function __construct()
    {
        $this->model = new Cart();
        $this->model_key = "carts";
        $this->userAuthCheckService = new UserAuthCheckService();
        $this->cartItem = new CartItem();
        $this->product = new Product();
        $this->store = new Store();
        $this->website = new Website();
        $this->channel = new Channel();
        $this->cartItemRepo = new CartItemRepository();
        $this->regionRepository = new RegionRepository();
        $this->productRepository = new ProductRepository();

        $this->rules = [
            "product_id" => "required|exists:products,id",
            "qty" => "sometimes|numeric|min:0",
            "type" => "sometimes|in:create,update"
        ];

        $this->cartStatus = [
            "product_removed" => __("core::app.cart.product-removed"),
            "product_qty_updated" => __("core::app.cart.product-qty-updated"),
            "product_added" => __("core::app.cart.product-added"),
            "product_remove_due_to_channel_change" => __("core::app.cart.product-remove-due-to-channel-change"),
            "cart_merged" => __("core::app.response.cart-merged"),
        ];
    }

    public function addOrUpdateCart(object $request): mixed
    {
        Event::dispatch("{$this->model_key}.add.update.before");

        DB::beginTransaction();
        try
        {
            $this->validateData($request);

            //check if add/update on cart is by guest or logged in user
            $customer = $this->userAuthCheckService->validateUser($request);

            $request->merge(["customer_id" => $customer->id ?? null, "type" => $request->type ?? "create"]);

            if (!$request->hasHeader("hc-cart") && $request->type == "update") {
                throw ValidationException::withMessages(["cart_hash_id" => __("core::app.exception_message.cart-id-required")]);
            } elseif (!$request->hasHeader("hc-cart") && $request->type == "create") {
                $this->checkProductConditions($request->product_id, $request);
                $this->addProductOnCart($request);
            } else {
                $cartHashId = $this->getCartHashIdFromHeader($request, "hc-cart");
                // if cart hash id exist in carts table
                $cart = $this->model::whereId($cartHashId)->first();
                if ($cart) {
                    $this->whenCartFoundInTable($cart, $request, $request->product_id);
                } else {
                    // if cart hash id sent but not found on cart table
                    // check product exist on product table, product status =1, in stock, same channel
                    $this->checkProductConditions($request->product_id, $request);
                    $this->addProductOnCart($request);
                }
            }
        }
        catch (Exception $exception)
        {
            DB::rollback();
            throw $exception;
        }

        DB::commit();
        Event::dispatch("{$this->model_key}.add.update.after", $this->responseData);
        return $this->responseData;
    }

    public function deleteProductFromCart(object $request): mixed
    {

        DB::beginTransaction();
        try
        {
            $this->validateData($request);

            //check if user is on guest or logged mode
            $customer = $this->userAuthCheckService->validateUser($request);
            $request->merge(["customer_id" => $customer->id ?? null]);

            if ($request->hasHeader("hc-cart")) {
                $cartId = $this->getCartHashIdFromHeader($request, "hc-cart");

                // if cart hash id exist in carts table
                $cart = $this->model::whereId($cartId)->firstOrFail();

                if ($cart) {
                    if ($request->customer_id != $cart->customer_id) {
                        throw new Forbidden403Exception(__("core::app.exception_message.not-allowed"));
                    }

                    $this->updateHeaderOnCart($cart, $request);

                    $conditions = ["cart_id" => $cartId, "product_id" => $request->product_id];
                    // if product id exist in the cart hash id, delete that product
                    $this->cartItem->where($conditions)->firstOrFail();
                    $this->itemClearFromCart($conditions, $cart);
                } else { // if cart hash id is sent but not found on cart table case:
                    $this->deleteProductAsPerCustomerMode($request);
                }
            } else { // if cart hash id is not sent case:
                $this->deleteProductAsPerCustomerMode($request);
            }
        }
        catch (Exception $exception)
        {
            DB::rollback();
            throw $exception;
        }

        DB::commit();
        return $this->responseData;
    }

    public function getAllProductFromCart(object $request): array
    {
        DB::beginTransaction();
        try
        {
            $coreCache = $this->getCoreCache($request);
            $checkChannel = $coreCache?->channel;

            $products = [];

            $relations = [
                    "catalog_inventories",
                    "images",
                    "images.types",
                    "product_attributes",
                    "product_attributes.attribute",
                    "website.channels"
                ];

                $subTotal = 0;
                $grandTotal = 0;
                $tax_rate_percent = 0;
                $tax_rate_value = 0;
                $cartId = null;

            // if cart hash id is sent
            if (isset($request->header()["hc-cart"]))
            {
                $cartHashId = $this->getCartHashIdFromHeader($request, "hc-cart");
                $cartId = $cartHashId;
                // if cart hash id exist in carts table
                $cart = $this->model::whereId($cartHashId)->firstOrFail();

                $this->updateHeaderOnCart($cart, $request);

                $this->mergeGuestCart($request, $cart);
                foreach ($cart->cartItems as $item)
                {
                   $productData = $this->getCartItemDetail($item, $relations, $checkChannel, $cart, $coreCache, $request);
                   $products[] = $productData;
                   $subTotal += $productData['total_amount'];
                   $tax_rate_percent += $productData['tax_rate_percent'];
                   $tax_rate_value += $productData['tax_rate_value'];
                }
            } elseif (auth("customer")->id() && !$request->hasHeader("hc-cart")) {
                $cart = $this->query(function ($query) {
                    return $query->whereCustomerId(auth("customer")->id())->firstOrFail();
                });
                $cartId = $cart->id;
                $item = $cart->cartItems()->latest()->first();
                $productData = $this->getCartItemDetail($item, $relations, $checkChannel, $cart, $coreCache, $request);
                $products[] = $productData;
                $subTotal += $productData['total_amount'];
                $tax_rate_percent += $productData['tax_rate_percent'];
                $tax_rate_value += $productData['tax_rate_value'];
            }
            $grandTotal = $subTotal;
            $items = [
                "items" => $products,
                "count" => count($products),
                "sub_total" => $subTotal,
                "sub_total_formatted" => PriceFormat::get($subTotal, $coreCache->store->id, "store"),
                "total_tax_percent" => $tax_rate_percent,
                "total_tax_value" => $tax_rate_value,
                "total_tax_value_formatted" => PriceFormat::get($tax_rate_value, $coreCache->store->id, "store"),
                "grand_total" => $grandTotal,
                "grand_total_formatted" => PriceFormat::get($grandTotal, $coreCache->store->id, "store"),
                "cart_id" => $cartId,
                "channel_change_msg" => $this->responseData['message'] ?? ""
            ];
        }
        catch (Exception $exception)
        {
            DB::rollback();
            throw $exception;
        }

        DB::commit();
        return $items;
    }

    private function getCartItemDetail(object $item, array $relations, object $checkChannel, object $cart, object $coreCache, object $request): mixed
    {
        $product = $this->product::whereId($item->product_id)->whereStatus(1)->with($relations)->firstOrFail();
        $channel_ids = $product->website->channels->pluck("id")->toArray();
        if (!in_array($checkChannel->id, $channel_ids)) {
            $item->delete();
            //  if there is no cart items on cart_items table then delete that cart hash id row from carts table
            $checkCartItemsExitsOnCartHashId = $this->cartItem->where("cart_id", $cart->id)->first();
            if (!$checkCartItemsExitsOnCartHashId) {
                $this->delete($cart->id);
            }
            $this->responseData['message'] = $this->cartStatus["product_remove_due_to_channel_change"];
        };
        return $this->getProductDetail($product, $item, $coreCache, $request);
    }

    private function mergeGuestCart(object $request, object $cart): bool
    {
        try
        {
            $customerId = auth("customer")->id();
            if ($customerId) {

                if ($cart->customer_id != null) throw new Forbidden403Exception(__("core::app.exception_message.not-allowed"));
                $checkCartOfUser = $this->model::whereCustomerId($customerId)->first();
                if ($checkCartOfUser) $checkCartOfUser->delete();

                $this->updateHeaderOnCart($cart, $request);
                $this->update(["customer_id" => $customerId], $cart->id);

                $this->responseData["message"] = $this->cartStatus["cart_merged"];
                $this->responseData["cart"] = $cart;
            }
        }

        catch (Exception $exception)
        {
            throw $exception;
        }

        return true;

    }

    public function mergeCart(object $request): mixed
    {
        DB::beginTransaction();
        try
        {
            // check if there is cart hash id
            if (isset($request->header()["hc-cart"])) {
                $cartHashId = $this->getCartHashIdFromHeader($request, "hc-cart");
                // if cart hash id exist in carts table
                $cart = $this->model::where("id", $cartHashId)->firstOrFail();
                $this->mergeGuestCart($request, $cart);
            }

        }
        catch (Exception $exception)
        {
            DB::rollback();
            throw $exception;
        }

        DB::commit();
        return count($this->responseData) > 0 ? $this->responseData : null;
    }

    private function getProductDetail(object $product, object $cartItem, object $coreCache, object $request): mixed
    {
        try
        {
            $data = [];
            $data["id"] = $product->id;
            $data["sku"] = $product->sku;
            $data["product_type"] = $product->parent_id ? "configurable" : "simple";

            $store = $coreCache->store;

            $product_details = $product->product_attributes->mapWithKeys(function ($product_attribute) use ($store, $product) {
                $match = [
                    "scope" => "store",
                    "scope_id" => $store->id,
                    "attribute_id" => $product_attribute->attribute->id
                ];

                return (!$product_attribute->attribute->is_user_defined) ? [$product_attribute->attribute->slug => ($product_attribute->attribute->type == "select") ? $product->value($match)?->name : $product->value($match)] : [];
            })->toArray();

            $data["name"] = $product_details["name"];
            $data["url_key"] = $this->productRepository->getUrlkeyWithVisibility($product, $store, $product_details["url_key"] ?? '');

            $match = [
                      "scope" => "store",
                      "scope_id" => $store->id,
                      "attribute_slug" => "tax_class_id",
                    ];
            $tax_rate_percent = 0;
            $tax_rate_value = 0;
            $tax_summary = [];
            $country_region_data = $this->validateChangedCountry($request, $coreCache);
            $calculateTax = NewTaxPrices::calculate(
                request: $request,
                product_id: $data["id"],
                country_id: $country_region_data["country_id"],
                region_id: $country_region_data["region_id"]
            );
            $final_amount_after_tax = $calculateTax["final"];
            $data["price"] = $final_amount_after_tax["amount"];
            if ($final_amount_after_tax) {
                $tax_rate_percent = $final_amount_after_tax["tax_rate_percent"];
                $tax_rate_value = $final_amount_after_tax["tax_amount"];
                foreach ($final_amount_after_tax["rules"] as $key => $rule) {
                    $tax_summary[$key]['rule'] = $rule->name;
                    $total_tax_rates = 0;
                    foreach ($rule->rates as $rate) {
                        $total_tax_rates+= $rate->tax_rate_value;
                    }
                    $tax_summary[$key]['amount'] = $total_tax_rates;
                    $tax_summary[$key]['amount_formatted'] = PriceFormat::get($total_tax_rates, $coreCache->store?->id, "store");
                }
            }
            $data["qty"] = $cartItem->qty;
            $data["tax_rate_value"] = $tax_rate_value * $data["qty"];
            $data["tax_rate_percent"] = $tax_rate_percent;
            $data["tax_amount_formatted"] = PriceFormat::get($data["tax_rate_value"], $coreCache->store?->id, "store");
            $data['tax_summary'] = $tax_summary;
            $data["price_formatted"] = PriceFormat::get($data["price"], $coreCache->store?->id, "store");

            if ($product->type == "simple" && $product->parent_id) {
                $configurable_attributes = [];
                $product->product_attributes->filter(function ($product_attribute) {
                    return ($product_attribute->attribute->slug == "color") || ($product_attribute->attribute->slug == "size");
                })->map(function ($product_attribute) use ($store, $product, &$configurable_attributes) {
                    $match = [
                        "scope" => "store",
                        "scope_id" => $store->id,
                        "attribute_id" => $product_attribute->attribute_id
                    ];
                    $configurable_attributes[] = [
                        "label" => $product_attribute->attribute->name,
                        "value" => $product->value($match)?->name,
                    ];
                })->toArray();

                $data["configurable_attributes"] = $configurable_attributes;
            }

            // Product Stock
            $productStock = $product->catalog_inventories->first();
            $coreCache = $this->getCoreCache($request);
            $config_manage_stock = SiteConfig::fetch("catalog_inventory_manage", "website", $coreCache?->website->id);
            $manage_stock = $productStock?->use_config_manage_stock
                            ? ($config_manage_stock ? 1 : 0)
                            : $productStock?->manage_stock;
            $stock_status = ($manage_stock && ((!$productStock?->is_in_stock) || ($cartItem?->qty > $productStock?->quantity)))
                            ? false
                            : true;
            $data["stock_status"] = $stock_status;
            $data["total_amount"] = ($data["price"] * $data["qty"]) + $data["tax_rate_value"];
            $data["total_amount_formatted"] = PriceFormat::get($data["total_amount"], $coreCache->store?->id, "store");

            // Product Single Image
            $data["image"] = "";
            foreach ($product->images as $image)
            {
                $imageTypes = $image->types->pluck("slug")->toArray();
                if (in_array("small_image", $imageTypes) && $image->path) {
                    $data["image"] = Storage::url($image->path);
                    break;
                }
                else {
                    $data["image"] = in_array("base_image", $imageTypes) ? Storage::url($image->path) : "";
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    private function addProductOnCart(object $request): mixed
    {
        try
        {
            $this->quantityValidation($request->qty);
            $checkIfUserHasCartAlready = $this->model::whereCustomerId($request->customer_id)->first();

            if (isset($request->customer_id) && $checkIfUserHasCartAlready) {

                $this->updateHeaderOnCart($checkIfUserHasCartAlready, $request);

                // if product id already exist in that logged in user id
                $cartItem = $this->cartItem->whereProductId($request->product_id)->whereCartId($checkIfUserHasCartAlready->id)->first();
                if ($cartItem) {
                    $qty = ($cartItem->qty) + ($request->qty ?? 1);
                    if ($request->type == "update") {
                        $qty = $request->qty ?? 1;
                    }

                    $whereConditions = [
                        "product_id" => $request->product_id,
                        "cart_id" => $checkIfUserHasCartAlready->id,
                    ];

                    $this->cartItemRepo->updateItemWithConditions($whereConditions, ["qty" => $qty]);

                    $this->responseData["message"] = $this->cartStatus["product_qty_updated"];
                    $this->responseData["cart"] = $checkIfUserHasCartAlready;
                } else {
                    $cartItemCreateData = [
                        "cart_id" => $checkIfUserHasCartAlready->id,
                        "product_id" => $request->product_id,
                        "qty" => $request->qty ?? 1,
                    ];

                    $this->cartItemRepo->create($cartItemCreateData);

                    $this->responseData['message'] = $this->cartStatus["product_added"];
                    $this->responseData["cart"] = $checkIfUserHasCartAlready;
                }
            } else {
                $cartData = [
                    "customer_id" => $request->customer_id,
                    "channel_code" => $this->getCartHashIdFromHeader($request, "hc-channel"),
                    "store_code" => $this->getCartHashIdFromHeader($request, "hc-store")
                ];
                $cart = $this->create($cartData);

                $cartItemCreateData = [
                    "cart_id" => $cart->id,
                    "product_id" => $request->product_id,
                    "qty" => $request->qty ?? 1
                ];
                $this->cartItemRepo->create($cartItemCreateData);

                $this->responseData["message"] = $this->cartStatus["product_added"];
                $this->responseData["cart"] = $cart;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return true;
    }

    private function checkProductConditions(int $productId, object $request): void
    {
        // check if product exist on product table
        $product = $this->getProduct($productId);
        // check if product exits on channel
        $this->checkIfProductExistOnChannel($product, $request);
        // check stock of product
        $this->checkProductStock($product, $request);
    }

    private function getProduct(?int $id): object
    {
        try
        {
            $product = $this->product::whereId($id)->whereStatus(1)->firstOrFail();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return $product;
    }

    private function checkIfProductExistOnChannel(object $product, object $request): bool
    {
        try
        {
            $cacheData = $this->getCoreCache($request);
            $website_ids = $product->website->channels->pluck("id")->toArray();
            $channel_status = $product->channels()->whereChannelId($cacheData->store?->channel_id)->first();
            if (!in_array($cacheData?->channel?->id, $website_ids) || $channel_status) throw new ProductNotFoundIndividuallyException();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return true;
    }

    private function checkProductStock(object $product, object $request): bool
    {
        try
        {
            $productStock = $product->catalog_inventories()->first();
            $coreCache = $this->getCoreCache($request);
            $config_manage_stock = SiteConfig::fetch("catalog_inventory_manage", "website", $coreCache?->website->id);
            $qty = $request->qty ?? 1;
            $manage_stock = $productStock?->use_config_manage_stock
                            ? ($config_manage_stock ? 1 : 0)
                            : $productStock?->manage_stock;
            if ($manage_stock && ((!$productStock?->is_in_stock) || ($qty > $productStock?->quantity))) {
                throw new OutOfStockException();
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return true;
    }

    private function deleteProductAsPerCustomerMode(object $request): bool
    {
        try
        {
            if(!$request->customer_id) throw new CartHashIdNotFoundException();
            $checkIfUserHasCartAlready = $this->model::whereCustomerId($request->customer_id)->firstOrFail();

            // if product id exist in that logged in user id
            $cartItem = $this->cartItem->whereProductId($request->product_id)->whereCartId($checkIfUserHasCartAlready->id)->firstOrFail();

            $this->updateHeaderOnCart($checkIfUserHasCartAlready, $request);
            $this->itemClearFromCart(["id" => $cartItem->id], $checkIfUserHasCartAlready);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return true;
    }

    private function updateHeaderOnCart(object $cart, object $request): bool
    {
        try
        {
            $headerChannel = $this->getCartHashIdFromHeader($request, "hc-channel");
            $headerStore = $this->getCartHashIdFromHeader($request, "hc-store");
            $data = [ "channel_code" => $headerChannel, "store_code" => $headerStore ];
            if ($cart->channel_code != $headerChannel || $cart->store_code != $headerStore) $this->update($data, $cart->id);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return true;
    }

    private function getCartHashIdFromHeader(object $request, string $headerName): mixed
    {
        return is_array($request->header()["{$headerName}"]) ? $request->header()["{$headerName}"][array_key_first($request->header()["{$headerName}"])] : '';
    }


    private function whenCartFoundInTable(object $cart, object $request, string $productId): bool
    {
        $this->updateHeaderOnCart($cart, $request);

        $cartHashId = $cart->id;

        $cartAndProductCheck = ["cart_id" => $cartHashId, "product_id" => $productId];

        // if product id already exist in the cart hash id
        $checkProductId = $this->cartItem->where($cartAndProductCheck)->first();
        if ($request->type == "update" && !$checkProductId) {
            throw new ProductNotFoundIndividuallyException();
        }

        if ($checkProductId) {
            //  check if passed qty in request 0 or not, if 0 then delete that product from cart_items table.
            // if not 0 then update product quantity.

            if ($request->qty == 0) {
                $this->cartItemRepo->deleteCartItem($cartAndProductCheck);
                //  if there is no cart items on cart_items table then delete that cart hash id row from carts table
                $checkCartItemsExitsOnCartHashId = $this->cartItem->where("cart_id", $cartHashId)->first();
                if (!$checkCartItemsExitsOnCartHashId) {
                    $this->delete($cartHashId);
                }

                $this->responseData["message"] = $this->cartStatus["product_removed"];
                $this->responseData["cart"] = $cart;
                } else {
                    // check product exist on product table, product status =1, has visibility and in stock
                    $this->checkProductConditions($productId, $request);

                    $qty =  ($checkProductId->qty) + ($request->qty ?? 1);
                    if ($request->type == "update") {
                        $qty = $request->qty ?? 1;
                    }
                    $this->cartItemRepo->updateItemWithConditions($cartAndProductCheck, ["qty" => $qty]);

                    $this->responseData["message"] = $this->cartStatus["product_qty_updated"];
                    $this->responseData["cart"] = $cart;
            }
        } else {

            $this->quantityValidation($request->qty);
            // check product exist on product table, product status =1, in stock, same channel
            $this->checkProductConditions($productId, $request);

            // if product does not exist on table of that cart hash id, add product on cart on that cart hash id:
            $cartItemCreateData = [
                "cart_id" => $cartHashId,
                "product_id" => $productId,
                "qty" => $request->qty ?? 1,
            ];
            $this->cartItemRepo->create($cartItemCreateData);
            $this->responseData["message"] = $this->cartStatus["product_added"];
            $this->responseData["cart"] = $cart;
        }
        return true;
    }

    private function quantityValidation(?int $qty): void
    {
        if (isset($qty) && $qty <= 0) throw ValidationException::withMessages(["quantity" => __("core::app.exception_message.product-qty-must-be-above-0")]);
    }

    private function itemClearFromCart(array $cartAndProductCheck, object $cart): void
    {
        try
        {
            $this->cartItemRepo->deleteCartItem($cartAndProductCheck);
            //  if there is no cart items on cart_items table then delete that cart hash id row from carts table
            $checkCartItemsExitsOnCartHashId = $this->cartItem->whereCartId($cart->id)->first()?->id;
            if (!$checkCartItemsExitsOnCartHashId) {
                $this->delete($cart->id);
            }

            $this->responseData["message"] = $this->cartStatus["product_removed"];
            $this->responseData["cart"] = $cart;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
