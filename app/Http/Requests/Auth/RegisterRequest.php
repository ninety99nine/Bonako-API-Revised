<?php

namespace App\Http\Requests\Auth;

use Illuminate\Validation\Rule;
use App\Services\Api\Ussd\UssdService;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
    public function rules(UssdService $ussdService)
    {
        $requestIsNotFromUssdServer = ($ussdService->verifyIfRequestFromUssdServer() == false);

        return [
            'first_name' => ['bail', 'required', 'string', 'min:3', 'max:20'],
            'last_name' => ['bail', 'required', 'string', 'min:3', 'max:20'],
            'password' => ['bail', Rule::requiredIf($requestIsNotFromUssdServer), 'string', 'min:6', 'confirmed'],
            'verification_code' => ['bail', Rule::requiredIf($requestIsNotFromUssdServer), 'string', 'size:6', 'regex:/^[0-9]+$/'],
            'mobile_number' => ['bail', 'required', 'string', 'starts_with:267', 'regex:/^[0-9]+$/', 'size:11', 'unique:users,mobile_number'],
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
            'mobile_number.unique' => 'An account using the mobile number '.request()->input('mobile_number').' already exists.',
            'verification_code.regex' => 'The verification code must only contain numbers'
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
