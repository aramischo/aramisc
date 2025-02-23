<?php

namespace App\Http\Requests\Admin\AdminSection;

use Illuminate\Foundation\Http\FormRequest;

class AramiscAdminSetupRequest extends FormRequest
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
                'type' => 'required',
                'name' => 'required|max:100',
                'description' => 'nullable'
        ];
    }
}
