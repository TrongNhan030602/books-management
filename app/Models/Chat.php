<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'chat_room_id',
        'user_id',
        'message'
    ];

    // Mối quan hệ với ChatRoom
    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class);
    }

    // Mối quan hệ với User (người gửi)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}