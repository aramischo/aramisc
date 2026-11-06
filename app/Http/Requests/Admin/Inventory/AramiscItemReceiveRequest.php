<?php

namespace App\Http\Requests\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class AramiscItemReceiveRequest extends FormRequest
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

    // Le formulaire envoie la date au format de la langue (j/m/aaaa en FR) : conversion en Y-m-d avant
    // la règle « date », qui la lirait sinon en m/j/aaaa (refus dès que le jour dépasse 12).
    protected function prepareForValidation()
    {
        $this->merge([
            'receive_date' => $this->receive_date ? toDbDateConvert($this->receive_date) : $this->receive_date,
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
            'bank_id' => "required_if:payment_method,Bank",
            'expense_head_id' => "required",
            'supplier_id' => "required",
            'store_id' => "required",
            'reference_no' => "sometimes|nullable",
            'receive_date' => "required|date",
            'payment_method' => "required",
            'description' => "sometimes|nullable",
            'item_id' =>"required|array",
            'unit_price' => "required|array",
            'quantity' => "required|array",
            'total' => "sometimes|nullable|array",
            'subTotalQuantity' => "sometimes|nullable",
            'subTotal' => "numeric|min:0",
            'totalPaid' =>  "numeric|min:0",
        ];
    }
}
