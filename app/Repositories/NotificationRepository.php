<?php

namespace App\Repositories;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use App\Interfaces\NotificationRepositoryInterface;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function createNotification(array $data)
    {
        $notification = Notification::create(attributes: $data);
        return $notification;
    }






    public function getAllNotificationsByUser($userId)
    {
        return Notification::where(column: 'user_id', operator: $userId)->get();
    }

    public function markAsRead($id)
    {
        $notification = Notification::find(id: $id);
        if ($notification) {
            $notification->update(['status' => 'read']);
            return $notification;
        }
        return null;
    }

    public function deleteNotification($id)
    {
        $notification = Notification::find(id: $id);
        if ($notification) {
            $notification->delete();
            return true;
        }
        return false;
    }

    public function getUnreadNotificationsByUser($userId)
    {
        return Notification::where(column: 'user_id', operator: $userId)
            ->where(column: 'status', operator: 'unread')
            ->get();
    }
    public function getUnreadCountByUser($userId)
    {
        return Notification::where(column: 'user_id', operator: $userId)
            ->where(column: 'status', operator: 'unread')
            ->count();
    }
}