<?php

namespace App\Interfaces;

interface BookRepositoryInterface
{
    public function findBookById(int $id);
    public function updateBookStatus(int $id, string $status);
    public function updateBookQuantity(int $id, int $quantity);
    public function checkBookAvailability(int $id): bool;
    public function createBook(array $data, $file = null);
    public function updateBook(int $id, array $data, $file = null);
    public function deleteBook(int $id);
    public function searchBooks(string $keyword);
    public function uploadBookCover(int $id, $file);
    public function deleteBookCover(int $id);
    public function getBookDetails($bookId, $userId);
    public function advancedSearchBooks(array $filters, int $userId);
    public function getUserBooksAndTransactions(int $userId);
}
