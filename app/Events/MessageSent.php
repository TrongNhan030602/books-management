<?php
namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class MessageSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;

    // Khởi tạo sự kiện với đối tượng Chat
    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    // Phát sóng sự kiện trên PresenceChannel của phòng chat
    public function broadcastOn()
    {
        return new PresenceChannel('chat-room.' . $this->chat->chat_room_id);
    }

    // Dữ liệu broadcast cho người nghe (client)
    public function broadcastWith()
    {
        return [
            'id' => $this->chat->id,
            'message' => $this->chat->message,
            'user' => $this->chat->user,  // Thông tin người gửi
            'created_at' => $this->chat->created_at,
        ];
    }
}