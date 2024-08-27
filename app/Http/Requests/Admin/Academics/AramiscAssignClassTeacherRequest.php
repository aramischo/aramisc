<?php

namespace App\Http\Requests\Admin\Academics;

use Illuminate\Foundation\Http\FormRequest;

class AramiscAssignClassTeacherRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'class' => "required",
            'section' => "required",
            'teacher' => "required",
        ];
    }
}
