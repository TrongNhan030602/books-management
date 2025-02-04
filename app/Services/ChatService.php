<?php

namespace App\Services;

use App\Interfaces\ChatRepositoryInterface;

class ChatService
{
    protected $chatRepository;

    public function __construct(ChatRepositoryInterface $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }

    // Kiểm tra hoặc tạo phòng chat riêng tư giữa user và admin
    public function checkOrCreatePrivateRoom($userId)
    {
        $adminId = config(key: 'constants.admin_id');
        return $this->chatRepository->getOrCreatePrivateRoom(userId: $userId, adminId: $adminId);
    }

    // Lấy danh sách phòng chat của người dùng
    public function getUserRooms($userId)
    {
        return $this->chatRepository->getRoomsByUserId(userId: $userId);
    }

    // Lấy danh sách phòng chat mà admin tham gia
    public function getAdminRooms($adminId)
    {
        return $this->chatRepository->getRoomsByAdminId(adminId: $adminId);
    }

    // Lấy tin nhắn trong một phòng chat
    public function getRoomMessages($roomId)
    {
        return $this->chatRepository->getMessagesByRoomId(roomId: $roomId);
    }

    // Gửi tin nhắn trong một phòng chat
    public function sendMessageToRoom($roomId, $message)
    {
        $chat = $this->chatRepository->sendMessage(roomId: $roomId, data: ['message' => $message, 'user_id' => auth()->id()]);
        // Tải thông tin người gửi cùng với tin nhắn
        $chat->load('user');
        return $chat;
    }
}