<?php

namespace App\Http\Requests\Models\Location;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateLocationRequest extends FormRequest
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
            'name' => ['bail', 'sometimes', 'required', 'string', 'min:3', 'max:20',
                /**
                 *  If we are creating a location on an existing store, then make sure
                 *  that this location name does not already exist for the same store
                 */
                ($store = request()->store) ? Rule::unique('locations')->where('store_id', $store->id) : ''
            ],
            'call_to_action' => ['bail', 'required', 'string', 'min:3', 'max:20'],
            'online' => ['bail', 'sometimes', 'required', 'boolean'],
            'offline_message' => ['bail', 'sometimes', 'required', 'string', 'min:3', 'max:200'],
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
            'name.unique' => 'The location name already exists for this store'
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
