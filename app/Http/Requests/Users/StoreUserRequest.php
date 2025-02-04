<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\RoleEnum;
use App\Enums\MembershipLevelEnum;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob' => 'required|date',
            'address' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'regex:/^(0[0-9]{9}|(\+84|84)[0-9]{9})$/',
                'unique:users,phone'
            ],
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\W).+$/'
            ],
            'role' => ['required', 'string', function ($attribute, $value, $fail) {
                $validRoles = RoleEnum::values();

                if (!in_array($value, $validRoles)) {
                    $fail('The selected ' . $attribute . ' is invalid.');
                }
            }],
            'membership_level' => ['required', 'string', function ($attribute, $value, $fail) {
                $validLevels = MembershipLevelEnum::values();

                if (!in_array($value, $validLevels)) {
                    $fail('The selected ' . $attribute . ' is invalid.');
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Số điện thoại không hợp lệ. Phải có 10 chữ số bắt đầu bằng 0 hoặc bao gồm tiền tố quốc gia hợp lệ.',
            'phone.unique' => 'Số điện thoại này đã được sử dụng.',
            'password.regex' => 'Mật khẩu phải chứa ít nhất một chữ cái viết hoa, một chữ cái viết thường, và một ký tự đặc biệt.',
            'role.required' => 'Vai trò là bắt buộc.',
            'role.string' => 'Vai trò phải là một chuỗi.',
            'membership_level.required' => 'Mức độ thành viên là bắt buộc.',
            'membership_level.string' => 'Mức độ thành viên phải là một chuỗi.',
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