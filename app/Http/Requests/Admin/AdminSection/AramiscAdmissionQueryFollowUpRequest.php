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

    // Le formulaire envoie les dates au format de la langue (j/m/aaaa en FR) : conversion en Y-m-d avant
    // la règle « after_or_equal », qui les lirait sinon en m/j/aaaa.
    protected function prepareForValidation()
    {
        $this->merge([
            'follow_up_date' => $this->follow_up_date ? toDbDateConvert($this->follow_up_date) : $this->follow_up_date,
            'next_follow_up_date' => $this->next_follow_up_date ? toDbDateConvert($this->next_follow_up_date) : $this->next_follow_up_date,
        ]);
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
