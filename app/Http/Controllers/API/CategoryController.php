<?php
namespace App\Http\Controllers\API;

use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use Illuminate\Http\JsonResponse;
use App\Services\CategoryService;
use App\Http\Controllers\Controller;
use App\Exceptions\ErrorsException;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    // Lấy danh sách thể loại
    public function index(): JsonResponse
    {
        try {
            $categories = $this->categoryService->getAllCategories();
            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Danh sách thể loại đã được lấy thành công.'
            ], 200);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    // Lấy thông tin thể loại theo ID
    public function show(int $id): JsonResponse
    {
        try {
            $category = $this->categoryService->findCategoryById($id);

            if ($category) {
                return response()->json([
                    'success' => true,
                    'data' => $category,
                    'message' => 'Thông tin thể loại đã được lấy thành công.'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Thể loại không tồn tại.'
            ], 404);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }


    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $category = $this->categoryService->createCategory($request->validated());

            return response()->json([
                'success' => true,
                'data' => $category,
                'message' => 'Thể loại đã được tạo thành công.'
            ], 201);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }


    // Cập nhật thể loại
    // Trong CategoryController.php

    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        try {
            $category = $this->categoryService->updateCategory($id, $request->validated());

            if ($category) {
                return response()->json([
                    'success' => true,
                    'data' => $category,
                    'message' => 'Thể loại đã được cập nhật thành công.'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Thể loại không tồn tại hoặc có lỗi xảy ra khi cập nhật.'
            ], 404);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }


    // Xóa thể loại
    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->categoryService->deleteCategory($id);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thể loại đã được xóa thành công.'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Thể loại không tồn tại hoặc có lỗi xảy ra khi xóa.'
            ], 404);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }
    // Trong CategoryController.php

    public function search(string $query): JsonResponse
    {
        try {
            $categories = $this->categoryService->searchCategories(query: $query);

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Kết quả tìm kiếm thể loại.'
            ], 200);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

}