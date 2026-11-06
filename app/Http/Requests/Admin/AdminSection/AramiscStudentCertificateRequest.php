<?php

namespace App\Http\Requests\Admin\AdminSection;

use Illuminate\Foundation\Http\FormRequest;

class AramiscStudentCertificateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    // Le formulaire envoie la date au format de la langue (j/m/aaaa en FR) : conversion en Y-m-d avant
    // la règle « date », qui la lirait sinon en m/j/aaaa (refus dès que le jour dépasse 12).
    protected function prepareForValidation()
    {
        $this->merge([
            'date' => $this->date ? toDbDateConvert($this->date) : $this->date,
        ]);
    }

    public function rules()
    {
        $maxFileSize =generalSetting()->file_size*1024;
        return [
            'name' => "required|max:50",
            'header_left_text' => "nullable",
            'date' => "nullable|date",
            'body' => "nullable",
            'footer_left_text' => "nullable",
            'footer_center_text' => "nullable",
            'footer_right_text' => "nullable",
            'student_photo' => "nullable",
            'layout' => "required",
            'body_font_size' => "required",
            'height' => "required",
            'width' => "required",
            'file' => "mimes:jpg,jpeg,png|max:".$maxFileSize
        ];

    }
}
