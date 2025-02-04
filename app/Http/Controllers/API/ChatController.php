<?php

namespace App\Http\Controllers\API;

use App\Services\ChatService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    // Kiểm tra hoặc tạo phòng chat riêng tư
    public function checkOrCreatePrivateRoom(Request $request)
    {
        try {
            $chatRoom = $this->chatService->checkOrCreatePrivateRoom(userId: $request->user()->id);
            return response()->json([
                'success' => true,
                'data' => ['chatRoom' => $chatRoom],
                'message' => 'Phòng chat riêng được tạo thành công.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể kiểm tra hoặc tạo phòng chat riêng.'
            ], 500);
        }
    }

    // Lấy danh sách phòng chat của người dùng
    public function getRooms(Request $request)
    {
        try {
            $rooms = $this->chatService->getUserRooms($request->user()->id);
            return response()->json([
                'success' => true,
                'data' => $rooms,
                'message' => 'Lấy danh sách phòng chat thành công.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách phòng chat.'
            ], 500);
        }
    }

    // Lấy danh sách phòng chat của admin
    public function getAdminRooms(Request $request)
    {
        try {
            // Lấy adminId từ cấu hình hoặc session
            $adminId = config(key: 'constants.admin_id');

            $rooms = $this->chatService->getAdminRooms($adminId);
            return response()->json([
                'success' => true,
                'data' => $rooms,
                'message' => 'Lấy danh sách phòng chat của admin thành công.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách phòng chat của admin.'
            ], 500);
        }
    }

    // Lấy tin nhắn trong một phòng chat
    public function getMessages($roomId)
    {
        try {
            $messages = $this->chatService->getRoomMessages($roomId);
            return response()->json([
                'success' => true,
                'data' => $messages,
                'message' => 'Lấy tin nhắn thành công.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy tin nhắn trong phòng chat.'
            ], 500);
        }
    }

    // Gửi tin nhắn trong phòng chat
    public function sendMessage(Request $request, $roomId)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        try {
            $message = $this->chatService->sendMessageToRoom($roomId, $request->message);

            return response()->json([
                'success' => true,
                'data' => [
                    'chat' => $message
                ],
                'message' => 'Gửi tin nhắn thành công.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi tin nhắn. Vui lòng thử lại sau.'
            ], 500);
        }
    }
}