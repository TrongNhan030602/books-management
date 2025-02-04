<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\LoginRequest;
use App\Notifications\ResetPasswordRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Users\UpdatePersonalInfoRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Log;
class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->login(credentials: $request->validated());

            return response()->json(data: $data, status: 200);
        } catch (\Exception $e) {
            return response()->json(data: ['message' => 'Đăng nhập thất bại', 'error' => $e->getMessage()], status: 400);
        }
    }


    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();
            return response()->json(['message' => 'Đăng xuất thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đăng xuất thất bại', 'error' => $e->getMessage()], 500);
        }
    }

    public function refreshToken(): JsonResponse
    {
        try {
            $token = $this->authService->refreshToken();
            return response()->json(['refresh_token' => $token], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Làm mới token thất bại', 'error' => $e->getMessage()], 500);
        }
    }

    public function me(): JsonResponse
    {
        try {
            $user = $this->authService->getUserFromToken();
            return response()->json($user, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Không thể lấy thông tin người dùng', 'error' => $e->getMessage()], 400);
        }
    }

    public function register(RegisterUserRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated());
            return response()->json(['user' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đăng ký thất bại', 'error' => $e->getMessage()], 400);
        }
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->getUserFromToken();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Mật khẩu hiện tại không chính xác'], 400);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json(['message' => 'Cập nhật mật khẩu thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Cập nhật mật khẩu thất bại', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateProfile(UpdatePersonalInfoRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->getUserFromToken();

            if ($user->hasRole(RoleEnum::Reader->value)) {
                $validatedData = $request->except(['role', 'membership_level']);
            } else {
                $validatedData = $request->validated();
            }

            $user->update($validatedData);

            return response()->json(['message' => 'Cập nhật hồ sơ thành công', 'user' => $user], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Cập nhật hồ sơ thất bại', 'error' => $e->getMessage()], 500);
        }
    }

    public function sendMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $user = User::where('email', $request->email)->firstOrFail();

            // Tạo token reset mật khẩu
            $passwordReset = $this->authService->createPasswordResetToken($user->email);

            // Nếu tạo token thành công
            if ($passwordReset) {
                // Chọn mailer tùy thuộc vào người dùng
                $mailer = $user->email === 'admin@gmail.com' ? 'ses' : 'smtp';

                $user->notify(new ResetPasswordRequest($passwordReset->token, $mailer));
            }

            return response()->json(['message' => 'Password reset email has been sent.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Email not found in the system.'], 404);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Failed to send email.', 'error' => $e->getMessage()], 500);
        }
    }


    public function reset(Request $request, $token)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        try {
            $passwordReset = $this->authService->findPasswordResetToken($token);

            if (!$passwordReset) {
                return response()->json(['message' => 'Invalid password reset token.'], 404);
            }

            if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
                $this->authService->deletePasswordResetToken($token);
                return response()->json(['message' => 'Token has expired.'], 422);
            }

            $user = User::where('email', $passwordReset->email)->firstOrFail();
            $user->update(['password' => Hash::make($request->password)]);
            $this->authService->deletePasswordResetToken($token);

            return response()->json(['message' => 'Password has been successfully updated.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found.'], 404);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Failed to update password.', 'error' => $e->getMessage()], 500);
        }
    }
}