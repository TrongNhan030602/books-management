<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\Publishers\StorePublisherRequest;
use App\Http\Requests\Publishers\UpdatePublisherRequest;
use Illuminate\Http\JsonResponse;
use App\Services\PublisherService;
use App\Http\Controllers\Controller;
use App\Exceptions\ErrorsException;

class PublisherController extends Controller
{
    protected $publisherService;

    public function __construct(PublisherService $publisherService)
    {
        $this->publisherService = $publisherService;
    }

    public function index(): JsonResponse
    {
        try {
            $publishers = $this->publisherService->getAllPublishers();
            return response()->json([
                'success' => true,
                'data' => $publishers,
                'message' => 'Danh sách nhà xuất bản đã được lấy thành công.'
            ], 200);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $publisher = $this->publisherService->findPublisherById($id);

            if ($publisher) {
                return response()->json([
                    'success' => true,
                    'data' => $publisher,
                    'message' => 'Thông tin nhà xuất bản đã được lấy thành công.'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Nhà xuất bản không tồn tại.'
            ], 404);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    public function store(StorePublisherRequest $request): JsonResponse
    {
        try {
            $publisher = $this->publisherService->createPublisher($request->validated());

            return response()->json([
                'success' => true,
                'data' => $publisher,
                'message' => 'Nhà xuất bản đã được tạo thành công.'
            ], 201);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    public function update(UpdatePublisherRequest $request, int $id): JsonResponse
    {
        try {
            $publisher = $this->publisherService->updatePublisher($id, $request->validated());

            if ($publisher) {
                return response()->json([
                    'success' => true,
                    'data' => $publisher,
                    'message' => 'Nhà xuất bản đã được cập nhật thành công.'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Nhà xuất bản không tồn tại hoặc có lỗi xảy ra khi cập nhật.'
            ], 404);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->publisherService->deletePublisher($id);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Nhà xuất bản đã được xóa thành công.'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Nhà xuất bản không tồn tại hoặc có lỗi xảy ra khi xóa.'
            ], 404);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }
    public function search(string $query): JsonResponse
    {
        try {
            $publishers = $this->publisherService->searchPublishers($query);
            return response()->json([
                'success' => true,
                'data' => $publishers,
                'message' => 'Kết quả tìm kiếm nhà xuất bản.'
            ], 200);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }
}
