<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NoticeRequestForm extends FormRequest
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
    // les règles « date » / « after_or_equal », qui les liraient sinon en m/j/aaaa (refus dès que le jour dépasse 12).
    protected function prepareForValidation()
    {
        $this->merge([
            'notice_date' => $this->notice_date ? toDbDateConvert($this->notice_date) : $this->notice_date,
            'publish_on' => $this->publish_on ? toDbDateConvert($this->publish_on) : $this->publish_on,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {   
        return [
            'notice_title'=>['required'],
            'notice_date'=>['required', 'date'],
            'role' => ['required', 'array'],
            'publish_on'=>['required', 'after_or_equal:notice_date', 'date']
        ];
    }
}
