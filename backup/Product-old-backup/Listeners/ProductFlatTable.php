<?php

namespace Modules\Product\Listeners;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Core\Entities\Locale;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductAttributeValue;
use Modules\Product\Entities\ProductFlat;
use Modules\Product\Helpers\ProductType;


/**
 * Product Flat Event handler
 *
 */
class ProductFlatTable
{
    /**
     * @var object
     */
    public $attributeTypeFields = [
        'text' => 'text',
        'textarea' => 'text',
        'price' => 'float',
        'boolean' => 'boolean',
        'select' => 'integer',
        'multiselect' => 'text',
        'datetime' => 'datetime',
        'date' => 'date',
        'file' => 'text',
        'image' => 'text',
        'checkbox' => 'text'
    ];


    /**
     * After the attribute is created
     *
     * @param $attribute
     * @return bool
     */
    public function afterAttributeCreatedUpdated($attribute)
    {

        if (!$attribute->use_in_flat) {
            $this->afterAttributeDeleted($attribute);
            return false;
        }

        if (!Schema::hasColumn('product_flat', $attribute->slug)) {
            Schema::table('product_flat', function (Blueprint $table) use ($attribute) {
                $table->{$this->attributeTypeFields[$attribute->type]}($attribute->slug)->nullable();

                if ($attribute->type == 'select' || $attribute->type == 'multiselect') {
                    $table->string($attribute->slug . '_label')->nullable();
                }
            });
        }
    }

    public function afterAttributeDeleted($attribute)
    {
        if (Schema::hasColumn('product_flat', $attribute->slug)) {
            Schema::table('product_flat', function (Blueprint $table) use ($attribute) {
                $table->dropColumn($attribute->slug);

                if ($attribute->type == 'select' || $attribute->type == 'multiselect') {
                    if(Schema::hasColumn('product_flat', $attribute->slug.'_label'))
                        $table->dropColumn($attribute->slug . '_label');

                }
            });
        }
    }

    /**
     * Creates product flat
     *
     * @param Product $product
     * @return void
     */
    public function afterProductCreatedUpdated($product)
    {
        $this->createFlat($product);

        if (ProductType::hasVariants($product->type)) {
            foreach ($product->variants()->get() as $variant) {

                $this->createFlat($variant, $product);
            }
        }
    }

    /**
     * Creates product flat
     *
     * @param Product $product
     * @param Product $parentProduct
     * @return void
     */
    public function createFlat($product, $parentProduct = null)
    {

        static $familyAttributes = [];

        static $superAttributes = [];

        $locale = config('locales.lang')? config('locales.lang'):config('app.locale');
        $locale = Locale::where('code', $locale)->first();

        $productFlat = ProductFlat::firstOrNew([
            'product_id' => $product->id,
            'locale' => $locale->code
        ]);


        if (!array_key_exists($product->attribute_family->id, $familyAttributes))
            $familyAttributes[$product->attribute_family->id] = $product->associatedAttributes();


        if ($parentProduct && !array_key_exists($parentProduct->id, $superAttributes))
            $superAttributes[$parentProduct->id] = $parentProduct->super_attributes()->pluck('slug')->toArray();

        foreach ($familyAttributes[$product->attribute_family->id] as $attribute) {

            if ($parentProduct && !in_array($attribute->slug, array_merge($superAttributes[$parentProduct->id], ['sku', 'name', 'price', 'weight', 'status'])))
                continue;

            if (!Schema::hasColumn('product_flat', $attribute->slug))
                continue;


            $productAttributeValue = $product->attribute_values()->where('attribute_id', $attribute->id)->first();

            $productFlat->{$attribute->slug} = isset($productAttributeValue)? $productAttributeValue[ProductAttributeValue::$attributeTypeFields[$attribute->type]] ?? null:null;

            if(isset($productAttributeValue)){
                if ($attribute->type == 'select') {
                    $attribute_value = $productAttributeValue[ProductAttributeValue::$attributeTypeFields[$attribute->type]];
                    $attributeOption = AttributeOption::findOrFail($attribute_value);

                    if ($attributeOption) {
                        if ($attributeOptionTranslation = $attributeOption->translate($locale->code)) {
                            $productFlat->{$attribute->slug . '_label'} = $attributeOptionTranslation->name;
                        } else {
                            $productFlat->{$attribute->slug . '_label'} = $attributeOption->name;
                        }
                    }

                }
            }
        }

        $productFlat->created_at = $product->created_at;
        $productFlat->updated_at = $product->updated_at;

        //upload image
        $images = $product->images;
        if($images){
            $thumbnail = $images->where( 'thumbnail', 1)->first();
            if($thumbnail){
                $productFlat->thumbnail = $thumbnail->path;
            }
        }


        if ($parentProduct) {
            $parentProductFlat = ProductFlat::where('product_id', $parentProduct->id)->first();

            if ($parentProductFlat)
                $productFlat->parent_id = $parentProductFlat->id;
        }
        $productFlat->slug = $product->slug;
        $productFlat->save();
    }


}
