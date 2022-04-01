<?php

namespace App\Http\Requests\Models\User;

use Illuminate\Validation\Rule;
use App\Services\Api\Ussd\UssdService;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
        $authIsSuperAdmin = ($user = auth()->user()) ? $user->isSuperAdmin() : false;
        $requestIsFromUssdServer = $ussdService->verifyIfRequestFromUssdServer();

        return [
            'first_name' => ['bail', 'required', 'string', 'min:3', 'max:20'],
            'last_name' => ['bail', 'required', 'string', 'min:3', 'max:20'],
            'mobile_number' => ['bail', 'required', 'string', 'starts_with:267', 'regex:/^[0-9]+$/', 'size:11', 'unique:users,mobile_number'],
            /**
             *  Since the creation of an account can be done by any user creating their
             *  own profile, or by a Super Admin creating a profile on be-half of other
             *  users, we need to check if we must require a password and verification
             *  code from the user. We require the password and verification code if:
             *
             *  (1) The request is not from the USSD server (e.g this action is being
             *  performed by a person either using the web-app or the mobile-app).
             *  But if the request is coming from the USSD server e.g a customer
             *  registering a new account, then we dont need the password and
             *  verification code (since they are not required to use a
             *  web-app or mobile-app to consume the service)
             *
             *  and ...
             *
             *  (2) The person performing the request is not a Super Admin.
             *
             *  If both these cases pass, then we must require the user to provide
             *  a password and a verification code to confirm the ownership of the
             *  mobile number before creating this account.
             *
             *  NOTE: If the request is performed by the Super Admin, then we do
             *  not need to confirm the password.
             */
            'password' => array_merge(
                ['bail', Rule::requiredIf(!$requestIsFromUssdServer && !$authIsSuperAdmin), 'string', 'min:6', ],
                $authIsSuperAdmin ? [] : ['confirmed']
            ),
            'verification_code' => [
                'bail', 'string', 'size:6', 'regex:/^[0-9]+$/',
                Rule::requiredIf(!$requestIsFromUssdServer && !$authIsSuperAdmin),
                Rule::exists('mobile_verifications', 'code')->where('mobile_number', request()->input('mobile_number')),
            ],
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
