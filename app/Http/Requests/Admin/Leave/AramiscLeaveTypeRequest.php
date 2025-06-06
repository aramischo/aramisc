<?php

namespace App\Http\Requests\Admin\Leave;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AramiscLeaveTypeRequest extends FormRequest
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
            'type' => ['required', 'max:200', Rule::unique('aramisc_leave_types')->where('school_id', $school_id)->ignore($this->id) ],
        ];
    }
}
