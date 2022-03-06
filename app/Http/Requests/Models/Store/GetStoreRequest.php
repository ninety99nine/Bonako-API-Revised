<?php

namespace App\Http\Requests\Models\Store;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class GetStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Request $request)
    {
        /**
         *  Authorize if the current user has been assigned to this store
         */
        return request()->user()->isAssignedToStore($request->store);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
