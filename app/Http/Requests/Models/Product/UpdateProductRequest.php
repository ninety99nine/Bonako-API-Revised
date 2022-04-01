<?php

namespace App\Http\Requests\Models\Product;

use App\Models\Product;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
        $moneyRules = ['bail', 'required', 'string', 'regex:/^\d+(\.\d{1,2})?$/'];

        return [

            /*  General Information  */
            'name' => [
                'bail', 'required', 'string', 'min:3', 'max:60',
                /**
                 *  Make sure that this product name does not
                 *  already exist for the same location
                 *  (Except for the same product)
                 */
                Rule::unique('products')->where('location_id', request()->product->location_id)->ignore(request()->product->id)
            ],
            'visible' => ['bail', 'sometimes', 'required', 'boolean'],
            'show_description' => ['bail', 'sometimes', 'required', 'boolean'],
            'description' => ['bail', 'sometimes', 'required', 'string', 'min:3', 'max:500'],

            /*  Tracking Information  */
            'sku' => ['bail', 'sometimes', 'required', 'string', 'min:3', 'max:100'],
            'barcode' => ['bail', 'sometimes', 'required', 'string', 'min:3', 'max:100'],

            /*  Variation Information
             *
             *  variant_attributes: Exclude from the request data returned
             *      - Only modifiable on creation of variations
             */
            'allow_variations' => ['bail', 'sometimes', 'required', 'boolean'],
            'variant_attributes' => ['exclude'],

            /*  Pricing Information
             *
             *  currency: Exclude from the request data returned
             *      - The currency is derived from the store itself
            */
            'is_free' => ['bail', 'sometimes', 'required', 'boolean'],
            'currency' => ['exclude'],
            'unit_regular_price' => $moneyRules,
            'unit_sale_price' => collect($moneyRules)->add('sometimes')->toArray(),
            'unit_cost' => collect($moneyRules)->add('sometimes')->toArray(),

            /*  Quantity Information  */
            'allowed_quantity_per_order' => ['bail', 'sometimes', 'required', 'string', Rule::in(Product::ALLOWED_QUANTITY_PER_ORDER)],
            'maximum_allowed_quantity_per_order' => [
                'bail', 'sometimes', 'integer', 'numeric', 'min:2', 'max:65535',
                Rule::requiredIf(fn() => request()->input('allowed_quantity_per_order') === 'limited')
            ],

            /*  Stock Information  */
            'stock_quantity_type' => ['bail', 'sometimes', 'required', 'string', Rule::in(Product::STOCK_QUANTITY_TYPE)],
            'stock_quantity' => [
                'bail', 'sometimes', 'integer', 'numeric', 'min:2', 'max:16777215',
                Rule::requiredIf(fn() => request()->input('stock_quantity_type') === 'limited')
            ],

            /*  Arrangement Information  */
            'arrangement' => ['bail', 'sometimes', 'required', 'integer', 'numeric', 'min:1', 'max:255'],

        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'allowed_quantity_per_order.string' => 'Answer '.collect(Product::ALLOWED_QUANTITY_PER_ORDER)->join(', ', ' or ').' to indicate the allowed quantity per order',
            'allowed_quantity_per_order.in' => 'Answer '.collect(Product::ALLOWED_QUANTITY_PER_ORDER)->join(', ', ' or ').' to indicate the allowed quantity per order',
            'stock_quantity_type.string' => 'Answer '.collect(Product::STOCK_QUANTITY_TYPE)->join(', ', ' or ').' to indicate the stock quantity type',
            'stock_quantity_type.in' => 'Answer '.collect(Product::STOCK_QUANTITY_TYPE)->join(', ', ' or ').' to indicate the stock quantity type',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }
}
