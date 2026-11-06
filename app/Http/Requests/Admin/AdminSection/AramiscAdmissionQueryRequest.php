<?php

namespace App\Http\Requests\Admin\AdminSection;

use Illuminate\Foundation\Http\FormRequest;

class AramiscAdmissionQueryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (userPermission('admission_query_store_a')) {
            return true;
        }
        return false;
    }

    // Le formulaire envoie les dates au format de la langue (j/m/aaaa en FR) : conversion en Y-m-d avant
    // les règles « date » / « after », qui les liraient sinon en m/j/aaaa (refus dès que le jour dépasse 12).
    protected function prepareForValidation()
    {
        $this->merge([
            'date' => $this->date ? toDbDateConvert($this->date) : $this->date,
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
        $rules = [
            'name' =>'nullable',
            'phone' =>'nullable',
            'email' =>'nullable',
            'address' => 'nullable',
            'description' => 'nullable',
            'date' => 'required|date',
            'next_follow_up_date' => 'required|date|after:date',
            'assigned'=>'required',
            'reference' =>'required',
            'source' => 'required',
            'no_of_child' =>'required',
        ];
        if (moduleStatusCheck('University')) {
            $rules += [
                'un_session_id' => 'required',
            ];
        } else {
            $rules += [
                'class' => 'required',
            ];
        }
        return $rules ;
    }
}
