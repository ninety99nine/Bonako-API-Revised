<?php

namespace App\Http\Requests\Auth;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //  Everyone is authorized to make this request
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
            'mobile_number' => ['bail', 'required', 'string', 'starts_with:267', 'regex:/^[0-9]+$/', 'size:11', 'exists:users,mobile_number'],
            'verification_code' => ['bail', 'required', 'string', 'size:6', 'regex:/^[0-9]+$/',
                Rule::exists('mobile_verifications', 'code')->where('mobile_number', request()->input('mobile_number')),
            ],
            'password' => ['bail', 'required', 'string', 'min:6', 'confirmed'],
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
            'mobile_number.regex' => 'The mobile number must only contain numbers',
            'mobile_number.exists' => 'The account using the mobile number '.request()->input('mobile_number').' does not exist.',
            'verification_code.regex' => 'The verification code must only contain numbers',
            'verification_code.exists' => 'The verification code is not valid',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [

        ];
    }
}
