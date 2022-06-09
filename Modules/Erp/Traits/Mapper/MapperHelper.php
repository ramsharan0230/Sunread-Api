<?php

namespace Modules\Erp\Traits\Mapper;

use Exception;
use Facade\Ignition\DumpRecorder\Dump;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Core\Entities\Configuration;
use Modules\Core\Services\Pipe;
use Modules\Core\Traits\Cacheable;
use Modules\Erp\Entities\ErpImport;
use Modules\Erp\Traits\Webhook\HasErpWebhookHelper;
use Symfony\Component\HttpFoundation\Response;

trait MapperHelper
{
    use Cacheable;
    use HasErpWebhookHelper;

    /**
     *
     * Helper function to get value form erp detail value field.
     */
    private function getValue(mixed $values, callable $callback = null): mixed
    {
        try
        {
            $data = $values->map(function ($value) use ($callback) {
                $value = (array) $value;
                if ($callback) {
                    $data = $callback( (array) $value["value"]);
                }
                return ($callback)
                    ? $data
                    : (array) $value["value"];
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    /**
     *
     * Get Erp detail collection by import type
     */
    public function getDetailCollection(string $slug, string $sku): object
    {
        try
        {
            $hash = md5("{$slug}{$sku}");
            $rows = ErpImport::query();
            if (!$this->fetch_from_api) {
                $details = $this->storeCache("erp_details_{$slug}", $hash, function () use ($rows, $slug, $sku) {
                    return $rows->where("type", $slug)
                        ->first()
                        ->erp_import_details()
                        ->where("sku", $sku)
                        ->get();
                });
            } else {
                $details = $this->getErpApiData($slug, $sku);
                if (array_key_exists("value", $details)) {
                    unset($details["@odata.context"]);
                    $details = array_map(function ($detail) use ($sku) {
                        return [
                            "sku" => $sku,
                            "value" => $detail,
                        ];
                    }, $details["value"]);
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return collect($details);
    }

    public function getErpApiData(string $slug, string $sku): array
    {
        try
        {
            if ($slug == "productDescriptions") {
                $descriptions = $this->getErpDescriptionData($sku);
                $data["value"] = $descriptions;
            } elseif ($slug == "productImages") {
                $data = [];
            } else {
                $data = $this->fetchErpData($slug, $sku);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function fetchErpData(string $slug, string $sku): array
    {
        try
        {
            $filter = "\$filter=";
            switch ($slug) {
                case "webAssortments":
                    $url = "webAssortments?{$filter}itemNo eq '{$sku}'";
                break;

                case "attributeGroups":
                    $url = "webItemAttributeGroups?{$filter}itemNo eq '{$sku}'";
                break;

                case "salePrices":
                    $url = "webSalesPrices?{$filter}itemNo eq '{$sku}'";
                break;

                case "eanCodes":
                    $url = "webItemCrossReferences?{$filter}itemNo eq '{$sku}'";
                break;

                case "webInventories":
                    $url = "webInventorys?{$filter}Item_No eq '{$sku}'";
                break;

                case "productVariants":
                    $url = "webItemVariants?{$filter}itemNo eq '{$sku}'";
                break;

                case "listProducts":
                    $url = "webItems?{$filter}no eq '{$sku}'";
                break;
            }

            $response = $this->httpGet($url)->throw()->json();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $response;
    }

    public function getErpDescriptionData(string $sku): array
    {
        try
        {
            $data = [];
            foreach (["ENU", "SVE"] as $langCode) {
                $url = "webExtendedTexts(tableName='Item',No='{$sku}',Language_Code='{$langCode}',textNo=1)/Data";
                $response = $this->httpGet($url);
                if ($response->status() == Response::HTTP_OK) {
                    $data[] = [
                        "no" => $sku,
                        "lang" => $langCode,
                        "description" => $response->body(),
                    ];
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    /**
     *
     * Get Attribute Id by attribute slug
     */
    public function getAttributeId(string $slug): ?int
    {
        try
        {
            $hash = md5($slug);
            $attribute_id = $this->storeCache("erp_attribute_{$slug}", $hash, function () use ($slug) {
                return Attribute::whereSlug($slug)->first()?->id;
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $attribute_id;
    }

    /**
     *
     * Get Attribute Option Id by code
     */
    public function getAttributeOptionId(string $code): ?int
    {
        try
        {
            $hash = md5($code);
            $attribute_option_id = $this->storeCache("erp_attribute_option_{$code}", $hash, function () use ($code) {
                return AttributeOption::whereCode($code)->first()?->id;
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $attribute_option_id;
    }

    /**
     *
     * Get any configuration data based scope
     */
    public function getConfigurationScopeValues(string $path, string $scope, ?int $filter_id, ?callable $callback = null): object
    {
        try
        {
            $taken = new Pipe(new Configuration());
            $data = $taken->pipe($taken->value->wherePath($path))
                ->pipe($taken->value->whereScope($scope))
                ->pipe($taken->value->get())
                ->pipe($this->getfilterConfigurations($taken->value, $filter_id, $callback))
                ->value;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    /**
     *
     * Get filtered configuration data
     */
    private function getfilterConfigurations(object $configurations, ?int $filter_id, ?callable $callback = null): object
    {
        try
        {
            $filtered_configurations = $configurations->filter(function ($configuration) use ($filter_id, $callback) {
                $condition = $callback ? $callback($configuration) : ($configuration->value == $filter_id);
                return $condition;
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $filtered_configurations;
    }
}
