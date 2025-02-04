<?php

namespace App\Services;

use App\Interfaces\NotificationRepositoryInterface;

class NotificationService
{
    protected $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function getAllNotificationsByUser($userId)
    {
        return $this->notificationRepository->getAllNotificationsByUser($userId);
    }
    public function getUnreadCountByUser($userId)
    {
        return $this->notificationRepository->getUnreadCountByUser($userId);
    }


    public function createNotification($userId, $message, $type)
    {
        $data = [
            'user_id' => $userId,
            'message' => $message,
            'type' => $type,
            'status' => 'unread'
        ];
        return $this->notificationRepository->createNotification($data);
    }

    public function markAsRead($id)
    {
        return $this->notificationRepository->markAsRead($id);
    }

    public function deleteNotification($id)
    {
        return $this->notificationRepository->deleteNotification($id);
    }

    public function getUnreadNotificationsByUser($userId)
    {
        return $this->notificationRepository->getUnreadNotificationsByUser($userId);
    }
}