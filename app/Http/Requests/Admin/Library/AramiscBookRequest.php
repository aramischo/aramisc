<?php

namespace App\Http\Requests\Admin\Library;

use Illuminate\Foundation\Http\FormRequest;

class AramiscBookRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $bookId = $this->route('id');

        return [
            'book_title' => "required|max:200",
            'book_category_id' => "required",
            'subject' => "required",
            'quantity' => "sometimes|nullable|integer|min:0",
            'book_number' => "sometimes|nullable",
            //'isbn_no' => "sometimes|nullable|unique:aramisc_books,isbn_no|different:book_number",
            'isbn_no' => [
                'sometimes',
                'nullable',
                'different:book_number',
                \Illuminate\Validation\Rule::unique('aramisc_books', 'isbn_no')->ignore($bookId),
            ],
            'publisher_name' => "sometimes|nullable",
            'author_name' => "sometimes|nullable",
            'details' => "sometimes|nullable",
            'book_price' => "sometimes|nullable|integer|min:0",
            'rack_number' => "sometimes|nullable",
        ];
    }
    public function attributes()
    {
        return [
            'book_category_id' => 'book category'
        ];
    }
}
