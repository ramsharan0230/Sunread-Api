<?php

namespace Modules\Erp\Traits;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Modules\Erp\Entities\ErpImport;
use Illuminate\Support\Facades\Http;
use Modules\Erp\Entities\ErpImportDetail;
use Modules\Erp\Jobs\ImportErpData;
use Symfony\Component\HttpFoundation\Response;

trait HasErpMapper
{
    protected $url = "https://bc.sportmanship.se:7148/sportmanshipbcprodapi/api/NaviproAB/web/beta/";

    private function basicAuth(): PendingRequest
    {
        return Http::withBasicAuth(env("ERP_API_USERNAME"), env("ERP_API_PASSWORD"));
    }

    public function erpImport(string $type, string $url, ?string $skip_token = null): Collection
    {
        try
        {
            $response_json = $this->getResponse($type, $url, $skip_token, function ($response_json_array, $new_skip_token) use ($type) {
                ImportErpData::dispatch($type, $response_json_array)->onQueue("erp");
                get_class()::dispatch($new_skip_token)->onQueue("erp");
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $this->generateCollection($response_json, $type);
    }

    public function getResponse(string $type, string $url, ?string $skip_token = null, callable $callback = null): array
    {
        try
        {
            $response = $this->basicAuth()->get($url, $skip_token);
            if ($response->status() == Response::HTTP_OK) {
                $response_json_array = $response->json()["value"];
                $skip_token = $this->skipToken(end($response_json_array), $type);

                if (!empty($response_json_array) && array_key_exists("@odata.nextLink", $response->json())) {
                    if ($callback) {
                        $callback($response_json_array, $skip_token);
                    }
                } else {
                    ErpImport::whereType($type)->first()?->update(["status" => 1]);
                    if ($type == "listProducts") {
                        ErpImport::whereType("productDescriptions")->first()->update(["status" => 1]);
                    }
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $response_json_array ?? [];
    }

    private function skipToken(array $data, string $type): string
    {
        try
        {
            $data = (object) $data;
            $skipToken = "\$skiptoken=";

            switch ($type) {
                case 'webAssortments':
                    $token = "{$skipToken}'{$data->itemNo}','SR','{$data->colorCode}'";
                    break;

                case 'listProducts':
                    $filter = "\$filter=webAssortmentColor_Description ne 'SAMPLE'  and webAssortmentWeb_Setup eq 'SR' and webAssortmentWeb_Active eq true";
                    $token = "{$skipToken}'{$data->no}','{$data->webAssortmentWeb_Setup}','{$data->webAssortmentColor_Code}','{$data->languageCode}','{$data->auxiliaryIndex1}','{$data->auxiliaryIndex2}','{$data->auxiliaryIndex3}','{$data->auxiliaryIndex4}'";
                    break;

                case 'attributeGroups':
                    $token = "{$skipToken}'{$data->itemNo}','{$data->sortKey}','{$data->groupCode}','{$data->attributeID}','{$data->name}','{$data->auxiliaryIndex1}'";
                    break;

                case 'productVariants':
                    $token = "{$skipToken}'{$data->pfVerticalComponentCode}','{$data->itemNo}'";
                    break;

                case 'salePrices':
                    $token = "{$skipToken}'{$data->itemNo}','{$data->salesCode}','{$data->currencyCode}','{$data->startingDate}','{$data->salesType}','{$data->minimumQuantity}','{$data->unitofMeasureCode}','{$data->variantCode}'";
                    break;

                case 'eanCodes':
                    $token = "{$skipToken}'{$data->itemNo}','{$data->variantCode}','{$data->unitofMeasure}','{$data->crossReferenceType}','{$data->crossReferenceTypeNo}','{$data->crossReferenceNo}'";
                    break;

                case 'webInventories':
                    $token = "{$skipToken}'{$data->Item_No}','{$data->Code}'";
                    break;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $token;
    }

    public function filterToken(string $type, ?string $sku = null): string
    {
        try
        {
            $filter = "\$filter=";
            switch ($type) {
                case 'webAssortments':
                    $filter_token = ($sku) ? "{$filter}itemNo eq '{$sku}'" : "";
                    break;

                case 'listProducts':
                    $filter_token = "{$filter}webAssortmentColor_Description ne 'SAMPLE'  and webAssortmentWeb_Setup eq 'SR' and webAssortmentWeb_Active eq true&";
                    break;

                case 'attributeGroups':
                    $filter_token = ($sku) ? "{$filter}itemNo eq '{$sku}'" : "";
                    break;

                case 'productVariants':
                    $filter_token = ($sku) ? "{$filter}itemNo eq '{$sku}'" : "";
                    break;

                case 'salePrices':
                    $filter_token = ($sku) ? "{$filter}itemNo eq '{$sku}'" : "";
                    break;

                case 'eanCodes':
                    $filter_token = ($sku) ? "{$filter}itemNo eq '{$sku}'" : "";
                    break;

                case 'webInventories':
                    $filter_token = ($sku) ? "{$filter}Item_No eq '{$sku}'" : "";
                    break;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $filter_token;
    }

    private function generateCollection(array $data, string $type): object
    {
        try
        {
            switch ($type) {
                case 'listProducts':
                    $collection = collect($data)->where("webAssortmentWeb_Active", true)
                        ->where("webAssortmentWeb_Setup", "SR")
                        ->chunk(50)
                        ->flatten(1);
                    break;

                case 'webAssortments':
                    $collection = collect($data)->where("webActive", true)
                        ->where("webSetup", "SR")
                        ->chunk(50)
                        ->flatten(1);
                    break;

                default :
                    $collection = collect($data)
                        ->chunk(50)
                        ->flatten(1);
                    break;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $collection;
    }
}
