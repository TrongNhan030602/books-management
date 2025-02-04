<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\PasswordReset;
use App\Enums\RoleEnum;
use Illuminate\Support\Str;
use App\Enums\MembershipLevelEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Interfaces\AuthRepositoryInterface;

class AuthRepository extends EloquentRepository implements AuthRepositoryInterface
{
    /**
     * Get model
     * @return string
     */
    public function getModel()
    {
        return User::class;
    }
    public function login(array $credentials)
    {
        if (!$token = Auth::attempt($credentials)) {
            throw new \Exception(message: 'Thông tin đăng nhập không chính xác!');
        }
        $user = Auth::user();

        // Trả về cả token và vai trò của người dùng
        return [
            'access_token' => $token,
            'role' => $user->role
        ];
    }


    public function logout()
    {
        Auth::logout();
    }

    public function refreshToken()
    {
        return Auth::refresh();
    }

    public function getUserFromToken()
    {
        return Auth::user();
    }

    public function register(array $data)
    {

        $data['role'] = RoleEnum::Reader->value;
        $data['membership_level'] = $data['membership_level'] ?? MembershipLevelEnum::Bronze->value;
        $data['password'] = Hash::make($data['password']);
        $data['remember_token'] = Str::random(10);

        return User::create($data);
    }

    public function createPasswordResetToken($email)
    {
        return PasswordReset::updateOrCreate(
            attributes: ['email' => $email],
            values: ['token' => Str::random(length: 60)]
        );
    }

    public function findPasswordResetToken($token)
    {
        return PasswordReset::where('token', $token)->first();
    }

    public function deletePasswordResetToken($token)
    {
        PasswordReset::where('token', $token)->delete();
    }

}