<?php

namespace App\Repositories;

use App\Models\Chat;
use App\Models\User;
use App\Models\ChatRoom;
use App\Interfaces\ChatRepositoryInterface;

class ChatRepository implements ChatRepositoryInterface
{
    // Lấy phòng chat của người dùng và admin
    public function getOrCreatePrivateRoom($userId, $adminId)
    {
        // Kiểm tra xem phòng chat đã tồn tại chưa
        $existingRoom = ChatRoom::where('user_id', $userId)
            ->where('admin_id', $adminId)
            ->where('is_private', true)
            ->first();

        if ($existingRoom) {
            return $existingRoom;
        }

        $user = User::find($userId);

        return ChatRoom::create([
            'user_id' => $userId,
            'admin_id' => $adminId,
            'is_private' => true,
            'name' => $user->last_name . ' ' . $user->first_name, // Đặt tên phòng theo tên người tạo
        ]);
    }



    // Lấy các phòng chat mà admin tham gia
    public function getRoomsByAdminId($adminId)
    {
        // Lấy các phòng chat mà admin tham gia
        return ChatRoom::where('admin_id', $adminId)->get();
    }

    // Lấy phòng chat của người dùng
    public function getRoomsByUserId($userId)
    {
        return ChatRoom::where('user_id', $userId)->get();
    }

    // Lấy tin nhắn trong một phòng chat
    public function getMessagesByRoomId($roomId)
    {
        return Chat::where('chat_room_id', $roomId)->with('user')->get();
    }

    // Gửi tin nhắn trong phòng chat
    public function sendMessage($roomId, array $data)
    {
        return Chat::create(array_merge($data, ['chat_room_id' => $roomId]));
    }
}