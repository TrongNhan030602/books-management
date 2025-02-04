<?php

namespace App\Http\Requests\Publishers;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePublisherRequest extends FormRequest
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
            'name' => 'sometimes|string|max:150|unique:publishers,name,' . $this->route('id'),
            'address' => 'sometimes|string|max:150'
        ];
    }

    /**
     * Các thông báo lỗi xác thực.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.sometimes' => 'Tên nhà xuất bản không bắt buộc phải có.',
            'name.string' => 'Tên nhà xuất bản phải là một chuỗi ký tự.',
            'name.max' => 'Tên nhà xuất bản không được vượt quá 150 ký tự.',
            'name.unique' => 'Tên nhà xuất bản đã tồn tại.',
            'address.sometimes' => 'Địa chỉ không bắt buộc phải có.',
            'address.string' => 'Địa chỉ phải là một chuỗi ký tự.',
            'address.max' => 'Địa chỉ không được vượt quá 150 ký tự.',
        ];
    }
}