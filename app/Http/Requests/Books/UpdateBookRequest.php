<?php

namespace App\Http\Requests\Books;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền gửi yêu cầu này hay không.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Xác thực dữ liệu đầu vào.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:150',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'author' => 'sometimes|string|max:100',
            'publisher_id' => 'sometimes|integer|exists:publishers,id',
            'price' => 'sometimes|numeric|min:0',
            'initial_quantity' => 'sometimes|integer|min:0',
            'quantity' => 'sometimes|integer|min:0',
            'published_year' => 'sometimes|integer|digits:4',
            'status' => 'sometimes|string|in:available,borrowed,reserved,not_available',
            'description' => 'sometimes|string|max:1000',
            'location' => 'nullable|string', // Vị trí sách
            'category_id' => 'nullable|exists:categories,id',
        ];
    }

    /**
     * Các thông báo lỗi tùy chỉnh.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.sometimes' => 'Tiêu đề sách không bắt buộc nhưng nếu có thì phải là một chuỗi ký tự và không quá 150 ký tự',
            'title.string' => 'Tiêu đề sách phải là một chuỗi ký tự',
            'title.max' => 'Tiêu đề sách không được vượt quá 150 ký tự',
            'author.sometimes' => 'Tác giả sách không bắt buộc nhưng nếu có thì phải là một chuỗi ký tự và không quá 100 ký tự',
            'author.string' => 'Tác giả sách phải là một chuỗi ký tự',
            'author.max' => 'Tác giả sách không được vượt quá 100 ký tự',
            'publisher_id.sometimes' => 'Nhà xuất bản không bắt buộc nhưng nếu có thì phải là một số nguyên và tồn tại',
            'publisher_id.integer' => 'Nhà xuất bản phải là một số nguyên',
            'publisher_id.exists' => 'Nhà xuất bản không tồn tại',
            'price.sometimes' => 'Giá sách không bắt buộc nhưng nếu có thì phải là một số và không nhỏ hơn 0',
            'price.numeric' => 'Giá sách phải là một số',
            'price.min' => 'Giá sách phải lớn hơn hoặc bằng 0',
            'initial_quantity.sometimes' => 'Số lượng ban đầu không bắt buộc nhưng nếu có thì phải là một số nguyên và không nhỏ hơn 0',
            'initial_quantity.integer' => 'Số lượng ban đầu phải là một số nguyên',
            'initial_quantity.min' => 'Số lượng ban đầu phải lớn hơn hoặc bằng 0',
            'quantity.sometimes' => 'Số lượng hiện có không bắt buộc nhưng nếu có thì phải là một số nguyên và không nhỏ hơn 0',
            'quantity.integer' => 'Số lượng hiện có phải là một số nguyên',
            'quantity.min' => 'Số lượng hiện có phải lớn hơn hoặc bằng 0',
            'published_year.sometimes' => 'Năm xuất bản không bắt buộc nhưng nếu có thì phải là một số nguyên và có 4 chữ số',
            'published_year.integer' => 'Năm xuất bản phải là một số nguyên',
            'published_year.digits' => 'Năm xuất bản phải có 4 chữ số',
            'status.sometimes' => 'Trạng thái sách không bắt buộc nhưng nếu có thì phải là một trong các giá trị: available, borrowed, reserved, not_available',
            'status.string' => 'Trạng thái sách phải là một chuỗi ký tự',
            'status.in' => 'Trạng thái sách phải là một trong các giá trị: available, borrowed, reserved, not_available',
        ];
    }
}