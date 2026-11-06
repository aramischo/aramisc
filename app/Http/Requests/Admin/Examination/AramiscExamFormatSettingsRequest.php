<?php

namespace App\Http\Requests\Admin\Examination;

use Illuminate\Foundation\Http\FormRequest;

class AramiscExamFormatSettingsRequest extends FormRequest
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
        foreach (['publish_date', 'start_date', 'end_date'] as $field) {
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
        $maxFileSize=generalSetting()->file_size*1024;
        return [
            'format_for' => "required",
            'exam_type' => "required_if:format_for,term_exam",
            'title' => "required_if:format_for,term_exam",
            'publish_date' => "required",
            'start_date' => "required_if:format_for,term_exam|nullable|before:end_date",
            'end_date' => "required_if:format_for,term_exam|nullable|before:publish_date",
            'file' => "sometimes|nullable|mimes:jpg,jpeg,png,svg|max:".$maxFileSize,
        ];
    }

    public function prepareForValidation()
    {
        if($this->format_for == 'progress_card'){
            $this->merge([
                'title' => null,
                'exam_type' => null,
                'start_date' => null,
                'end_date' => null,
                'file' => null,
            ]);
        }

    }
}
