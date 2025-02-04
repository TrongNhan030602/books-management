<?php

namespace App\Services;

use App\Interfaces\WishlistRepositoryInterface;

class WishlistService
{
    protected $wishlistRepository;

    public function __construct(WishlistRepositoryInterface $wishlistRepository)
    {
        $this->wishlistRepository = $wishlistRepository;
    }

    public function addBookToWishlist($userId, $bookId)
    {
        return $this->wishlistRepository->addBookToWishlist($userId, $bookId);
    }
    public function getLatestTransaction($userId, $bookId)
    {
        return $this->wishlistRepository->getLatestTransaction($userId, $bookId);
    }
    public function removeBookFromWishlist($userId, $bookId)
    {
        return $this->wishlistRepository->removeBookFromWishlist($userId, $bookId);
    }

    public function getWishlistByUser($userId)
    {
        return $this->wishlistRepository->getWishlistByUser(userId: $userId);
    }

    public function isBookInWishlist($userId, $bookId)
    {
        return $this->wishlistRepository->isBookInWishlist($userId, $bookId);
    }
}
