<?php

namespace Modules\Erp\Traits\FilterImport;

use Exception;
use Modules\Erp\Entities\ErpImportDetail;

trait ImportHelper
{
    public function storeErpImport(int $erp_import_id, array $data, ?callable $callback = null): void
    {
        try
        {
            $chunked = array_chunk($data, 100);
            foreach ($chunked as $chunk) {
                $hashes = [];
                $import_data = array_map(function ($item) use ($erp_import_id, &$hashes, $callback) {
                    if (array_key_exists("no", $item)) {
                        $sku = $item["no"];
                    } elseif (array_key_exists("Item_No", $item)) {
                        $sku = $item["Item_No"];
                    } else {
                        $sku = $item["itemNo"];
                    }
                    if ($callback) {
                        $callback($sku);
                    }

                    $item = json_encode($item);
                    $item_hash = md5($erp_import_id.$sku.$item);
                    $hashes[] = $item_hash;
                    return [
                        "erp_import_id" => $erp_import_id,
                        "sku" => $sku,
                        "value" => $item,
                        "hash" => $item_hash,
                        "created_at" => now(),
                        "updated_at" => now(),
                    ];
                }, $chunk);

                $existing_details = ErpImportDetail::whereIn("hash", $hashes)->get()->pluck("hash")->toArray();
                $import_data = array_filter($import_data, function ($item) use ($existing_details) {
                    return !in_array($item["hash"], $existing_details);
                });

                ErpImportDetail::insert($import_data);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}