<?php

namespace App\Enums;

enum BorrowTransactionStatusEnum: string
{
    case PENDING = 'pending'; // Chờ duyệt mượn (đã gửi yêu cầu)
    case APPROVED = 'approved'; // Đã duyệt mượn (đang mượn)
    case RETURNED = 'returned'; // Chờ duyệt trả
    case COMPLETED = 'completed'; // Đã xác nhận trả (có thể mượn lại)
    case CANCELLED = 'cancelled'; // Đã hủy yêu cầu mượn hoặc bị từ chối (có thể mượn lại)
    case OVERDUE = 'overdue'; // Trạng thái mới cho sách quá hạn
}