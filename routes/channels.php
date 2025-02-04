<?php

use App\Models\ChatRoom;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('chat-room.{chatRoomId}', function ($user, $chatRoomId) {
    // Kiểm tra xem người dùng có quyền tham gia phòng chat này hay không
    $chatRoom = ChatRoom::find($chatRoomId);

    // Chỉ cho phép tham gia nếu người dùng là chủ hoặc admin của phòng chat
    return $chatRoom && ($chatRoom->user_id === $user->id || $chatRoom->admin_id === $user->id);
});