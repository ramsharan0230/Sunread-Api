<?php

namespace Modules\Tax\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Tax\Entities\CustomerTaxGroup;
use Modules\Tax\Entities\ProductTaxGroup;
use Modules\Tax\Entities\TaxRate;
use Modules\Tax\Entities\TaxRule;

class TaxRuleTableSeeder extends Seeder
{
    public function run(): void
    {
        $product_tax_group_ids = ProductTaxGroup::get()->pluck("id")->toArray();
        $customer_tax_group_ids = CustomerTaxGroup::get()->pluck("id")->toArray();
        $tax_rate_ids = TaxRate::get()->pluck("id")->toArray();
        
        $tax_rule = TaxRule::factory()->create();

        $tax_rule->tax_rates()->sync($tax_rate_ids);
        $tax_rule->product_tax_groups()->sync($product_tax_group_ids);
        $tax_rule->customer_tax_groups()->sync($customer_tax_group_ids);
    }
}
