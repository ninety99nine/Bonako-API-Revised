<?php

namespace App\Http\Requests\Models\Location;

use App\Models\Location;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamMemberPermissionsRequest extends FormRequest
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
        return [];
    }
}
