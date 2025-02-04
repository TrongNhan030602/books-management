<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:50',
            'dob' => 'required|date|before:today',
            'address' => 'required|string|max:100',
            'phone' => [
                'required',
                'string',
                'max:11',
                'regex:' . config('custom.phoneFormat'),
                'unique:users',
            ],
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'confirmed',
                'regex:' . config('custom.passwordFormat'),
            ],
            'role' => 'nullable|string|in:Reader,Admin',
            'membership_level' => 'nullable|string|in:Bronze,Silver,Gold',
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'Địa chỉ email này đã được sử dụng.',
            'phone.unique' => 'Số điện thoại này đã được sử dụng.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.regex' => 'Mật khẩu phải chứa ít nhất một chữ hoa, một chữ thường, một chữ số và một ký tự đặc biệt, và độ dài từ 6 đến 15 ký tự.',
            'phone.regex' => 'Số điện thoại không hợp lệ. Phải có 10 chữ số bắt đầu bằng 0.',
            'dob.before' => 'Ngày sinh phải trước hôm nay.',
            'role.in' => 'Giá trị của vai trò không hợp lệ.',
            'membership_level.in' => 'Giá trị của mức độ thành viên không hợp lệ.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'phone' => $this->formatPhoneNumber($this->input('phone')),
        ]);
    }

    private function formatPhoneNumber($phoneNumber)
    {
        $phoneNumber = trim($phoneNumber);

        if (!preg_match('/^(0|\+84)/', $phoneNumber)) {
            if (strpos($phoneNumber, '84') === 0) {
                $phoneNumber = '0' . substr($phoneNumber, 2);
            } elseif (!preg_match('/^0/', $phoneNumber)) {
                $phoneNumber = '0' . $phoneNumber;
            }
        }

        return preg_replace('/\D/', '', $phoneNumber);
    }
}
