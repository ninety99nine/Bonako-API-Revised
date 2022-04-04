<?php

namespace App\Http\Requests\Models\Cart;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Models\Cart\CreateCartRequest;

class UpdateCartRequest extends FormRequest
{
    /**
     * Return the create cart form request
     *
     * @return CreateCartRequest
     */
    public function createCartRequest()
    {
        return resolve(CreateCartRequest::class);
    }

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
        return collect( $this->createCartRequest()->rules() )->except('session_id')->all();
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return $this->createCartRequest()->messages();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return $this->createCartRequest()->attributes();
    }
}
