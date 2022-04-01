<?php

namespace App\Http\Requests\Models\Location;

use Illuminate\Validation\Rule;
use App\Models\Pivots\LocationUser;
use Illuminate\Foundation\Http\FormRequest;

class ShowTeamMembersRequest extends FormRequest
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
            'accepted_invitation' => ['sometimes', 'string', Rule::in(LocationUser::CLOSED_ANSWERS)],
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
            'accepted_invitation.string' => 'Answer '.collect(LocationUser::CLOSED_ANSWERS)->join(', ', ' or ').' if you have registered with a bank',
            'accepted_invitation.in' => 'Answer '.collect(LocationUser::CLOSED_ANSWERS)->join(', ', ' or ').' if you have registered with a bank',
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
