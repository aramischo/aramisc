<?php

namespace App\Http\Requests\Admin\AdminSection;

use Illuminate\Foundation\Http\FormRequest;

class AramiscAdmissionQueryFollowUpRequest extends FormRequest
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
        $today = date('Y-m-d');
        return [
            'next_follow_up_date' => 'required|after_or_equal:follow_up_date,'.$today,
            'response' => 'required'
        ];
    }
}
