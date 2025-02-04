<?php

namespace App\Repositories;

use App\Models\Wishlist;
use App\Models\BorrowTransaction;
use App\Interfaces\WishlistRepositoryInterface;

class WishlistRepository implements WishlistRepositoryInterface
{
    public function addBookToWishlist($userId, $bookId)
    {
        return Wishlist::create([
            'user_id' => $userId,
            'book_id' => $bookId
        ]);
    }

    public function removeBookFromWishlist($userId, $bookId)
    {
        return Wishlist::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->delete();
    }

    public function getWishlistByUser($userId)
    {
        return Wishlist::where(column: 'user_id', operator: $userId)
            ->with(relations: [
                'book',
                'book.borrowTransactions' => function ($query) use ($userId) {
                    // Lấy giao dịch của người dùng đang đăng nhập
                    $query->where('user_id', $userId)->latest()->first();
                }
            ])
            ->get();
    }


    public function isBookInWishlist($userId, $bookId)
    {
        return Wishlist::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->exists();
    }
    public function getLatestTransaction($userId, $bookId)
    {
        return BorrowTransaction::where(column: 'user_id', operator: $userId)
            ->where(column: 'book_id', operator: $bookId)
            ->orderBy(column: 'created_at', direction: 'desc')
            ->first();
    }
}