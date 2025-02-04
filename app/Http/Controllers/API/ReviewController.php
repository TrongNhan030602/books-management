<?php

namespace App\Http\Controllers\API;

use App\Services\ReviewService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Reviews\CreateReviewRequest;
use App\Http\Requests\Reviews\UpdateReviewRequest;

class ReviewController extends Controller
{
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    // Lấy danh sách tất cả đánh giá
    public function index()
    {
        try {
            $reviews = $this->reviewService->getAllReviews();
            return response()->json(data: [
                'success' => true,
                'data' => $reviews,
                'message' => 'Lấy danh sách đánh giá thành công.'
            ], status: 200);
        } catch (\Exception $e) {
            return response()->json(data: [
                'success' => false,
                'message' => 'Không thể lấy danh sách đánh giá. Vui lòng thử lại sau.'
            ], status: 500);
        }
    }

    // Lấy chi tiết một đánh giá
    public function show($id)
    {
        try {
            $review = $this->reviewService->findReviewById($id);
            if ($review) {
                return response()->json(data: [
                    'success' => true,
                    'data' => $review,
                    'message' => 'Lấy đánh giá thành công.'
                ], status: 200);
            }
            return response()->json(data: [
                'success' => false,
                'message' => 'Không tìm thấy đánh giá.'
            ], status: 404);
        } catch (\Exception $e) {
            return response()->json(data: [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy chi tiết đánh giá. Vui lòng thử lại sau.'
            ], status: 500);
        }
    }

    // Tạo đánh giá mới
    public function store(CreateReviewRequest $request)
    {
        $userId = Auth::id();
        $data = $request->validated();
        $data['user_id'] = $userId;
        $review = $this->reviewService->createReview(data: $data);
        return response()->json([
            'success' => true,
            'data' => $review,
            'message' => 'Tạo đánh giá thành công'
        ], status: 201);
    }

    // Cập nhật đánh giá
    public function update(UpdateReviewRequest $request, $id)
    {
        $data = $request->validated();
        try {
            $updatedReview = $this->reviewService->updateReview(id: $id, data: $data);
            if ($updatedReview) {
                return response()->json(data: [
                    'success' => true,
                    'data' => $updatedReview,
                    'message' => 'Cập nhật đánh giá thành công.'
                ], status: 200);
            }
            return response()->json(data: [
                'success' => false,
                'message' => 'Không tìm thấy đánh giá để cập nhật.'
            ], status: 404);
        } catch (\Exception $e) {
            return response()->json(data: [
                'success' => false,
                'message' => 'Không thể cập nhật đánh giá. Vui lòng thử lại sau.'
            ], status: 500);
        }
    }

    // Xóa đánh giá
    public function destroy($id)
    {
        try {
            if ($this->reviewService->deleteReview($id)) {
                return response()->json(data: [
                    'success' => true,
                    'message' => 'Xóa đánh giá thành công.'
                ], status: 200);
            }
            return response()->json(data: [
                'success' => false,
                'message' => 'Không tìm thấy đánh giá để xóa.'
            ], status: 404);
        } catch (\Exception $e) {
            return response()->json(data: [
                'success' => false,
                'message' => 'Không thể xóa đánh giá. Vui lòng thử lại sau.'
            ], status: 500);
        }
    }

    // Lấy đánh giá theo sách
    public function getReviewsByBook($bookId)
    {
        try {
            $reviews = $this->reviewService->getReviewsByBookId($bookId);
            return response()->json(data: [
                'success' => true,
                'data' => $reviews,
                'message' => 'Lấy danh sách đánh giá theo sách thành công.'
            ], status: 200);
        } catch (\Exception $e) {
            return response()->json(data: [
                'success' => false,
                'message' => 'Không thể lấy đánh giá theo sách. Vui lòng thử lại sau.'
            ], status: 500);
        }
    }

    // Lấy đánh giá theo người dùng
    public function getReviewsByUser($userId)
    {
        try {
            $reviews = $this->reviewService->getReviewsByUserId(userId: $userId);
            return response()->json(data: [
                'success' => true,
                'data' => $reviews,
                'message' => 'Lấy danh sách đánh giá theo người dùng thành công.'
            ], status: 200);
        } catch (\Exception $e) {
            return response()->json(data: [
                'success' => false,
                'message' => 'Không thể lấy đánh giá theo người dùng. Vui lòng thử lại sau.'
            ], status: 500);
        }
    }
    // Lấy tất cả đánh giá của người dùng đang đăng nhập
    public function getUserReviews()
    {
        try {
            $userId = Auth::id();
            $reviews = $this->reviewService->getReviewsByUserId(userId: $userId);


            return response()->json(data: [
                'success' => true,
                'data' => $reviews,
                'message' => 'Lấy danh sách đánh giá của bạn thành công.'
            ], status: 200);
        } catch (\Exception $e) {
            return response()->json(data: [
                'success' => false,
                'message' => 'Không thể lấy danh sách đánh giá của bạn. Vui lòng thử lại sau.'
            ], status: 500);
        }
    }



    // Cập nhật đánh giá của người dùng đang đăng nhập
    public function updateMyReview(UpdateReviewRequest $request, $id)
    {
        $userId = Auth::id();
        $data = $request->validated();

        try {
            $review = $this->reviewService->findReviewById(id: $id);

            // Kiểm tra xem đánh giá có thuộc về người dùng hiện tại không
            if ($review && $review->user_id == $userId) {
                $updatedReview = $this->reviewService->updateReview(id: $id, data: $data);
                return response()->json(data: [
                    'success' => true,
                    'data' => $updatedReview,
                    'message' => 'Cập nhật đánh giá thành công.'
                ], status: 200);
            }

            return response()->json(data: [
                'success' => false,
                'message' => 'Bạn không có quyền cập nhật đánh giá này.'
            ], status: 403);
        } catch (\Exception $e) {
            return response()->json(data: [
                'success' => false,
                'message' => 'Không thể cập nhật đánh giá. Vui lòng thử lại sau.'
            ], status: 500);
        }
    }

    // Xóa đánh giá của người dùng đang đăng nhập
    public function deleteMyReview($id)
    {
        $userId = Auth::id();

        try {
            $review = $this->reviewService->findReviewById($id);

            // Kiểm tra xem đánh giá có thuộc về người dùng hiện tại không
            if ($review && $review->user_id == $userId) {
                $this->reviewService->deleteReview(id: $id);
                return response()->json(data: [
                    'success' => true,
                    'message' => 'Xóa đánh giá thành công.'
                ], status: 200);
            }

            return response()->json(data: [
                'success' => false,
                'message' => 'Bạn không có quyền xóa đánh giá này.'
            ], status: 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa đánh giá. Vui lòng thử lại sau.'
            ], status: 500);
        }
    }
}
