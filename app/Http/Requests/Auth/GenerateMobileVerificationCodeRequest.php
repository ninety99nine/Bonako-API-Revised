<?php

namespace App\Http\Requests\Auth;

use Illuminate\Validation\Rule;
use App\Models\MobileVerification;
use Illuminate\Foundation\Http\FormRequest;

class GenerateMobileVerificationCodeRequest extends FormRequest
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
        /**
         *  Check if this request is being performed by the Super Admin
         *  on behalf of the user.
         */
        $requestBySuperAdmin = request()->routeIs('user.*');

        return [
            'mobile_number' => array_merge(
                /**
                 *  If the request is performed by the Super Admin, then the
                 *  Super Admin does not require to provide the users mobile
                 *  number. This works well if the request is performed on
                 *  an existing user.
                 */
                $requestBySuperAdmin ? ['sometimes'] : [],
                ['bail', 'required', 'string', 'starts_with:267', 'regex:/^[0-9]+$/', 'size:11'],
            ),
            'purpose' => ['bail', 'required', 'string', Rule::in(MobileVerification::PURPOSE)],
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
            'purpose.in' => 'Answer '.collect(MobileVerification::PURPOSE)->join(', ', ' or ').' for the purpose intended',
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
