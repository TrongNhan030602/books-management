<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\RoleEnum;
use App\Enums\MembershipLevelEnum;
use Illuminate\Validation\Rule;

class UpdatePersonalInfoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = $this->user()->id;

        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:50',
            'dob' => 'required|date|before:today',
            'address' => 'required|string|max:150',
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
            'role' => ['nullable', Rule::in(RoleEnum::values())],
            'membership_level' => ['nullable', Rule::in(MembershipLevelEnum::values())],
        ];
    }

    public function messages()
    {
        return [
            'phone.regex' => 'Số điện thoại không hợp lệ. Phải có 10 chữ số bắt đầu bằng 0 hoặc bao gồm tiền tố quốc gia hợp lệ.',
            'phone.unique' => 'Số điện thoại này đã được sử dụng.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng.',
            'dob.before' => 'Ngày sinh phải trước hôm nay.',
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