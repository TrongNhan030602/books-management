<?php

namespace App\Interfaces;


interface NotificationRepositoryInterface
{
    public function getAllNotificationsByUser($userId);
    public function createNotification(array $data);
    public function markAsRead($id);
    public function deleteNotification($id);
    public function getUnreadNotificationsByUser($userId);
    public function getUnreadCountByUser($userId);
}