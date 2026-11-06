<?php

namespace App\Http\Requests\Admin\StudentInfo;

use Illuminate\Foundation\Http\FormRequest;

class AramiscStudentAttendanceSearchRequest extends FormRequest
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
        foreach (['attendance_date'] as $field) {
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
        if (moduleStatusCheck('University')) {
            return [
                'attendance_date' => 'required|date',
                'un_session_id' => 'sometimes|nullable',
                'un_faculty_id' => 'sometimes|nullable',
                'un_department_id' => 'sometimes|nullable',
                'un_academic_id' => 'sometimes|nullable',
                'un_semester_id' => 'sometimes|nullable',
                'un_semester_label_id' => 'sometimes|nullable',
                'un_subject_id' => 'sometimes|nullable',
                'un_section_id' => 'required',
            ];
        } else {
            return [
                'class_id' => 'required|integer',
                'section_id'=>'required|integer',
                'attendance_date' => 'required|date'
            ];
        }
    }
}
