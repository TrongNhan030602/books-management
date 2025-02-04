<?php
namespace App\Interfaces;

interface ChatRepositoryInterface
{
    public function getOrCreatePrivateRoom($userId, $adminId);
    public function getRoomsByAdminId($adminId);
    public function getRoomsByUserId($userId);
    public function getMessagesByRoomId($roomId);
    public function sendMessage($roomId, array $data);
}