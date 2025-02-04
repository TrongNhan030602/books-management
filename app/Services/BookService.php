<?php

namespace App\Services;

use App\Interfaces\BookRepositoryInterface;
use App\Exceptions\ErrorsException;

class BookService
{
    protected $bookRepository;

    public function __construct(BookRepositoryInterface $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    public function findBookById(int $id)
    {
        return $this->bookRepository->findBookById(id: $id);
    }
    public function getUserBooksAndTransactions(int $userId)
    {
        return $this->bookRepository->getUserBooksAndTransactions(userId: $userId)->load('borrowTransactions');
    }


    public function getBookDetails($bookId, $userId)
    {
        return $this->bookRepository->getBookDetails(bookId: $bookId, userId: $userId);
    }


    public function updateBookStatus(int $id, string $status)
    {
        return $this->bookRepository->updateBookStatus(id: $id, status: $status);
    }

    public function checkBookAvailability(int $id): bool
    {
        return $this->bookRepository->checkBookAvailability(id: $id);
    }

    public function updateBookQuantity(int $id, int $quantity)
    {
        return $this->bookRepository->updateBookQuantity(id: $id, quantity: $quantity);
    }

    public function createBook(array $data, $file = null)
    {
        return $this->bookRepository->createBook(data: $data, file: $file);
    }

    public function updateBook(int $id, array $data, $file = null)
    {
        return $this->bookRepository->updateBook(id: $id, data: $data, file: $file);
    }


    public function deleteBook(int $id)
    {
        return $this->bookRepository->deleteBook($id);
    }

    public function searchBooks(string $keyword)
    {
        return $this->bookRepository->searchBooks(keyword: $keyword)->load('borrowTransactions'); // Load giao dịch
    }


    public function uploadBookCover(int $id, $file)
    {
        return $this->bookRepository->uploadBookCover(id: $id, file: $file);
    }

    public function deleteBookCover(int $id)
    {
        return $this->bookRepository->deleteBookCover(id: $id);
    }
    public function advancedSearchBooks(array $filters, int $userId)
    {
        return $this->bookRepository->advancedSearchBooks($filters, $userId);
    }
}
