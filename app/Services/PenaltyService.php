<?php

namespace App\Services;

use App\Interfaces\PenaltyRepositoryInterface;
use App\Exceptions\ErrorsException;

class PenaltyService
{
    protected $penaltyRepository;

    public function __construct(PenaltyRepositoryInterface $penaltyRepository)
    {
        $this->penaltyRepository = $penaltyRepository;
    }

    public function getPenaltiesByUser(int $userId)
    {
        return $this->penaltyRepository->getPenaltyByUser($userId);
    }

    public function updatePenalty(int $id, array $data)
    {
        return $this->penaltyRepository->updatePenalty($id, $data);
    }

    public function deletePenalty(int $id)
    {
        return $this->penaltyRepository->deletePenalty($id);
    }

    public function getAllPenalties()
    {
        return $this->penaltyRepository->getAllPenalties();
    }
}
