<?php

namespace Modules\Core\Services;

use Exception;
use Modules\Core\Facades\SiteConfig;

class PriceFormatter {

    public function get(mixed $price, int $scope_id, ?string $scope = "channel"): ?string
    {
        try
        {
            $data = $this->getConfigurationData($scope, $scope_id);
            $group_separator_value = $this->group_separator($data->group_separator);
            $decimal_separator_value = $this->decimal_separator($data->decimal_separator);

            $separator_price = number_format($price, 2, $decimal_separator_value, $group_separator_value);
            if ($price < 0) {
                $separator_price = substr($separator_price, 1);
                $formatted_price = $this->minusSignPosition($data->minus, $separator_price, $data->minus_position, $data->currency_symbol, $data->currency_position);
            } else {
                $formatted_price = $this->symbolPosition($data->currency_symbol, $separator_price, $data->currency_position);
            }
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }
        
        return $formatted_price;
    }

    public function getConfigurationData(?string $scope, ?int $scope_id): ?object
    {
        try
        {
            $currency = SiteConfig::fetch("channel_currency", $scope, $scope_id);
            $currency_position = SiteConfig::fetch("symbol_position", $scope, $scope_id);
            $group_separator = SiteConfig::fetch("group_seperator", $scope, $scope_id);
            $decimal_separator = SiteConfig::fetch("decimal_seperator", $scope, $scope_id);
            $minus = SiteConfig::fetch("minus_sign", $scope, $scope_id); 
            $minus_position = SiteConfig::fetch("minus_sign_position", $scope, $scope_id);

            $data = [
                "currency_symbol" => optional($currency)->code,
                "currency_position" => $currency_position,
                "group_separator" => $group_separator,
                "decimal_separator" => $decimal_separator,
                "minus" => $minus,
                "minus_position" => $minus_position,
            ];

        }
        catch ( Exception $exception )
        {
            throw $exception;
        }
        
        return (object) $data;
    }

    public function minusSignPosition(
        ?string $minus_symbol, 
        mixed $price, 
        ?int $minus_position, 
        ?string $currency_symbol, 
        ?int $currency_position
    ): ?string {
        try
        {
            switch ($minus_position) {
                case ($currency_position == 1  && ($minus_position == 1 || $minus_position == 4) ):
                    $formatted_price = "{$currency_symbol}{$minus_symbol}{$price}";
                    break;
                case ($currency_position == 1 && $minus_position == 2):
                    $formatted_price = "{$minus_symbol}{$currency_symbol}{$price}";
                    break;
                case ($currency_position == 1 && $minus_position == 3):
                    $formatted_price = "{$currency_symbol}{$price}{$minus_symbol}";
                    break;       
                case ($currency_position == 2 && ($minus_position == 1 || $minus_position == 4 )):
                    $formatted_price = "{$currency_symbol}{$minus_symbol} {$price}";
                    break;
                case ($currency_position == 2 && $minus_position == 2):
                    $formatted_price = "{$minus_symbol}{$currency_symbol} {$price}";
                    break;
                case ($currency_position == 2 && $minus_position == 3):
                    $formatted_price = "{$currency_symbol} {$price}{$minus_symbol}";
                    break;             
                case ($currency_position == 3 && $minus_position == 1 ):
                    $formatted_price = "{$minus_symbol}{$price}{$currency_symbol}";
                    break;
                case ($currency_position == 3 && ($minus_position == 2 || $minus_position == 3)):
                    $formatted_price = "{$price}{$minus_symbol}{$currency_symbol}";
                    break;
                case ($currency_position == 3 && $minus_position == 4):
                    $formatted_price = "{$price}{$currency_symbol}{$minus_symbol}";
                    break;
                case ($currency_position == 4 && $minus_position == 1 ):
                    $formatted_price = "{$minus_symbol}{$price} {$currency_symbol}";
                    break;
                case ($currency_position == 4 && ($minus_position == 2 || $minus_position == 3)):
                    $formatted_price = "{$price}{$minus_symbol} {$currency_symbol}";
                    break;
                case ($currency_position == 4 && $minus_position == 4):
                    $formatted_price = "{$price} {$currency_symbol}{$minus_symbol}";
                    break;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $formatted_price;
    }

    public function symbolPosition(?string $symbol, mixed $price, ?int $currency_position): ?string
    {
        try
        {
            switch ($currency_position) {
                case 1:
                    $position = "{$symbol}{$price}";
                    break;

                case 2:
                    $position = "{$symbol} {$price}";
                    break;

                case 3:
                    $position = "{$price}{$symbol}";
                    break;

                case 4:
                    $position = "{$price} {$symbol} ";
                    break;

                default:
                    $position = null;
                    break;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $position;
    }

    public function group_separator(?int $group_separator): ?string
    {
        try
        {
            switch ($group_separator) {
                case 1:
                    $seperator =  ",";
                    break;

                case 2:
                    $seperator =  ".";
                    break;

                case 3:
                    $seperator =  " ";
                    break;

                case 4:
                    $seperator =  null;
                    break;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $seperator;
    }

    public function decimal_separator(?int $decimal_separator): ?string
    {
        try
        {
            switch ($decimal_separator) {
                case 1:
                    $seperator = ",";
                    break;

                case 2:
                    $seperator = ".";
                    break;

                default:
                    $seperator = null;
                    break;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $seperator;
    }

    public function getForList(mixed $price, array $price_data): ?string 
    {
        try
        {
            $separator_price = number_format($price, 2, $price_data["decimal_separator_value"], $price_data["group_separator_value"]);
            if ($price < 0) {
                $separator_price = substr($separator_price, 1);
                $formatted_price = str_replace("price", $separator_price ?? 0, $price_data["withminus"]);
            } else {
                $formatted_price = str_replace("price", $price ?? 0, $price_data["withoutminus"]);
            }
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }
        
        return $formatted_price;
    }
}