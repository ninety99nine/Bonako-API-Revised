<?php

namespace App\Http\Requests\Models\User;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Services\Api\Ussd\UssdService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $authIsSuperAdmin = auth()->user()->isSuperAdmin();
        $requestIsFromUssdServer = $ussdService->verifyIfRequestFromUssdServer();

        /**
         *  If we are updating a user, then make sure that this user
         *  mobile number does not already exist except if this is
         *  the exact same user that we are updating.
         */
        if( request()->routeIs('auth.user.profile.update') ){

            /**
             *  If the current auth is updating their own profile then
             *  check if the mobile number:
             *
             *  (1) Is unique except for the current auth user.
             *
             *  (2) Has been changed for the current auth user.
             */
            $user = auth()->user();

            $user_id = $user->id;

        }else{

            /**
             *  If the current auth is a Super Admin updating another users profile
             *  then check if the mobile number:
             *
             *  (1) Is unique except for the current user profile being updated.
             *
             *  (2) Has been changed for the current user profile being updated.
             */
            $user = ($user = request()->user) instanceof Model ? $user : User::find($user);

            $user_id = $user->id;

        }

        $uniqueMobileNumber = Rule::unique('users')->ignore($user_id);
        $passwordHasBeenChanged = request()->filled('password') ? !(Hash::check(request()->input('password'), $user->password)) : false;
        $mobileNumberHasBeenChanged = request()->filled('mobile_number') ? (request()->input('mobile_number') != $user->mobile_number) : false;

        return [
            'first_name' => ['bail', 'sometimes', 'required', 'string', 'min:3', 'max:20'],
            'last_name' => ['bail', 'sometimes', 'required', 'string', 'min:3', 'max:20'],
            'mobile_number' => [
                'bail', 'sometimes', 'required', 'string', 'starts_with:267',
                'regex:/^[0-9]+$/', 'size:11', $uniqueMobileNumber
            ],

            /**
             *  When updating the user's password, the user must confirm the new
             *  password that they want to change to. If the request is performed
             *  by the Super Admin, then we do not need to confirm any password
             *  to set a new password.
             */
            'password' => array_merge(
                ['bail', 'sometimes', 'required', 'string', 'min:6'],
                $authIsSuperAdmin ? [] : ['confirmed']
            ),

            /**
             *  When updating the user's password, the user must confirm their
             *  current password before setting their new password. If the
             *  request is performed by the Super Admin, then we do not
             *  need to confirm the current password.
             */
            'current_password' => [
                'bail', 'string', 'min:6', 'current_password:sanctum',
                Rule::requiredIf($passwordHasBeenChanged && !$authIsSuperAdmin)
            ],

            /**
             *  When updating the user's mobile number, the user must provide the
             *  verification code of the new mobile number that they would like
             *  to change to. If the request is performed by the Super Admin,
             *  then we do not need to provide a verification code to verify
             *  this mobile number.
             */
            'verification_code' => [
                'bail', 'string', 'size:6', 'regex:/^[0-9]+$/',
                Rule::requiredIf($mobileNumberHasBeenChanged && !$authIsSuperAdmin),
                Rule::exists('mobile_verifications', 'code')->where('mobile_number', request()->input('mobile_number')),
            ]
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
            'verification_code.required' => 'The verification code is requried to verify ownership of the mobile number ' . request()->input('mobile_number'),
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
