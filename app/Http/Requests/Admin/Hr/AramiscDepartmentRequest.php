<?php

namespace App\Http\Requests\Admin\Hr;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AramiscDepartmentRequest extends FormRequest
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
        $school_id=auth()->user()->school_id;
        return [
            'name' => ['required', 'max:200', Rule::unique('aramisc_human_departments')->where('school_id', $school_id)->ignore($this->id) ],
        ];
    }
}
