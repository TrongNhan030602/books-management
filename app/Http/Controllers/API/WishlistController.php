<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Services\WishlistService;
use App\Exceptions\ErrorsException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    protected $wishlistService;

    public function __construct(WishlistService $wishlistService)
    {
        $this->wishlistService = $wishlistService;
    }

    public function addBookToWishlist(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);
        $userId = Auth::id();
        $bookId = $request->book_id;
        try {
            $this->wishlistService->addBookToWishlist($userId, $bookId);
            return response()->json(data: [
                'success' => true,
                'message' => 'Sách đã được thêm vào danh sách ưa thích.'
            ], status: 201);
        } catch (ErrorsException $e) {
            return response()->json(data: [
                'success' => false,
                'message' => 'Không thể thêm sách vào danh sách ưa thích. Vui lòng thử lại sau.'
            ], status: 500);
        }
    }

    public function removeBookFromWishlist(Request $request)
    {
        $request->validate(['book_id' => 'required|exists:wishlists,book_id']);
        $userId = Auth::id();
        $bookId = $request->book_id;
        try {
            $this->wishlistService->removeBookFromWishlist(userId: $userId, bookId: $bookId);
            return response()->json(data: [
                'success' => true,
                'message' => 'Sách đã được xóa khỏi danh sách ưa thích.'
            ], status: 200);
        } catch (ErrorsException $e) {
            return response()->json(data: [
                'success' => false,
                'message' => 'Không thể xóa sách khỏi danh sách ưa thích. Vui lòng thử lại sau.'
            ], status: 500);
        }
    }

    public function getWishlistByUser()
    {
        $userId = Auth::id();
        try {
            $wishlist = $this->wishlistService->getWishlistByUser(userId: $userId);
            return response()->json(data: [
                'success' => true,
                'data' => $wishlist,
                'message' => 'Danh sách ưa thích đã được lấy thành công.'
            ], status: 200);
        } catch (ErrorsException $e) {
            return response()->json(data: [
                'success' => false,
                'message' => 'Không thể lấy danh sách ưa thích. Vui lòng thử lại sau.'
            ], status: 500);
        }
    }



    public function isBookInWishlist(Request $request)
    {
        $request->validate(['book_id' => 'required|exists:books,id']);
        $userId = Auth::id();
        $bookId = $request->book_id;
        try {
            $exists = $this->wishlistService->isBookInWishlist($userId, $bookId);
            return response()->json(data: [
                'success' => true,
                'exists' => $exists,
                'message' => $exists ? 'Sách có trong danh sách ưa thích.' : 'Sách không có trong danh sách ưa thích.'
            ], status: 200);
        } catch (ErrorsException $e) {
            return response()->json(data: [
                'success' => false,
                'message' => 'Không thể kiểm tra sách trong danh sách ưa thích. Vui lòng thử lại sau.'
            ], status: 500);
        }
    }
}