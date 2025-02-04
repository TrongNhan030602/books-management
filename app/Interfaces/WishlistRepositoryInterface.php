<?php

namespace App\Interfaces;

interface WishlistRepositoryInterface
{
    public function addBookToWishlist($userId, $bookId);
    public function removeBookFromWishlist($userId, $bookId);
    public function getWishlistByUser($userId);
    public function isBookInWishlist($userId, $bookId);
    public function getLatestTransaction($userId, $bookId);
}
