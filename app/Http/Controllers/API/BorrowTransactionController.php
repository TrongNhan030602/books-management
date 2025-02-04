<?php

namespace App\Http\Controllers\API;

use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use App\Exceptions\ErrorsException;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\BorrowTransactionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\BorrowTransaction\CreateBorrowTransactionRequest;

class BorrowTransactionController extends Controller
{
    protected $borrowTransactionService;

    public function __construct(BorrowTransactionService $borrowTransactionService)
    {
        $this->borrowTransactionService = $borrowTransactionService;
    }

    // Tạo giao dịch mượn sách
    public function create(CreateBorrowTransactionRequest $request)
    {
        $validated = $request->validated();
        $userId = Auth::user()->id; // Lấy ID người dùng hiện tại
        try {
            $borrowTransaction = $this->borrowTransactionService->createBorrowTransaction(userId: $userId, data: $validated);
            return response()->json(data: [
                'success' => true,
                'data' => $borrowTransaction,
                'message' => 'Đã gửi yêu cầu mượn sách'
            ], status: 201);
        } catch (ErrorsException $e) {
            return response()->json(data: ['error' => $e->getMessage()], status: $e->getCode());
        }
    }

    // Duyệt giao dịch mượn sách
    public function approve($id)
    {
        try {
            $borrowTransaction = $this->borrowTransactionService->approveBorrowTransaction(id: $id);
            return response()->json(data: $borrowTransaction);
        } catch (ErrorsException $e) {
            return response()->json(data: ['error' => $e->getMessage()], status: $e->getCode());
        }
    }

    // Xóa giao dịch mượn sách
    public function delete($id)
    {
        try {
            $this->borrowTransactionService->deleteBorrowTransaction(id: $id);
            return response()->json(data: ['message' => 'Đã xóa giao dịch thành công']);
        } catch (ErrorsException $e) {
            return response()->json(data: ['error' => $e->getMessage()], status: $e->getCode());
        }
    }

    // Lấy tất cả giao dịch mượn sách của một người dùng
    public function getAllByUser($userId)
    {
        $transactions = $this->borrowTransactionService->getAllTransactionsByUser($userId);
        return response()->json(data: $transactions);
    }

    // Lấy tất cả giao dịch mượn sách đang chờ
    public function getPendingtransactions()
    {
        $transactions = $this->borrowTransactionService->getPendingTransactions();
        return response()->json(data: $transactions);
    }
    public function getReturnTransactions()
    {
        $transactions = $this->borrowTransactionService->getReturnTransactions();
        return response()->json(data: $transactions);
    }
    public function extendTransaction(Request $request, $transactionId)
    {
        $extra_days = $request->input('extra_days'); // Số ngày gia hạn từ request

        if (!$extra_days || !is_numeric($extra_days) || $extra_days <= 0) {
            return response()->json(['error' => 'Số ngày gia hạn không hợp lệ.'], 400);
        }

        try {
            $updatedTransaction = $this->borrowTransactionService->extendBorrowTransaction($transactionId, (int) $extra_days);

            return response()->json([
                'success' => true,
                'data' => $updatedTransaction,
                'message' => 'Gia hạn mượn sách thành công.'
            ]);
        } catch (ErrorsException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }



    // Trả sách
    public function returnBook($borrowTransactionId)
    {
        try {
            $borrowTransaction = $this->borrowTransactionService->returnBook(borrowTransactionId: $borrowTransactionId);
            return response()->json(data: [
                'success' => true,
                'data' => $borrowTransaction,
                'message' => 'Đã gửi yêu cầu trả sách'
            ]);
        } catch (ErrorsException $e) {
            return response()->json(data: ['error' => $e->getMessage()], status: $e->getCode());
        }
    }

    // Xác nhận hoàn thành trả sách
    public function completeReturn($id)
    {
        try {
            $borrowTransaction = $this->borrowTransactionService->completeReturn(id: $id);
            return response()->json(data: $borrowTransaction);
        } catch (ErrorsException $e) {
            return response()->json(data: ['error' => $e->getMessage()], status: $e->getCode());
        }
    }
    public function rejectBorrowTransaction(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        try {
            $borrowTransaction = $this->borrowTransactionService->rejectBorrowTransaction(id: $id, reason: $request->reason);
            return response()->json(data: [
                'success' => true,
                'data' => $borrowTransaction,
                'message' => 'Yêu cầu mượn sách đã bị từ chối'
            ]);
        } catch (ErrorsException $e) {
            return response()->json(data: ['error' => $e->getMessage()], status: $e->getCode());
        }
    }
    public function cancelBorrowTransaction($id)
    {
        $userId = Auth::user()->id;

        try {
            $borrowTransaction = $this->borrowTransactionService->cancelBorrowTransaction(id: $id, userId: $userId);
            return response()->json(data: [
                'success' => true,
                'data' => $borrowTransaction,
                'message' => 'Đã hủy yêu cầu mượn sách'
            ]);
        } catch (ErrorsException $e) {
            return response()->json(data: ['error' => $e->getMessage()], status: $e->getCode());
        }
    }
    public function testNotifyUsersOfUpcomingDueDate()
    {
        try {
            // Gọi hàm notifyUsersOfUpcomingDueDate từ service
            $this->borrowTransactionService->notifyUsersOfUpcomingDueDate();

            // Phản hồi thành công nếu không có lỗi xảy ra
            return response()->json([
                'message' => 'Thông báo đã được gửi cho người dùng về giao dịch sắp đến hạn.'
            ], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi nếu có
            Log::error('Lỗi khi gửi thông báo: ' . $e->getMessage());

            return response()->json([
                'message' => 'Đã xảy ra lỗi khi gửi thông báo.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getAllTransactions(Request $request)
    {
        try {
            $transactions = $this->borrowTransactionService->getAllTransactions();
            return response()->json([
                'success' => true,
                'data' => $transactions,
                'message' => 'Danh sách giao dịch mượn trả thành công'
            ]);
        } catch (ErrorsException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
    public function getTransactionDetails($id)
    {
        try {
            $transaction = $this->borrowTransactionService->getTransactionDetails($id);
            return response()->json([
                'success' => true,
                'data' => $transaction,
                'message' => 'Chi tiết giao dịch mượn trả'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Giao dịch không tồn tại'], 404);
        } catch (ErrorsException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }



    public function getHistory()
    {
        $userId = Auth::user()->id; // Lấy ID người dùng hiện tại
        try {
            $history = $this->borrowTransactionService->getTransactionHistory(userId: $userId);
            return response()->json(data: [
                'success' => true,
                'data' => $history,
                'message' => 'Danh sách lịch sử mượn sách'
            ]);
        } catch (ErrorsException $e) {
            return response()->json(data: ['error' => $e->getMessage()], status: $e->getCode());
        }
    }



    // Lấy tất cả sách đã mượn của một người dùng
    public function getBorrowedBooksByUser()
    {
        $userId = Auth::user()->id; // Lấy ID người dùng hiện tại
        try {
            $books = $this->borrowTransactionService->getBorrowedBooksByUser(userId: $userId);
            return response()->json(data: [
                'success' => true,
                'data' => $books,
                'message' => 'Danh sách sách đã mượn'
            ]);
        } catch (ErrorsException $e) {
            return response()->json(data: ['error' => $e->getMessage()], status: $e->getCode());
        }
    }

    // Thống kê


}