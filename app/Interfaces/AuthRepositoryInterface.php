<?php

namespace App\Interfaces;

interface AuthRepositoryInterface
{
    public function login(array $credentials);
    public function logout();
    public function refreshToken();
    public function getUserFromToken();
    public function register(array $data);
    public function createPasswordResetToken($email);
    public function findPasswordResetToken($token);
    public function deletePasswordResetToken($token);
}
