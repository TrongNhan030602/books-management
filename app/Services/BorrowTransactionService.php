<?php

namespace App\Services;

use App\Enums\BookStatusEnum;
use App\Enums\NotificationTypeEnum;
use App\Exceptions\ErrorsException;
use Illuminate\Support\Facades\Log;
use App\Enums\BorrowTransactionStatusEnum;
use App\Interfaces\BookRepositoryInterface;
use App\Interfaces\PenaltyRepositoryInterface;
use App\Interfaces\NotificationRepositoryInterface;
use App\Interfaces\BorrowTransactionRepositoryInterface;
use Carbon\Carbon;

class BorrowTransactionService
{
    protected $borrowTransactionRepository;
    protected $bookRepository;
    protected $notificationRepository;
    protected $penaltyRepository;

    public function __construct(
        BorrowTransactionRepositoryInterface $borrowTransactionRepository,
        BookRepositoryInterface $bookRepository,
        NotificationRepositoryInterface $notificationRepository,
        PenaltyRepositoryInterface $penaltyRepository,
    ) {
        $this->borrowTransactionRepository = $borrowTransactionRepository;
        $this->bookRepository = $bookRepository;
        $this->notificationRepository = $notificationRepository;
        $this->penaltyRepository = $penaltyRepository;
    }

    public function createBorrowTransaction(int $userId, array $data)
    {
        $data['user_id'] = $userId;
        $data['status'] = BorrowTransactionStatusEnum::PENDING->value;

        // Kiểm tra xem sách có đủ bản sao để mượn không
        if (!$this->bookRepository->checkBookAvailability(id: $data['book_id'])) {
            throw new ErrorsException(message: 'Sách hiện không có sẵn', code: 400);
        }

        // Kiểm tra xem người dùng đã mượn cuốn sách này chưa
        $existingTransaction = $this->borrowTransactionRepository->getActiveTransactionByUserAndBook($userId, $data['book_id']);
        if ($existingTransaction) {
            throw new ErrorsException(message: 'Bạn đã mượn cuốn sách này rồi.', code: 400);
        }

        // Tạo giao dịch mượn
        $borrowTransaction = $this->borrowTransactionRepository->createTransaction(userId: $userId, bookId: $data['book_id'], data: $data);

        // Không cập nhật trạng thái sách ở đây

        $this->notificationRepository->createNotification([
            'user_id' => $userId,
            'message' => 'Yêu cầu mượn sách đã được gửi',
            'type' => NotificationTypeEnum::INFO->value,
        ]);

        return $borrowTransaction;
    }


    public function approveBorrowTransaction(int $id)
    {
        // Xác thực giao dịch mượn
        $borrowTransaction = $this->validateBorrowTransaction($id, BorrowTransactionStatusEnum::PENDING);

        // Tìm sách tương ứng với giao dịch
        $book = $this->bookRepository->findBookById(id: $borrowTransaction->book_id);
        if ($book->quantity <= 0) {
            throw new ErrorsException(message: 'Không có bản sao nào của cuốn sách', code: 400);
        }

        // Giảm số lượng sách
        $book->quantity -= 1;
        $book->save();

        // Cập nhật trạng thái giao dịch thành APPROVED
        $borrowTransaction = $this->borrowTransactionRepository->updateTransaction(id: $id, data: ['status' => BorrowTransactionStatusEnum::APPROVED->value]);

        // Gửi thông báo cho người dùng
        $this->sendNotification($borrowTransaction->user_id, 'Yêu cầu mượn sách của bạn đã được phê duyệt.', NotificationTypeEnum::SUCCESS->value);

        // Kiểm tra xem sách đã quá hạn chưa
        if ($borrowTransaction->return_date < now()) {
            $this->sendNotification($borrowTransaction->user_id, 'Lưu ý: Sách của bạn đã quá hạn!', NotificationTypeEnum::WARNING->value);
        }

        return $borrowTransaction;
    }
    public function notifyUsersOfUpcomingDueDate()
    {
        $daysBeforeDue = config(key: 'notification.days_before_due', default: 2);
        // Lấy các giao dịch sắp đến hạn 
        $transactions = $this->borrowTransactionRepository->getTransactionsWithDueDateInDays($daysBeforeDue);
        if ($transactions->isNotEmpty()) {
            foreach ($transactions as $transaction) {
                if ($transaction->user && $transaction->book) {
                    // Đảm bảo return_date là một thể hiện Carbon
                    $returnDate = is_string(value: $transaction->return_date) ? Carbon::parse(time: $transaction->return_date) : $transaction->return_date;
                    $this->sendNotification(
                        userId: $transaction->user_id,
                        message: 'Sách "' . $transaction->book->title . '" của bạn sẽ đến hạn trả vào ngày ' . $returnDate->format('d-m-Y'),
                        type: NotificationTypeEnum::WARNING->value
                    );
                } else {
                    Log::error('Giao dịch không hợp lệ cho user_id: ' . ($transaction->user->id ?? 'không có') . ', book_id: ' . ($transaction->book->id ?? 'không có'));
                }
            }
        } else {
            Log::info('Không có giao dịch nào để xử lý.');
        }
    }


    public function extendBorrowTransaction(int $transactionId, int $days)
    {
        $borrowTransaction = $this->borrowTransactionRepository->findBorrowTransactionById($transactionId);

        if (!$borrowTransaction || $borrowTransaction->status !== BorrowTransactionStatusEnum::APPROVED->value) {
            throw new ErrorsException(message: 'Giao dịch không hợp lệ hoặc không được phép gia hạn.', code: 400);
        }

        // Kiểm tra số lần gia hạn đã thực hiện
        $maxExtensions = config('custom.max_extensions', default: 1); // Lấy từ config (mặc định tối đa 3 lần)
        if ($borrowTransaction->extension_count >= $maxExtensions) {
            throw new ErrorsException(
                message: "Bạn đã đạt đến số lần gia hạn tối đa ({$maxExtensions} lần).",
                code: 400
            );
        }

        // Kiểm tra số ngày gia hạn hợp lệ
        $maxExtensionDays = config('custom.max_extension_days', 7); // Lấy số ngày tối đa từ config
        if ($days > $maxExtensionDays) {
            throw new ErrorsException(
                message: "Bạn chỉ được phép gia hạn tối đa {$maxExtensionDays} ngày.",
                code: 400
            );
        }

        // Cập nhật ngày trả và số lần gia hạn
        $newReturnDate = Carbon::parse($borrowTransaction->return_date)->addDays($days);
        $this->borrowTransactionRepository->updateTransaction($transactionId, [
            'return_date' => $newReturnDate,
            'extension_count' => $borrowTransaction->extension_count + 1, // Tăng số lần gia hạn
        ]);

        // Gửi thông báo cho người dùng
        $this->sendNotification(
            userId: $borrowTransaction->user_id,
            message: "Giao dịch mượn sách của bạn đã được gia hạn. Ngày trả mới là " . $newReturnDate->format('d-m-Y'),
            type: NotificationTypeEnum::SUCCESS->value
        );

        return $borrowTransaction->fresh(); // Lấy dữ liệu mới nhất sau khi cập nhật
    }


    public function returnBook(int $borrowTransactionId)
    {
        $borrowTransaction = $this->validateBorrowTransaction(
            id: $borrowTransactionId,
            expectedStatus: BorrowTransactionStatusEnum::APPROVED
        );

        $this->borrowTransactionRepository->updateTransaction(
            id: $borrowTransactionId,
            data: ['status' => BorrowTransactionStatusEnum::RETURNED->value]
        );

        if ($borrowTransaction->return_date < now()) {
            // Tạo hình phạt cho người dùng nếu trả sách muộn
            $penaltyData = [
                'amount' => config(key: 'custom.late_fee', default: 10000),// Mức phạt
                'reason' => 'Trả sách muộn',
                'due_date' => now()->addDays(value: 7), // Hạn nộp phạt
            ];
            $this->penaltyRepository->createPenalty(
                userId: $borrowTransaction->user_id,
                borrowTransactionId: $borrowTransactionId, // ID giao dịch mượn
                data: $penaltyData
            );

            // Gửi thông báo về hình phạt
            $this->sendNotification(
                userId: $borrowTransaction->user_id,
                message: 'Bạn đã bị phạt ' . $penaltyData['amount'] . ' vì trả sách muộn. Lý do: ' . $penaltyData['reason'] . '. Hạn nộp phạt là ' . $penaltyData['due_date']->format(format: 'd-m-Y') . '.',
                type: NotificationTypeEnum::WARNING->value
            );
        }

        return $borrowTransaction;
    }





    public function completeReturn(int $id, ?string $reason = null)
    {
        $borrowTransaction = $this->validateBorrowTransaction(id: $id, expectedStatus: BorrowTransactionStatusEnum::RETURNED);

        $this->bookRepository->updateBookStatus(id: $borrowTransaction->book_id, status: BookStatusEnum::AVAILABLE->value);
        $book = $this->bookRepository->findBookById(id: $borrowTransaction->book_id);
        $book->quantity += 1;
        $book->save();

        $this->borrowTransactionRepository->updateTransaction(id: $id, data: [
            'status' => BorrowTransactionStatusEnum::COMPLETED->value,
            'reason' => $reason,
        ]);

        $this->sendNotification(userId: $borrowTransaction->user_id, message: 'Bạn đã trả sách thành công.', type: NotificationTypeEnum::INFO->value);

        return $borrowTransaction;
    }

    public function rejectBorrowTransaction(int $id, string $reason)
    {
        $borrowTransaction = $this->validateBorrowTransaction($id, BorrowTransactionStatusEnum::PENDING);

        $borrowTransaction = $this->borrowTransactionRepository->updateTransaction(id: $id, data: [
            'status' => BorrowTransactionStatusEnum::CANCELLED->value,
            'reason' => $reason,
        ]);

        $this->bookRepository->updateBookStatus(id: $borrowTransaction->book_id, status: BookStatusEnum::AVAILABLE->value);
        $this->sendNotification(userId: $borrowTransaction->user_id, message: 'Yêu cầu mượn sách của bạn đã bị từ chối: ' . $reason, type: NotificationTypeEnum::ERROR->value);

        return $borrowTransaction;
    }


    public function cancelBorrowTransaction(int $id, int $userId)
    {
        $borrowTransaction = $this->validateBorrowTransaction(id: $id, expectedStatus: BorrowTransactionStatusEnum::PENDING);
        if ($borrowTransaction->user_id !== $userId) {
            throw new ErrorsException(message: 'Bạn không có quyền hủy giao dịch này', code: 403);
        }
        $this->bookRepository->updateBookStatus(id: $borrowTransaction->book_id, status: BookStatusEnum::AVAILABLE->value);

        return $this->borrowTransactionRepository->cancelTransaction(id: $id);
    }

    public function getAllTransactions()
    {
        return $this->borrowTransactionRepository->getAllTransactionsWithDetails();
    }
    public function getTransactionDetails($transactionId)
    {
        $transaction = $this->borrowTransactionRepository->getTransactionById(transactionId: $transactionId);

        if (!$transaction) {
            throw new ErrorsException(message: 'Giao dịch không tồn tại', code: 404);
        }

        return $transaction;
    }

    public function getTransactionHistory(int $userId)
    {
        return $this->borrowTransactionRepository->getTransactionHistoryByUser(userId: $userId);
    }

    public function findBookById(int $bookId)
    {
        return $this->bookRepository->findBookById(id: $bookId);
    }

    public function deleteBorrowTransaction(int $id)
    {
        return $this->borrowTransactionRepository->deleteTransaction(id: $id);
    }

    public function getAllTransactionsByUser(int $userId)
    {
        return $this->borrowTransactionRepository->getAllTransactionsByUser($userId);
    }

    public function getBorrowedBooksByUser(int $userId)
    {
        return $this->borrowTransactionRepository->getBorrowedBooksByUser($userId);
    }

    public function getPendingTransactions()
    {
        return $this->borrowTransactionRepository->getPendingTransactions();
    }

    public function getReturnTransactions()
    {
        return $this->borrowTransactionRepository->getReturnTransactions();
    }

    public function notifyUsersOfOverdueBooks()
    {
        $overdueTransactions = $this->borrowTransactionRepository->getOverdueTransactions();

        foreach ($overdueTransactions as $transaction) {
            $this->sendNotification(userId: $transaction->user_id, message: 'Sách của bạn đã quá hạn: ' . $transaction->book->title, type: NotificationTypeEnum::WARNING->value);
        }
    }

    protected function validateBorrowTransaction(int $id, BorrowTransactionStatusEnum $expectedStatus)
    {
        $borrowTransaction = $this->borrowTransactionRepository->findBorrowTransactionById(id: $id);
        if (!$borrowTransaction || $borrowTransaction->status !== $expectedStatus->value) {
            throw new ErrorsException(message: 'Giao dịch không hợp lệ', code: 400);
        }
        return $borrowTransaction;
    }

    protected function sendNotification(int $userId, string $message, string $type)
    {
        $this->notificationRepository->createNotification(data: [
            'user_id' => $userId,
            'message' => $message,
            'type' => $type,
        ]);
    }


    // Thống kê
    public function getBorrowStats(int $userId = null)
    {
        if ($userId) {
            $totalTransactions = $this->borrowTransactionRepository->getTotalTransactionsByUser($userId);
            $transactionsByStatus = $this->borrowTransactionRepository->getTransactionsByStatus($userId);
            $transactionsByMonth = $this->borrowTransactionRepository->getTransactionsByMonth($userId);
            $mostBorrowedBooks = $this->borrowTransactionRepository->getMostBorrowedBooksByMonth($userId);
        } else {
            $totalTransactions = $this->borrowTransactionRepository->getTotalTransactionsForAllUsers();
            $transactionsByStatus = $this->borrowTransactionRepository->getTransactionsByStatusForAllUsers();
            $transactionsByMonth = $this->borrowTransactionRepository->getTransactionsByMonthForAllUsers();
            $mostBorrowedBooks = $this->borrowTransactionRepository->getMostBorrowedBooksByMonth();
        }

        return [
            'total_transactions' => $totalTransactions,
            'transactions_by_status' => $transactionsByStatus,
            'transactions_by_month' => $transactionsByMonth,
            'most_borrowed_books_by_month' => $mostBorrowedBooks,
        ];
    }



}