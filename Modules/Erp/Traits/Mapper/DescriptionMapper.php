<?php

namespace Modules\Erp\Traits\Mapper;

use Exception;
use Modules\Attribute\Entities\Attribute;
use Modules\Core\Entities\Configuration;
use Modules\Core\Entities\Locale;
use Modules\Core\Services\Pipe;
use Illuminate\Support\Str;
use Modules\Product\Entities\ProductAttribute;

trait DescriptionMapper
{
    use MapperHelper;

    /**
     *
     * Set scope wise value description
     */
    public function getScopeWiseDescription(object $description_value, object $product, object $erp_product_iteration): ?string
    {
        try
        {
            if (($description_value->count() > 0)) {
                $this->storeDescriptionScopeValue($description_value, $product, $erp_product_iteration);
                $description = $this->getValue($description_value)->first()["description"];
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return isset($description) ? $description : "";
    }

    /**
     *
     * Store scope wise description values
     */
    public function storeDescriptionScopeValue(object $description_data, object $product, object $erp_product_iteration): void
    {
        try
        {
            $data = $this->getNameDescriptionScopeValue($description_data);
            $list_products = $this->getDetailCollection("listProducts", $erp_product_iteration->sku);
            $data = array_merge($data,
                $this->getNameDescriptionScopeValue($list_products, "name"),
                $this->getNameDescriptionScopeValue($list_products, "meta_title"),
                $this->getNameDescriptionScopeValue($description_data, "meta_description"),
                $this->getNameDescriptionScopeValue($description_data, "short_description"),
            );

            $chunked = array_chunk($data, 65);
            foreach ($chunked as $chunk) {
                foreach ($chunk as $attributeData) {
                    if (isset($attributeData["attribute_id"])) {
                        $attribute = Attribute::find($attributeData["attribute_id"]);
                        $attribute_type = config("attribute_types")[$attribute->type ?? "string"];

                        $value = $attribute_type::create(["value" => $attributeData["value"]]);

                        $product_attribute_data = [
                            "attribute_id" => $attribute->id,
                            "product_id"=> $product->id,
                            "value_type" => $attribute_type,
                            "value_id" => $value->id,
                            "scope" => $attributeData["scope"],
                            "scope_id" => $attributeData["scope_id"],
                        ];
                        $match = $product_attribute_data;
                        unset($match["value_id"]);
                        ProductAttribute::updateOrCreate($match, $product_attribute_data);
                    }
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    /**
     *
     * Get Scope wise description value
     */
    public function getNameDescriptionScopeValue(object $data, ?string $attribute_slug = "description"): array
    {
        try
        {
            if ($attribute_slug == "description") {
                $getValue = $this->getValue($data)->where("lang", "SVE");
            } elseif ($attribute_slug == "meta_description") {
                $getValue = $this->getValue($data)->where("lang", "SVE");
            } elseif ($attribute_slug == "short_description") {
                $getValue = $this->getValue($data)->where("lang", "SVE");
            } else {
                $getValue = $this->getValue($data)->where("languageCode", "SVE")->unique("languageCode");
            }

            $data = $getValue->map(function ($description) use ($attribute_slug) {
                $locale = Locale::whereCode("sv-SE")->first();
                if ($locale) {
                    $taken = new Pipe(new Configuration());
                    $data = $taken->pipe($taken->value->wherePath("store_locale"))
                        ->pipe($taken->value->whereScope("store"))
                        ->pipe($taken->value->get())
                        ->pipe($this->getfilterConfigurations($taken->value, $locale->id))
                        ->pipe($this->getScopeMappedDescription($taken->value, $description, $attribute_slug))
                        ->pipe($taken->value->toArray())
                        ->value;
                    return $data;
                }
            })->flatten(1)->toArray();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    /**
     *
     * Get scope wise mapped description values
     */
    public function getScopeMappedDescription(object $configurations, array $description, string $attribute_slug): object
    {
        try
        {
            $mapped_description = $configurations->map(function ($configuration) use ($description, $attribute_slug) {
                if ($attribute_slug == "meta_description") {
                    $description = Str::limit($description["description"], 100);
                } elseif ($attribute_slug == "short_description") {
                    $description = Str::limit($description["description"], 100);
                } else {
                    $description = $description["description"];
                }
                return [
                    "attribute_id" => $this->getAttributeId($attribute_slug),
                    "scope" => $configuration->scope,
                    "scope_id" => $configuration->scope_id,
                    "value" => $description,
                ];
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $mapped_description;
    }
}
