<?php

namespace App\Services;

use App\Interfaces\UserRepositoryInterface;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers()
    {
        return $this->userRepository->getAllUsers();
    }

    public function findUserById($id)
    {
        return $this->userRepository->findUserById($id);
    }
    public function getUserTransactions(int $userId)
    {
        return $this->userRepository->getUserTransactions($userId);
    }
    public function createUser(array $data)
    {
        return $this->userRepository->createUser(data: $data);
    }

    public function updateUser($id, array $data)
    {
        return $this->userRepository->updateUser($id, $data);
    }

    public function deleteUser($id)
    {
        return $this->userRepository->deleteUser($id);
    }
    public function uploadAvatar(int $id, $file)
    {
        return $this->userRepository->uploadAvatar($id, $file);
    }

    public function deleteAvatar(int $id)
    {
        return $this->userRepository->deleteAvatar($id);
    }
    public function searchUsers(string $keyword)
    {
        return $this->userRepository->searchUsers($keyword);
    }
}