<?php

namespace App\Http\Requests\Admin\GeneralSettings;

use Illuminate\Foundation\Http\FormRequest;

class AramiscOptionalSetupStoreRequest extends FormRequest
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
            'class' => "required|array",
            'gpa_above' => "required",
        ];
    }
}
