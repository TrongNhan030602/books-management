<?php

namespace App\Enums;

enum NotificationStatusEnum: string
{
    case UNREAD = 'unread'; // Thông báo chưa được đọc
    case READ = 'read';     // Thông báo đã được đọc
}