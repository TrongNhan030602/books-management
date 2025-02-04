<?php

namespace App\Interfaces;

interface BorrowTransactionRepositoryInterface
{
    public function findBorrowTransactionById(int $id);
    public function createTransaction(int $userId, int $bookId, array $data);
    public function updateTransaction(int $id, array $data);
    public function deleteTransaction(int $id);
    public function getAllTransactionsWithDetails();
    public function getTransactionById(int $transactionId);
    public function getAllTransactionsByUser(int $userId);
    public function getBorrowedBooksByUser(int $userId);
    public function getPendingTransactions();
    public function getReturnTransactions();
    public function checkBookAvailability(int $bookId): bool;
    public function cancelTransaction(int $id);
    public function getTransactionHistoryByUser(int $userId);
    public function getOverdueTransactions();
    public function getActiveTransactionByUserAndBook(int $userId, int $bookId);
    public function getTransactionsWithDueDateInDays(int $days);
    // Thống kê
    public function getTotalTransactionsByUser(int $userId);
    public function getTransactionsByStatus(int $userId);
    public function getTransactionsByMonth(int $userId);
    public function getMostBorrowedBooksByMonth(int $userId = null);
    public function getTotalTransactionsForAllUsers();
    public function getTransactionsByStatusForAllUsers();
    public function getTransactionsByMonthForAllUsers();
}