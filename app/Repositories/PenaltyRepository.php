<?php

namespace App\Repositories;

use App\Interfaces\PenaltyRepositoryInterface;
use App\Models\Penalty;

class PenaltyRepository implements PenaltyRepositoryInterface
{
    public function createPenalty(int $userId, int $borrowTransactionId, array $data)
    {
        return Penalty::create(attributes: [
            'user_id' => $userId,
            'borrow_transaction_id' => $borrowTransactionId, // Lưu ID giao dịch mượn
            'amount' => $data['amount'],
            'reason' => $data['reason'],
            'due_date' => $data['due_date'] ?? null, // Hạn nộp phạt
        ]);
    }



    public function getPenaltyByUser(int $userId)
    {
        // Eager load bảng liên quan: books và borrow_transactions
        return Penalty::with(relations: ['borrowTransaction.book']) // Tải trước thông tin về giao dịch và sách
            ->where(column: 'user_id', operator: $userId)
            ->get();
    }


    public function updatePenalty(int $id, array $data)
    {
        $penalty = Penalty::find(id: $id);
        if ($penalty) {
            $penalty->update($data);
        }
        return $penalty;
    }

    public function deletePenalty(int $id)
    {
        $penalty = Penalty::find(id: $id);
        if ($penalty) {
            $penalty->delete();
        }
        return $penalty;
    }

    public function getAllPenalties()
    {
        return Penalty::all();
    }
}
