<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

use Modules\Attribute\Entities\AttributeFamily;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductAttributeValue;


class ProductForm extends FormRequest
{
    /**
     * AttributeFamilyRepository object
     *
     * @var array
     */
    protected $attributeFamily;

    /**
     * ProductRepository object
     *
     * @var array
     */
   // protected $product;

    /**
     * ProductAttributeValueRepository object
     *
     * @var array
     */
    protected $attributeValue;

    /**
     * Create a new controller instance.
     *
     * @param AttributeFamily $attributeFamily
     * @param Product $product
     * @param \Modules\Product\Http\Requests\ProductAttributeValue $productAttributeValue
     */
//    public function __construct(AttributeFamily $attributeFamily, Product $product, ProductAttributeValue $productAttributeValue)
//    {
//        $this->attributeFamily = $attributeFamily;
//
//        $this->product = $product;
//
//       $this->attributeValue = $productAttributeValue;
//    }

    protected $rules;

    /**
     * Determine if the product is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $product = Product::findOrFail($this->id);


        $this->rules = array_merge($product->getTypeInstance()->getTypeValidationRules(), [
            'sku' => ['required', 'unique:products,sku,' . $this->id],
            'images.*' => 'mimes:jpeg,jpg,bmp,png',
            'special_price_from' => 'nullable|date',
            'special_price_to' => 'nullable|date|after_or_equal:special_price_from',
            'special_price' => ['nullable', 'decimal']
        ]);


        foreach ($product->getEditableAttributes() as $attribute) {
            if ($attribute->slug == 'sku' || $attribute->type == 'boolean')
                continue;

            $validations = [];

            if (! isset($this->rules[$attribute->slug]))
                array_push($validations, $attribute->is_required ? 'required' : 'nullable');
            else
                $validations = $this->rules[$attribute->slug];

            if ($attribute->type == 'text' && $attribute->validation) {
                array_push($validations, $attribute->validation == 'decimal' ? 'decimal' : $attribute->validation);
            }

            if ($attribute->type == 'price')
                array_push($validations, 'decimal');

            if ($attribute->is_unique) {
                array_push($validations, function ($field, $value, $fail) use ($attribute) {
                    $column = ProductAttributeValue::$attributeTypeFields[$attribute->type];

                    if (! $this->attributeValue->isValueUnique($this->id, $attribute->id, $column, request($attribute->slug)))
                        $fail('The :attribute has already been taken.');
                });
            }

            $this->rules[$attribute->slug] = $validations;
        }

        return $this->rules;
    }

    /**
     * Custom message for validation
     *
     * @return array
    */
    public function messages()
    {
        return [
            'variants.*.sku.unique' => 'The sku has already been taken.',
        ];
    }

    /**
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator) {
        throw new HttpResponseException(
            response()->json([
                'status' => false,
                'messages' => $validator->errors()->all()
            ], 422)
        );
    }
}
