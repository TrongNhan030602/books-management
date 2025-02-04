<?php

namespace App\Services;

use App\Interfaces\AuthRepositoryInterface;

class AuthService
{
    protected $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function login(array $credentials)
    {
        return $this->authRepository->login(credentials: $credentials);
    }

    public function logout()
    {
        $this->authRepository->logout();
    }

    public function refreshToken()
    {
        return $this->authRepository->refreshToken();
    }

    public function getUserFromToken()
    {
        return $this->authRepository->getUserFromToken();
    }
    public function register(array $data)
    {
        return $this->authRepository->register($data);
    }

    public function createPasswordResetToken($email)
    {
        return $this->authRepository->createPasswordResetToken($email);
    }

    public function findPasswordResetToken($token)
    {
        return $this->authRepository->findPasswordResetToken($token);
    }

    public function deletePasswordResetToken($token)
    {
        return $this->authRepository->deletePasswordResetToken($token);
    }
}