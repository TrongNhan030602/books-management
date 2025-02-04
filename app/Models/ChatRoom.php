<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $fillable = [
        'name',
        'is_private',
        'user_id',
        'admin_id'
    ];

    // Mối quan hệ với User (người tạo phòng chat)
    public function user()
    {
        return $this->belongsTo(related: User::class, foreignKey: 'user_id');
    }

    // Mối quan hệ với Admin (nếu có)
    public function admin()
    {
        return $this->belongsTo(related: User::class, foreignKey: 'admin_id');
    }

    // Mối quan hệ với các tin nhắn trong phòng chat
    public function chats()
    {
        return $this->hasMany(related: Chat::class);
    }
}