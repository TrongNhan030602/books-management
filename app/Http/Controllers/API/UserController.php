<?php

namespace App\Http\Controllers\API;

use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use App\Exceptions\ErrorsException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserInfoRequest;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Hiển thị danh sách tất cả người dùng.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $users = $this->userService->getAllUsers();
            return response()->json([
                'success' => true,
                'data' => $users,
                'message' => 'Danh sách người dùng đã được lấy thành công.'
            ], 200);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách người dùng. Vui lòng thử lại sau.'
            ], $e->getCode());
        }
    }

    /**
     * Hiển thị thông tin một người dùng cụ thể.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->findUserById($id);
            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Thông tin người dùng đã được lấy thành công.'
            ], 200);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy thông tin người dùng. Vui lòng kiểm tra lại.'
            ], $e->getCode());
        }
    }
    public function getUserTransactions(int $userId): JsonResponse
    {
        try {
            $transactions = $this->userService->getUserTransactions($userId);

            return response()->json([
                'success' => true,
                'data' => $transactions,
                'message' => 'Danh sách giao dịch mượn sách của người dùng đã được lấy thành công.'
            ], 200);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy giao dịch mượn sách của người dùng. Vui lòng thử lại sau.'
            ], $e->getCode());
        }
    }

    /**
     * Tạo một người dùng mới.
     *
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());
            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Người dùng đã được tạo thành công.'
            ], 201);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo người dùng mới. Vui lòng kiểm tra lại thông tin nhập vào.'
            ], $e->getCode());
        }
    }

    /**
     * Cập nhật thông tin người dùng với tất cả các trường (dành cho Admin).
     *
     * @param UpdateUserInfoRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateUserInfo(UpdateUserInfoRequest $request, int $id): JsonResponse
    {
        try {
            $currentUser = Auth::user();

            if (!$currentUser || !$currentUser->hasRole(RoleEnum::Admin->value)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền thực hiện hành động này.'
                ], 403);
            }

            $user = $this->userService->updateUser($id, $request->validated());

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Thông tin người dùng đã được cập nhật thành công.'
            ], 200);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật thông tin người dùng. Vui lòng thử lại sau.'
            ], $e->getCode());
        }
    }

    /**
     * Xóa người dùng.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->userService->deleteUser($id);
            return response()->json([
                'success' => true,
                'message' => 'Người dùng đã được xóa thành công.'
            ], 200);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa người dùng. Vui lòng thử lại sau.'
            ], $e->getCode());
        }
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa đăng nhập. Vui lòng đăng nhập trước khi thực hiện hành động này.'
                ], 403);
            }

            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $file = $request->file('avatar');
            $user = $this->userService->uploadAvatar($user->id, $file);

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Ảnh đại diện đã được tải lên thành công.'
            ], 200);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải lên ảnh đại diện. Vui lòng thử lại sau.'
            ], $e->getCode());
        }
    }

    public function deleteAvatar(): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa đăng nhập. Vui lòng đăng nhập trước khi thực hiện hành động này.'
                ], 403);
            }

            $user = $this->userService->deleteAvatar($user->id);
            if ($user) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ảnh đại diện đã được xóa thành công.'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Người dùng không tồn tại hoặc có lỗi xảy ra khi xóa ảnh.'
            ], 404);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa ảnh đại diện. Vui lòng thử lại sau.'
            ], $e->getCode());
        }
    }
    public function search(string $keyword): JsonResponse
    {
        try {
            $users = $this->userService->searchUsers($keyword);
            return response()->json([
                'success' => true,
                'data' => $users,
                'message' => 'Kết quả tìm kiếm người dùng thành công.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tìm kiếm người dùng. Vui lòng thử lại sau.'
            ], 500);
        }
    }
}