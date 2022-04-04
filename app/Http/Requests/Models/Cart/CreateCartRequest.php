<?php

namespace App\Http\Requests\Models\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CreateCartRequest extends FormRequest
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
        return [

            /*  Cart Products  */
            'cart_products' => ['sometimes', 'array'],
            'cart_products.*.id' => ['bail', 'required', 'integer', 'numeric', 'min:1', 'distinct'],
            'cart_products.*.quantity' => ['bail', 'sometimes', 'required', 'integer', 'numeric', 'min:1'],

            /*  Cart Coupon Codes  */
            'cart_coupon_codes' => ['sometimes', 'array'],
            'cart_coupon_codes.*' => ['string', 'min:1', 'distinct']

        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'cart_products.*.id' => 'cart product id',
            'cart_products.*.quantity' => 'cart product quantity',
        ];
    }
}
