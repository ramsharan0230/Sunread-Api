<?php

namespace Modules\Erp\Traits\Mapper;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Exception;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Core\Traits\Slugify;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductAttribute;

trait AttributeMapper
{
    use Slugify;
    use PriceMapper;
    use DescriptionMapper;

    /**
     *
     * Creates attribute value with their respective scopes.
     */
    private function createAttributeValue(
        ?object $product = null,
        ?object $erp_product_iteration = null,
        ?bool $ean_code_value = true,
        ?int $visibility = 8,
        mixed $variant = null,
        ?string $scope = "website",
        ?callable $callback = null
    ): void {
        try
        {

            if (!$callback) {
                $attribute_data = $this->getAttributeData(
                    product: $product,
                    erp_product_iteration: $erp_product_iteration,
                    ean_code_value: $ean_code_value,
                    visibility: $visibility,
                    variant: $variant
                );
            } else {
                $attribute_data = $callback();
            }

            foreach ($attribute_data as $attributeData) {
                if (empty($attributeData["value"])) {
                    continue;
                }
                $attribute = Attribute::find($attributeData["attribute_id"]);
                $attribute_type = config("attribute_types")[$attribute->type ?? "string"];
                $value = $attribute_type::create(["value" => $attributeData["value"]]);

                $product_attribute_data = [
                    "attribute_id" => $attribute->id,
                    "product_id"=> $product->id,
                    "value_type" => $attribute_type,
                    "value_id" => $value->id,
                    "scope" => $scope,
                    "scope_id" => $this->website_id,
                ];
                $match = $product_attribute_data;
                unset($match["value_id"]);

                ProductAttribute::updateOrCreate($match, $product_attribute_data);
            }

        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    /**
     *
     * Get all the attribute values from erp data
     */
    public function getAttributeData(
        object $product,
        object $erp_product_iteration,
        bool $ean_code_value = true,
        int $visibility = 8,
        mixed $variant = null
    ): array {
        try
        {
            $ean_code = $this->getDetailCollection("eanCodes", $erp_product_iteration->sku);
            $variants = $this->getDetailCollection("productVariants", $erp_product_iteration->sku);

            if ($ean_code_value) {
                $pluck_code = $this->getValue($variants)->first()["code"];
                $ean_code_value = $this->getValue($ean_code)->where("variantCode", $pluck_code)->first()["crossReferenceNo"] ?? "";
            } else {
                $ean_code_value = $this->getValue($ean_code)->first()["crossReferenceNo"] ?? "";
            }

            $description_value = $this->getDetailCollection("productDescriptions", $erp_product_iteration->sku);
            $description = ($description_value->count() > 0)
                ? $this->getScopeWiseDescription($description_value, $product, $erp_product_iteration)
                : "";

            // get price for specific product
            $price = $this->getDetailCollection("salePrices", $erp_product_iteration->sku);
            $default_price_data = [
                "unitPrice" => 0.0,
                "startingDate" => "",
                "endingDate" => "",
            ];
            if ($product->type == Product::SIMPLE_PRODUCT) {
                $this->storeScopeWiseValue($price, $product);
            }

            $price_value = ($price->count() > 0)
                ? $this->getValue($price)->where("currencyCode", "")->where("salesCode", "WEB")->first() ?? $default_price_data
                : $default_price_data;

            // Condition for invalid date/times
            $max_time = strtotime("2030-12-28");
            $start_time = abs(strtotime($price_value["startingDate"]));
            $end_time = abs(strtotime($price_value["endingDate"]));

            $start_time = ($start_time < $max_time)
                ? $start_time
                : $max_time - 1;

            $end_time = ($end_time < $max_time)
                ? $end_time
                : $max_time;


            $this->slug_field = "value";

            $attributeData = [
                [
                    "attribute_id" => $this->getAttributeId("name"),
                    "value" => $erp_product_iteration->value["description"]
                ],
                [
                    "attribute_id" => $this->getAttributeId("price"),
                    "value" => ($product->type == Product::SIMPLE_PRODUCT) ? $price_value["unitPrice"] : "",
                ],
                [
                    "attribute_id" => $this->getAttributeId("cost"),
                    "value" => "",
                ],
                [
                    "attribute_id" => $this->getAttributeId("special_from_date"),
                    "value" => ($product->type == Product::SIMPLE_PRODUCT) ? Carbon::parse(date("Y-m-d", $start_time)) : "",
                ],
                [
                    "attribute_id" => $this->getAttributeId("special_to_date"),
                    "value" => ($product->type == Product::SIMPLE_PRODUCT) ? Carbon::parse(date("Y-m-d", $end_time)) : "",
                ],
                [
                    "attribute_id" => $this->getAttributeId("tax_class_id"),
                    "value" => 1,
                ],
                [
                    "attribute_id" => $this->getAttributeId("visibility"),
                    "value" => $visibility,
                ],
                [
                    "attribute_id" => $this->getAttributeId("has_weight"),
                    "value" => 4,
                ],
                [
                    "attribute_id" => $this->getAttributeId("description"),
                    "value" => empty($description) ? $erp_product_iteration->value["description"] : $description,
                ],
                [
                    "attribute_id" => $this->getAttributeId("short_description"),
                    "value" => Str::limit($description, 100),
                ],
                [
                    "attribute_id" => $this->getAttributeId("url_key"),
                    "value" => $this->slugify($erp_product_iteration->value["description"]),
                ],
                [
                    "attribute_id" => $this->getAttributeId("meta_keywords"),
                    "value" => "",
                ],
                [
                    "attribute_id" => $this->getAttributeId("meta_title"),
                    "value" => $erp_product_iteration->value["description"],
                ],
                [
                    "attribute_id" => $this->getAttributeId("meta_description"),
                    "value" => empty($description) ? $erp_product_iteration->value["description"] : $description,
                ],
                [
                    "attribute_id" => $this->getAttributeId("color"),
                    "value" => $this->getOptionValue($product, $variant, "color"),
                ],
                [
                    "attribute_id" => $this->getAttributeId("size"),
                    "value" => $this->getOptionValue($product, $variant, "size"),
                ],
                [
                    "attribute_id" => $this->getAttributeId("erp_features"),
                    "value" => $this->getAttributeValue($product, $erp_product_iteration ,"Features" ),
                ],
                [
                    "attribute_id" => $this->getAttributeId("size_and_care"),
                    "value" => $this->getAttributeValue($product, $erp_product_iteration ,"Size and care" ),
                ],
                [
                    "attribute_id" => $this->getAttributeId("ean_code"),
                    "value" => $ean_code_value,
                ],
                [
                    "attribute_id" => $this->getAttributeId("erp_variant_code"),
                    "value" => ($product->type == Product::SIMPLE_PRODUCT && $product->parent_id) ? $variant["code"] : "",
                ],
                [
                    "attribute_id" => $this->getAttributeId("erp_guid"),
                    "value" => $erp_product_iteration->value["id"],
                ],
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $attributeData;
    }

    /**
     *
     * Update product status.
     */
    public function getProductStatus(object $product, mixed $data): void
    {
        $webAssortments = $this->getDetailCollection("webAssortments", $data["itemNo"]);
        $webAssortments = $this->getValue($webAssortments)
            ->where("colorCode", $data["pfVerticalComponentCode"])
            ->first();
        if (is_array($webAssortments) && array_key_exists("showOnHomepage", $webAssortments)) {
            $status = ($webAssortments["showOnHomepage"] == false) ? 0 : 1;
            $product->update([
                "status" => $status,
            ]);
        }
    }

    /**
     *
     * Create Attribute Color Option Value
     */
    private function createOption(object $erp_product_iteration): void
    {
        try
        {
            $data = [
                "attribute_id" => $this->getAttributeId("color"),
                "name" => $erp_product_iteration->value["colorDescription"],
                "code" => $erp_product_iteration->value["colorCode"],
            ];
            $match = $data;
            unset($match["name"]);
            if (!empty($data["name"]) || !empty($data["code"])) {
                AttributeOption::updateOrCreate($match, $data);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }

    /**
     *
     * Get Attribute option value as per erp data value.
     * Will map erp data value with our system value.
     */
    private function getOptionValue(object $product, mixed $variant_iteration, string $attribute_slug): ?int
    {
        if ($product->type == Product::SIMPLE_PRODUCT && $product->parent_id) {
            $option = $this->getAttributeOptionValue($variant_iteration, $attribute_slug);
        } elseif ($product->type == Product::SIMPLE_PRODUCT && $product->parent_id == null) {
            $variant = $this->getValue($this->getDetailCollection("productVariants", $product->sku))->first();
            $option = $this->getAttributeOptionValue($variant, $attribute_slug);
        }

        return isset($option) ? $option : null;
    }

    /**
     *
     * Get Attribute option value for color and size.
     * Get attrubute option value mapped from system value
     */
    private function getAttributeOptionValue(mixed $variant_iteration, string $attribute_slug): ?int
    {
        try
        {
            switch ($attribute_slug) {
                case "color":
                    $attribute_option = AttributeOption::whereCode($variant_iteration["pfVerticalComponentCode"])->first();
                break;

                case "size":
                    $data = [
                        "attribute_id" => $this->getAttributeId("size"),
                        "name" => $variant_iteration["pfHorizontalComponentCode"],
                    ];
                    if (!empty($data["name"])) {
                        $attribute_option = AttributeOption::updateOrCreate($data);
                    }
                break;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return isset($attribute_option) ? $attribute_option?->id : null;
    }

     /**
     *
     * Concat features and size and care values.
     */
    private function getAttributeValue(?object $product = null, object $erp_product_iteration, string $attribute_name): string
    {
        try
        {
            $attribute_groups = $this->getDetailCollection("attributeGroups", $erp_product_iteration->sku);
            $attach_value = "<ul>";
            if ($attribute_groups->count() > 1) {
                $this->getValue($attribute_groups, function ($value) use (&$attach_value, $attribute_name) {
                    if ($value["attributetype"] == $attribute_name ) {
                        if (!empty($value["translation"])) {
                            $attach_value .= Str::start(Str::finish($value["translation"], ".</li>"), "<li>");
                        }
                    }
                });
            }
            $attach_value = Str::finish($attach_value, "</ul>");
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return $attach_value;
    }
}
