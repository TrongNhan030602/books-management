<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\NotificationStatusEnum;
use App\Enums\NotificationTypeEnum;

class Notification extends Model
{
    protected $fillable = ['user_id', 'message', 'type', 'status'];

    public function user()
    {
        return $this->belongsTo(related: User::class);
    }

    // Getter cho trạng thái thông báo
    public function getStatusAttribute($value): NotificationStatusEnum
    {
        return NotificationStatusEnum::from(value: $value);
    }

    // Getter cho loại thông báo
    public function getTypeAttribute($value): NotificationTypeEnum
    {
        return NotificationTypeEnum::from(value: $value);
    }
}