<?php

namespace App\Http\Requests\Admin\FeesCollection;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AramiscFeesDiscountRequest extends FormRequest
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
        $school_id=auth()->user()->school_id;
        if(moduleStatusCheck('University')){
            return [
                'name' => ['required', 'max:200' ,Rule::unique('aramisc_fees_discounts')->where('school_id', $school_id)->ignore($this->id) ],
                'code' =>  ['required' ,Rule::unique('aramisc_fees_discounts')->where('school_id', $school_id)->ignore($this->id) ],
                'amount' => "required|min:0",
                'description' => 'nullable|max:200',
            ];
        }
        
        elseif(directFees()){
            return [
                'name' => ['required', 'max:200' ,Rule::unique('aramisc_fees_discounts')->where('school_id', $school_id)->ignore($this->id) ],
                'code' =>  ['required' ,Rule::unique('aramisc_fees_discounts')->where('school_id', $school_id)->ignore($this->id) ],
                'amount' => "required|min:0",
                'description' => 'nullable|max:200',
            ];
        }
        else{
            return [
                'name' => ['required', 'max:200' ,Rule::unique('aramisc_fees_discounts')->where('school_id', $school_id)->ignore($this->id) ],
                'code' => ['required' ,Rule::unique('aramisc_fees_discounts')->where('school_id', $school_id)->ignore($this->id) ],
                'amount' => "required|min:0",
                'type' =>"required",
                'description' => 'nullable|max:200',
            ];
        }

    }
}
