<?php

namespace Modules\Erp\Traits\Mapper;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Core\Services\Pipe;
use Modules\Core\Entities\Store;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Configuration;
use Modules\Attribute\Entities\Attribute;
use Modules\Core\Entities\Currency;
use Modules\Core\Entities\Website;
use Modules\Core\Facades\SiteConfig;
use Modules\Product\Entities\ProductAttribute;

trait PriceMapper
{
    use MapperHelper;

    /**
     *
     * Store price scope wise value.
     */
    public function storeScopeWiseValue(mixed $prices, object $product): bool
    {
        try
        {
            $taken = new Pipe($this->getValue($prices));
            $price_data = $taken->pipe($this->getFilteredProductPrices($taken->value))
                ->pipe($this->getMappedProductPrices($taken->value))
                ->value;

            foreach ($price_data as $price) {
                foreach ($price as $attributeData) {
                    if (isset($attributeData["attribute_id"])) {
                        $attribute = Attribute::find($attributeData["attribute_id"]);
                        $attribute_type = config("attribute_types")[$attribute->type ?? "string"];

                        $value = $attribute_type::create(["value" => $attributeData["value"]]);

                        $product_attribute_data = [
                            "attribute_id" => $attribute->id,
                            "product_id"=> $product->id,
                            "value_type" => $attribute_type,
                            "value_id" => $value->id,
                            "scope" => "channel",
                            "scope_id" => $attributeData["channel_id"],
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

        return true;
    }

    public function getFilteredProductPrices(object $priceValue): object
    {
        try
        {
            $filterPrices = $priceValue->filter(function ($price_value) {
                return $price_value["salesCode"] == "WEB";
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $filterPrices;
    }

    /**
     *
     * Get erp product price value to mapped with specific channel scope.
     */
    public function getMappedProductPrices(object $priceValue): object
    {
        try
        {
            $productPrices = $priceValue->map(function ($price_value) {

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

                $final_array = [];
                $currency_code = (isset($price_value["currencyCode"]) && $price_value["currencyCode"] != '')
                    ? $price_value["currencyCode"]
                    : "SEK";
                $currency =  Currency::whereCode($currency_code)->first();

                if ($currency) {
                    $channel_configurations = $this->getConfigurationScopeValues("channel_currency", "channel", $currency->id);
                    $website_configurations = $this->getConfigurationScopeValues("channel_currency", "website", $currency->id);

                    $channelIds = $this->getChannelIds($channel_configurations, $website_configurations);

                    foreach ($channelIds as $channelId) {
                        $channel_detail = Channel::find($channelId);
                        if (!$channel_detail) {
                            continue;
                        }

                        $adjust_price = SiteConfig::fetch("adjust_price", "channel", $channelId);
                        $adjustment_type = SiteConfig::fetch("adjustment_type", "channel", $channelId);
                        $adjustment_rate = SiteConfig::fetch("adjustment_rate", "channel", $channelId);

                        if ($adjust_price == "yes") {
                            $adjustment_price = ($adjustment_rate/100) * $price_value["unitPrice"];
                            if ($adjustment_type == "deduct") {
                                $price_value["unitPrice"] -= $adjustment_price;
                            } else {
                                $price_value["unitPrice"] += $adjustment_price;
                            }
                        }
                        $data = [
                            [
                                "attribute_id" => $this->getAttributeId("price"),
                                "value" => $price_value["unitPrice"],
                                "channel_id" => $channel_detail->id,
                            ],
                            [
                                "attribute_id" => $this->getAttributeId("special_from_date"),
                                "value" => Carbon::parse(date("Y-m-d", $start_time)),
                                "channel_id" => $channel_detail->id,
                            ],
                            [
                                "attribute_id" => $this->getAttributeId("special_to_date"),
                                "value" => Carbon::parse(date("Y-m-d", $end_time)),
                                "channel_id" => $channel_detail->id,
                            ],
                        ];
                        $final_array = array_merge($final_array, $data);
                    }
                }

                return $final_array;
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $productPrices;
    }

    public function getChannelIds(object $channel_configurations, object $website_configurations): array
    {
        try
        {
            $channel_ids = [];
            foreach ($channel_configurations as $channel_config) {
                $channel_ids[] = $channel_config->scope_id;
            }
            foreach ($website_configurations as $config) {
                $website = Website::with("channels")->find($config->scope_id);
                if ($website) {
                    $channel_ids = array_unique(array_merge($channel_ids, $website->channels->pluck("id")->toArray()));
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $channel_ids;
    }
}
