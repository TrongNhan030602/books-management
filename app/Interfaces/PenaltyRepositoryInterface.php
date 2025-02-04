<?php

namespace App\Interfaces;

interface PenaltyRepositoryInterface
{
    public function createPenalty(int $userId, int $borrowTransactionId, array $data);
    public function getPenaltyByUser(int $userId);
    public function updatePenalty(int $id, array $data);
    public function deletePenalty(int $id);
    public function getAllPenalties();
}
