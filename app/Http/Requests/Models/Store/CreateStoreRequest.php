<?php

namespace App\Http\Requests\Models\Store;

use App\Models\Store;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateStoreRequest extends FormRequest
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

            'name' => ['bail', 'required', 'string', 'min:3', 'max:20'],
            'call_to_action' => ['bail', 'required', 'string', 'min:3', 'max:20'],
            'registered_with_bank' => ['sometimes', 'string', Rule::in(['yes', 'no'])],
            'banking_with' => ['bail', 'string',  Rule::requiredIf(fn() => collect([true, 1, '1'])->contains(request()->input('registered_with_bank'))), Rule::in(Store::BANKING_WITH)],

            //  'registered_bank_ids' => ['bail', 'array', 'min:1', Rule::requiredIf(fn() => collect([true, 1, '1'])->contains(request()->input('registered_with_bank')))],
            //  'registered_bank_ids.*' => ['bail', 'integer', 'numeric', 'distinct', Rule::in([1, 2, 3, 4] /* Banks::pluck('id') */)],

            'registered_with_cipa' => ['sometimes', 'string', Rule::in(['yes', 'no'])],
            'registered_with_cipa_as' => ['bail', 'string',  Rule::requiredIf(fn() => collect([true, 1, '1'])->contains(request()->input('registered_with_cipa'))), Rule::in(Store::REGISTERED_WITH_CIPA_AS)],

            'company_uin' => ['bail', 'sometimes', 'alpha_num', 'starts_with:BW','size:13'],
            'number_of_employees' => ['bail', 'sometimes', 'integer', 'numeric', 'min:1', 'max:65535'],

            'accepted_golden_rules' => ['required', 'accepted']

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
            'registered_with_bank.string' => 'Answer '.collect(Store::CLOSED_ANSWERS)->join(', ', ' or ').' if you have registered with a bank',
            'registered_with_bank.in' => 'Answer '.collect(Store::CLOSED_ANSWERS)->join(', ', ' or ').' if you have registered with a bank',

            'banking_with.in' => 'Answer '.collect(Store::BANKING_WITH)->join(', ', ' or ').' to indicate the banking instituation',

            'registered_with_cipa.string' => 'Answer '.collect(Store::CLOSED_ANSWERS)->join(', ', ' or ').' if you have registered with CIPA (Companies and Intellectual Property Authority)',
            'registered_with_cipa.in' => 'Answer '.collect(Store::CLOSED_ANSWERS)->join(', ', ' or ').' if you have registered with CIPA (Companies and Intellectual Property Authority)',
            'registered_with_cipa_as.in' => 'Answer '.collect(Store::REGISTERED_WITH_CIPA_AS)->join(', ', ' or ').' to indicate type of entity registration with CIPA (Companies and Intellectual Property Authority)',

            'accepted_golden_rules.required' => 'Please accept the :attribute',
            'accepted_golden_rules.accepted' => 'Please accept the :attribute',
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
            'accepted_golden_rules' => 'golden rules',
        ];
    }
}
