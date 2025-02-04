<?php

namespace App\Http\Requests\Books;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
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
            'title' => 'required|string|max:150',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'author' => 'required|string|max:100',
            'publisher_id' => 'required|integer|exists:publishers,id',
            'price' => 'required|numeric|min:0',
            'initial_quantity' => 'required|integer|min:0',
            'quantity' => 'required|integer|min:0|lte:initial_quantity',
            'published_year' => 'required|integer|digits:4',
            'status' => 'required|string|in:available,borrowed,reserved,not_available',
            'description' => 'nullable|string|max:1000',
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
            'title.required' => 'Tiêu đề sách là bắt buộc',
            'title.string' => 'Tiêu đề sách phải là một chuỗi ký tự',
            'title.max' => 'Tiêu đề sách không được vượt quá 150 ký tự',
            'author.required' => 'Tác giả sách là bắt buộc',
            'author.string' => 'Tác giả sách phải là một chuỗi ký tự',
            'author.max' => 'Tác giả sách không được vượt quá 100 ký tự',
            'publisher_id.required' => 'Nhà xuất bản là bắt buộc',
            'publisher_id.integer' => 'Nhà xuất bản phải là một số nguyên',
            'publisher_id.exists' => 'Nhà xuất bản không tồn tại',
            'price.required' => 'Giá sách là bắt buộc',
            'price.numeric' => 'Giá sách phải là một số',
            'price.min' => 'Giá sách phải lớn hơn hoặc bằng 0',
            'initial_quantity.required' => 'Số lượng ban đầu là bắt buộc',
            'initial_quantity.integer' => 'Số lượng ban đầu phải là một số nguyên',
            'initial_quantity.min' => 'Số lượng ban đầu phải lớn hơn hoặc bằng 0',
            'quantity.required' => 'Số lượng hiện có là bắt buộc',
            'quantity.integer' => 'Số lượng hiện có phải là một số nguyên',
            'quantity.min' => 'Số lượng hiện có phải lớn hơn hoặc bằng 0',
            'quantity.lte' => 'Số lượng hiện có không thể lớn hơn số lượng ban đầu',
            'published_year.required' => 'Năm xuất bản là bắt buộc',
            'published_year.integer' => 'Năm xuất bản phải là một số nguyên',
            'published_year.digits' => 'Năm xuất bản phải có 4 chữ số',
            'status.required' => 'Trạng thái sách là bắt buộc',
            'status.string' => 'Trạng thái sách phải là một chuỗi ký tự',
            'status.in' => 'Trạng thái sách phải là một trong các giá trị: available, borrowed, reserved, not_available',
            'description.string' => 'Mô tả sách phải là một chuỗi ký tự',
            'description.max' => 'Mô tả sách không được vượt quá 1000 ký tự',
        ];
    }
}