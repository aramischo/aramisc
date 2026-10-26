<?php

namespace App\Http\Requests\Admin\StudentInfo;

use Illuminate\Foundation\Http\FormRequest;

class StudentSubjectWiseAttendanceStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    // Dates saisies au format de la langue (j/m/aaaa en FR) : la validation les lit converties en Y-m-d, sinon la règle
    // « date » / « before » les lit en m/j/aaaa et refuse dès que le jour dépasse 12. La valeur reçue par le contrôleur reste inchangée.
    public function validationData()
    {
        $data = parent::validationData();
        foreach (['date'] as $field) {
            if (!empty($data[$field]) && is_string($data[$field])) {
                $data[$field] = toDbDateConvert($data[$field]);
            }
        }
        return $data;
    }

    public function rules()
    {
        $rules = [];

        if (moduleStatusCheck('University')) {
            $rules += [
                'un_session_id' => 'required',
                'un_faculty_id' => 'nullable',
                'un_department_id' => 'required',
                'un_academic_id' => 'required',
                'un_semester_id' => 'required',
                'un_semester_label_id' => 'required',
                'un_section_id' => 'required',
                'un_subject_id' => 'required',
            ];
        } else {
            $rules +=[
                'class' => 'required | numeric ',
                'section' => 'required | numeric ',
                'subject' => 'required | numeric ',
                'date' => 'required|date',
            ];
        }
        return $rules;
    }

    public function attributes()
    {
        $rules = [];
            if (moduleStatusCheck('University')) {
                $rules += [
                    'un_session_id' => "session",
                    'un_faculty_id' => "faculty",
                    'un_department_id' => "department",
                    'un_academic_id' => "academic",
                    'un_semester_id' => "semester",
                    'un_semester_label_id' => "semester label",
                    'un_section_id' => "section",
                    'un_subject_id' => "subject",
                ];
            }
            return $rules;
        }
}
