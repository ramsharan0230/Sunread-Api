<?php

namespace Modules\Erp\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ErpImportSeederTableSeeder extends Seeder
{
    public function run()
    {
        $data = [
            "webAssortments",
            "listProducts",
            "attributeGroups",
            "salePrices",
            "eanCodes",
            "webInventories",
            "productDescriptions",
            "productVariants",
            "productImages"
        ];

        foreach ( $data as $type )
        {
            DB::table("erp_imports")->insert(["type" => $type, "created_at" => now(), "updated_at" => now()]);
        }
    }
}
