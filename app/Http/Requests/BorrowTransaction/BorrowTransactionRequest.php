<?php

namespace App\Http\Requests\BorrowTransaction;

use Illuminate\Foundation\Http\FormRequest;

class BorrowTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'book_id' => 'required|exists:books,id',
            'borrow_date' => 'required|date',
            'return_date' => 'required|date|after:borrow_date',
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => 'Mã sách là bắt buộc',
            'book_id.exists' => 'Sách không tồn tại',
            'borrow_date.required' => 'Ngày mượn là bắt buộc',
            'borrow_date.date' => 'Ngày mượn phải là một ngày hợp lệ',
            'return_date.required' => 'Ngày trả là bắt buộc',
            'return_date.date' => 'Ngày trả phải là một ngày hợp lệ',
            'return_date.after' => 'Ngày trả phải sau ngày mượn',
        ];
    }
}