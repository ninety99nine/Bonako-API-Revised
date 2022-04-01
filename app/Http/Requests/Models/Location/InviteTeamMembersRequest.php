<?php

namespace App\Http\Requests\Models\Location;

use App\Models\Location;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class InviteTeamMembersRequest extends FormRequest
{
    private $permissions;

    public function __construct()
    {

        $this->permissions = collect(Location::PERMISSIONS)->map(fn($permission) => $permission['grant'])->toArray();
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

        return [

            'mobile_numbers' => ['required', 'array'],
            'mobile_numbers.*' => ['bail', 'string', 'starts_with:267', 'regex:/^[0-9]+$/', 'size:11', 'exists:users,mobile_number'],

            'permissions' => ['required', 'array'],
            'permissions.*' => [ 'bail', 'string',  Rule::in($this->permissions)]

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
            'mobile_numbers.*.regex' => 'The mobile number must only contain numbers',
            'mobile_numbers.*.exists' => 'The account using the mobile number does not exist. You can only invite team members with accounts',

            'permissions.*.string' => 'The following permissions are allowed: '.collect($this->permissions)->join(', ', ' or '),
            'permissions.*.in' => 'The following permissions are allowed: '.collect($this->permissions)->join(', ', ' or ')
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
            'mobile_numbers.*' => 'mobile number'
        ];
    }
}
