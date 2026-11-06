<?php

namespace App\Http\Requests\Admin\Accounts;

use App\AramiscPaymentMethhod;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AramiscAddIncomeRequest extends FormRequest
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
            'date' => $this->date ? toDbDateConvert($this->date) : $this->date,
        ]);
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
            'income_head' => "required",
            'name' => "required",
            'date' => "required|date",
            'accounts' => [Rule::requiredIf(fn () => $this->isBankPayment())],
            'payment_method' => "required",
            'amount' => "required",
            'file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:".$maxFileSize,
            'description' => "sometimes|nullable|max:200",
        ];
    }

    // `payment_method` contient l'id du moyen de paiement (jamais « Bank ») : l'ancienne règle
    // required_if:payment_method,Bank n'exigeait donc jamais le compte bancaire.
    protected function isBankPayment(): bool
    {
        return $this->payment_method
            && AramiscPaymentMethhod::where('id', $this->payment_method)->value('method') == 'Bank';
    }
}
