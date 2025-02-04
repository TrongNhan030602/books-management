<?php

namespace App\Repositories;

use App\Models\BorrowTransaction;
use Illuminate\Support\Facades\Log;
use App\Enums\BorrowTransactionStatusEnum;
use App\Interfaces\BorrowTransactionRepositoryInterface;

class BorrowTransactionRepository implements BorrowTransactionRepositoryInterface
{
    public function findBorrowTransactionById(int $id)
    {
        return BorrowTransaction::find(id: $id);
    }

    public function createTransaction(int $userId, int $bookId, array $data)
    {
        return BorrowTransaction::create(attributes: [
            'user_id' => $userId,
            'book_id' => $bookId,
            'borrow_date' => $data['borrow_date'] ?? now(),
            'return_date' => $data['return_date'] ?? null,
            'status' => $data['status'] ?? BorrowTransactionStatusEnum::PENDING->value,
        ]);
    }
    public function getActiveTransactionByUserAndBook(int $userId, int $bookId)
    {
        return BorrowTransaction::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->whereIn('status', [
                BorrowTransactionStatusEnum::APPROVED->value,
                BorrowTransactionStatusEnum::PENDING->value
            ])
            ->first();
    }



    public function updateTransaction(int $id, array $data)
    {
        $transaction = BorrowTransaction::find(id: $id);
        if ($transaction) {
            $transaction->update($data);
        }
        return $transaction;
    }

    public function cancelTransaction(int $id)
    {
        $transaction = BorrowTransaction::find(id: $id);
        if ($transaction && $transaction->status === BorrowTransactionStatusEnum::PENDING->value) {
            $transaction->update(['status' => BorrowTransactionStatusEnum::CANCELLED->value]);
        }
        return $transaction;
    }

    public function getTransactionHistoryByUser(int $userId)
    {
        return BorrowTransaction::with(relations: 'book')
            ->where(column: 'user_id', operator: $userId)
            ->where(column: 'status', operator: BorrowTransactionStatusEnum::COMPLETED->value) // Thêm điều kiện trạng thái
            ->orderBy(column: 'borrow_date', direction: 'desc')
            ->get();
    }




    public function deleteTransaction(int $id)
    {
        $transaction = BorrowTransaction::find(id: $id);
        if ($transaction) {
            $transaction->delete();
        }
        return $transaction;
    }

    public function getAllTransactionsByUser(int $userId)
    {
        return BorrowTransaction::where(column: 'user_id', operator: $userId)->get();
    }

    public function getPendingTransactions()
    {
        return BorrowTransaction::with(relations: 'book')
            ->where(column: 'status', operator: BorrowTransactionStatusEnum::PENDING->value)
            ->get();
    }
    public function getReturnTransactions()
    {
        return BorrowTransaction::with(relations: 'book')
            ->where(column: 'status', operator: BorrowTransactionStatusEnum::RETURNED->value)
            ->get();
    }
    public function getBorrowedBooksByUser(int $userId)
    {
        return BorrowTransaction::with(relations: 'book')
            ->where(column: 'user_id', operator: $userId)
            ->where(column: 'status', operator: BorrowTransactionStatusEnum::APPROVED->value)
            ->get();
    }

    public function getOverdueTransactions()
    {
        return BorrowTransaction::where(column: 'return_date', operator: '<', value: now())
            ->where(column: 'status', operator: BorrowTransactionStatusEnum::APPROVED->value)
            ->get();
    }
    public function getAllTransactionsWithDetails()
    {
        return BorrowTransaction::with(relations: ['user', 'book'])
            ->orderBy(column: 'borrow_date', direction: 'desc')
            ->get();
    }
    public function getTransactionById($transactionId)
    {
        return BorrowTransaction::with(relations: ['user', 'book', 'penalties'])
            ->findOrFail($transactionId);
    }



    public function checkBookAvailability(int $bookId): bool
    {
        // Kiểm tra xem sách có bất kỳ giao dịch nào đang trong trạng thái "PENDING" hoặc "APPROVED"
        $ongoingTransaction = BorrowTransaction::where(column: 'book_id', operator: $bookId)
            ->whereIn(column: 'status', values: [BorrowTransactionStatusEnum::PENDING->value, BorrowTransactionStatusEnum::APPROVED->value])
            ->exists();

        return !$ongoingTransaction;
    }
    public function getTransactionsWithDueDateInDays(int $days)
    {
        $transactions = BorrowTransaction::with(relations: ['user', 'book']) // Eager load mối quan hệ
            ->where(column: 'status', operator: BorrowTransactionStatusEnum::APPROVED->value)
            ->whereBetween(column: 'return_date', values: [now(), now()->addDays(value: $days)])
            ->get();
        return $transactions;
    }

    // Thống kê
    public function getTotalTransactionsByUser(int $userId)
    {
        return BorrowTransaction::where('user_id', $userId)->count();
    }

    public function getTransactionsByStatus(int $userId)
    {
        return BorrowTransaction::where(column: 'user_id', operator: $userId)
            ->groupBy(groups: 'status')
            ->selectRaw(expression: 'status, count(*) as total')
            ->get();
    }

    public function getTransactionsByMonth(int $userId)
    {
        return BorrowTransaction::where(column: 'user_id', operator: $userId)
            ->selectRaw(expression: 'MONTH(borrow_date) as month, count(*) as total')
            ->groupBy(groups: 'month')
            ->get();
    }
    public function getMostBorrowedBooksByMonth(int $userId = null)
    {
        $query = BorrowTransaction::query()
            ->selectRaw('MONTH(borrow_date) as month, book_id, count(*) as times_borrowed')
            ->groupBy('month', 'book_id')
            ->orderByDesc('times_borrowed');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    public function getTotalTransactionsForAllUsers()
    {
        return BorrowTransaction::count();
    }

    public function getTransactionsByStatusForAllUsers()
    {
        return BorrowTransaction::groupBy('status')
            ->selectRaw('status, count(*) as total')
            ->get();
    }

    public function getTransactionsByMonthForAllUsers()
    {
        return BorrowTransaction::selectRaw('MONTH(borrow_date) as month, count(*) as total')
            ->groupBy('month')
            ->get();
    }

}