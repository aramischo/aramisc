<?php

namespace App\Http\Requests\Admin\Accounts;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AramiscBankAccountRequest extends FormRequest
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
            'bank_name' => "required",
            'account_name' => "required",
            // En modification (route bank-account/{id}), le compte lui-même ne compte pas comme doublon.
            'account_number' => ['required', Rule::unique('aramisc_bank_accounts', 'account_number')->ignore($this->route('id'))],
            'account_type' => "sometimes|nullable",
            'opening_balance' => "required|numeric",           
            'note'=>"sometimes|nullable|max:200",
        ];
    }
}
