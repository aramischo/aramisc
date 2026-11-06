<?php

namespace App\Http\Requests\Admin\Leave;

use Illuminate\Foundation\Http\FormRequest;

class AramiscLeaveRequest extends FormRequest
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

    // Dates saisies au format de la langue (j/m/aaaa en FR) : la validation les lit converties en Y-m-d, sinon la règle
    // « date » / « before » les lit en m/j/aaaa et refuse dès que le jour dépasse 12. La valeur reçue par le contrôleur reste inchangée.
    public function validationData()
    {
        $data = parent::validationData();
        foreach (['apply_date', 'leave_from', 'leave_to'] as $field) {
            if (!empty($data[$field]) && is_string($data[$field])) {
                $data[$field] = toDbDateConvert($data[$field]);
            }
        }
        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $maxFileSize = generalSetting()->file_size*1024;
        return [
            'apply_date' => "required",
            'leave_define_id' => "required",
            'leave_from' => 'required|before_or_equal:leave_to',
            'leave_to' => "required",
            'attach_file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:".$maxFileSize,
        ];
    }
}
