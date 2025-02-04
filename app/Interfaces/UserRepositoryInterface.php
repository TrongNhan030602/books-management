<?php

namespace App\Interfaces;

interface UserRepositoryInterface extends EloquentRepositoryInterface
{
    public function getAllUsers();
    public function findUserById($id);
    public function getUserTransactions(int $userId);
    public function createUser(array $data);
    public function updateUser($id, array $data);
    public function deleteUser($id);
    public function uploadAvatar(int $id, $file);
    public function deleteAvatar(int $id);
    public function searchUsers(string $keyword);
}