<?php

namespace App\Repositories;

use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Support\Str;
use App\Enums\MembershipLevelEnum;
use App\Exceptions\ErrorsException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Interfaces\UserRepositoryInterface;

class UserRepository extends EloquentRepository implements UserRepositoryInterface
{

    public function getModel()
    {
        return User::class;
    }


    public function getAllUsers()
    {
        return $this->getAll();
    }

    public function findUserById($id)
    {
        $user = $this->find($id);
        if (!$user) {
            throw new ErrorsException('User not found', 404);
        }
        return $user;
    }
    public function getUserTransactions(int $userId)
    {
        // Truy vấn các giao dịch mượn sách của người dùng từ bảng BorrowTransaction
        $user = $this->find($userId);
        if (!$user) {
            throw new ErrorsException('User not found', 404);
        }

        return $user->borrowTransactions;
    }
    public function createUser(array $data)
    {
        // Mặc định là Reader + Bronze
        if (!isset($data['role'])) {
            $data['role'] = RoleEnum::Reader->value;
        }
        if (!isset($data['membership_level'])) {
            $data['membership_level'] = MembershipLevelEnum::Bronze->value;
        }
        $data['password'] = Hash::make($data['password']);
        $data['remember_token'] = Str::random(10);

        return $this->create($data);
    }

    public function updateUser($id, array $data)
    {
        $user = $this->find($id);
        if (!$user) {
            throw new ErrorsException('User not found', 404);
        }
        return $this->update($id, $data);
    }

    public function deleteUser($id)
    {
        $user = $this->find($id);
        if (!$user) {
            throw new ErrorsException('User not found', 404);
        }
        return $this->delete($id);
    }

    public function uploadAvatar(int $id, $file)
    {
        $user = User::find($id);
        if ($user) {
            $this->deleteAvatar($id);

            // Tạo tên tệp tin từ first_name và thêm dấu thời gian 
            $firstName = preg_replace('/[^a-zA-Z0-9]/', '_', $user->first_name);
            $timestamp = time();
            $extension = $file->getClientOriginalExtension();
            $filename = $firstName . '_' . $timestamp . '.' . $extension;

            $path = $file->storeAs('avatars', $filename, 'public');
            $user->avatar = $path;
            $user->save();
        }
        return $user;
    }




    public function deleteAvatar(int $id)
    {
        $user = User::find($id);
        if ($user && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
        }
        return $user;
    }
    public function searchUsers(string $keyword)
    {
        return User::where('id', 'like', "%$keyword%")
            ->orWhere('first_name', 'like', "%$keyword%")
            ->orWhere('last_name', 'like', "%$keyword%")
            ->orWhere('email', 'like', "%$keyword%")
            ->orWhere('phone', 'like', "%$keyword%")
            ->orWhere('role', 'like', "%$keyword%")
            ->orWhere('membership_level', 'like', "%$keyword%")
            ->get();
    }

}