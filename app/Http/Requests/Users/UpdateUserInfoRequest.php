<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\RoleEnum;
use App\Enums\MembershipLevelEnum;

class UpdateUserInfoRequest extends FormRequest
{
    /**
     * Xác thực người dùng có quyền thực hiện yêu cầu này không.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Xác thực dữ liệu yêu cầu.
     *
     * @return array
     */
    public function rules(): array
    {
        $userId = $this->route('user_id'); // Lấy ID của người dùng từ route
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob' => 'required|date|before:today',
            'address' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'regex:/^(0[0-9]{9}|(\+84|84)[0-9]{9})$/',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'role' => [
                'nullable',
                'string',
                Rule::in(RoleEnum::values())
            ],
            'membership_level' => [
                'nullable',
                'string',
                Rule::in(MembershipLevelEnum::values())
            ],
        ];
    }

    /**
     * Thông báo lỗi xác thực.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Địa chỉ email này đã được sử dụng.',
            'phone.regex' => 'Số điện thoại không hợp lệ. Phải có 10 chữ số bắt đầu bằng 0 hoặc bao gồm tiền tố quốc gia hợp lệ.',
            'phone.unique' => 'Số điện thoại này đã được sử dụng.',
            'dob.before' => 'Ngày sinh phải trước hôm nay.',
            'role.in' => 'Giá trị của vai trò không hợp lệ.',
            'membership_level.in' => 'Giá trị của mức độ thành viên không hợp lệ.',
        ];
    }

    /**
     * Chuẩn hóa dữ liệu trước khi xác thực.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'phone' => $this->formatPhoneNumber($this->input('phone')),
        ]);
    }

    /**
     * Định dạng lại số điện thoại.
     *
     * @param string $phoneNumber
     * @return string
     */
    private function formatPhoneNumber($phoneNumber)
    {
        // Loại bỏ các khoảng trắng thừa
        $phoneNumber = trim($phoneNumber);

        // Nếu số điện thoại bắt đầu bằng +84 hoặc 84, chuyển đổi về dạng 0XXXXXXXXX
        if (preg_match('/^\+84/', $phoneNumber)) {
            $phoneNumber = '0' . substr($phoneNumber, 3);
        } elseif (preg_match('/^84/', $phoneNumber)) {
            $phoneNumber = '0' . substr($phoneNumber, 2);
        } elseif (!preg_match('/^0/', $phoneNumber)) {
            // Thêm số 0 ở đầu nếu không có
            $phoneNumber = '0' . $phoneNumber;
        }

        return $phoneNumber;
    }
}