<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    // Lấy tất cả thông báo của người dùng
    public function getNotificationsByUser()
    {
        $userId = Auth::id(); // Lấy userId của người dùng đang đăng nhập
        $notifications = $this->notificationService->getAllNotificationsByUser(userId: $userId);
        return response()->json(data: $notifications);
    }

    // Lấy tất cả thông báo chưa đọc của người dùng
    public function getUnreadNotificationsByUser()
    {
        $userId = Auth::id(); // Lấy userId của người dùng đang đăng nhập
        $unreadNotifications = $this->notificationService->getUnreadNotificationsByUser($userId);
        return response()->json(data: $unreadNotifications);
    }
    public function getUnreadCount()
    {
        $userId = Auth::id(); // Lấy userId của người dùng đang đăng nhập
        $unreadCount = $this->notificationService->getUnreadCountByUser($userId);
        return response()->json(['count' => $unreadCount]);
    }


    // Tạo thông báo mới
    public function createNotification(Request $request)
    {
        $notification = $this->notificationService->createNotification(
            userId: $request->user_id,
            message: $request->message,
            type: $request->type
        );
        return response()->json(data: $notification, status: 201);
    }

    // Đánh dấu thông báo đã đọc
    public function markAsRead($id)
    {
        $notification = $this->notificationService->markAsRead($id);
        if ($notification) {
            return response()->json(data: ['message' => 'Notification marked as read'], status: 200);
        }
        return response()->json(data: ['message' => 'Notification not found'], status: 404);
    }

    // Xóa thông báo
    public function deleteNotification($id)
    {
        $deleted = $this->notificationService->deleteNotification($id);
        if ($deleted) {
            return response()->json(data: ['message' => 'Notification deleted'], status: 200);
        }
        return response()->json(data: ['message' => 'Notification not found'], status: 404);
    }
}