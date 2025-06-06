<?php

namespace App\Http\Requests\Admin\FrontSettings;

use Illuminate\Foundation\Http\FormRequest;

class AramiscTestimonialRequest extends FormRequest
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
        $maxFileSize =generalSetting()->file_size*1024;
        $rules =  [
            'name' => 'required|max:100',
            'designation' => 'required|max:100',
            'institution_name' => 'required|max:100',
            'description' => 'required|max:250',
            'rating' => 'required',
        ];
        if($this->id){
            $rules['image'] = "sometimes|nullable|mimes:jpg,jpeg,png|max:".$maxFileSize;
        } else{
            $rules['image'] = "required|mimes:jpg,jpeg,png|max:".$maxFileSize;
        }
        return $rules;
    }
}
