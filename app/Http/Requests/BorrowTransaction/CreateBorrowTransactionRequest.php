<?php

namespace App\Http\Requests\BorrowTransaction;

use Illuminate\Foundation\Http\FormRequest;

class CreateBorrowTransactionRequest extends FormRequest
{
    /**
     * Xác thực người dùng có quyền thực hiện request này không.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Hoặc kiểm tra quyền của người dùng nếu cần
    }

    /**
     * Xác thực dữ liệu đầu vào.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'book_id' => 'required|exists:books,id',
            'borrow_date' => 'required|date',
            'return_date' => 'nullable|date|after_or_equal:borrow_date',
        ];
    }

    /**
     * Các thông báo lỗi xác thực.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'book_id.required' => 'Mã sách là bắt buộc.',
            'book_id.exists' => 'Sách được chọn không tồn tại.',
            'borrow_date.required' => 'Ngày mượn là bắt buộc.',
            'borrow_date.date' => 'Ngày mượn phải là một ngày hợp lệ.',
            'return_date.date' => 'Ngày trả phải là một ngày hợp lệ.',
            'return_date.after_or_equal' => 'Ngày trả phải là ngày sau hoặc bằng ngày mượn.',
        ];
    }
}