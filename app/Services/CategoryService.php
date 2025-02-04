<?php
namespace App\Services;

use App\Interfaces\CategoryRepositoryInterface;

class CategoryService
{
    protected $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    // Tìm thể loại theo ID
    public function findCategoryById(int $id)
    {
        return $this->categoryRepository->findCategoryById($id);
    }

    // Tạo mới thể loại
    public function createCategory(array $data)
    {
        return $this->categoryRepository->createCategory($data);
    }

    // Cập nhật thể loại
    public function updateCategory(int $id, array $data)
    {
        return $this->categoryRepository->updateCategory($id, $data);
    }

    // Xóa thể loại
    public function deleteCategory(int $id)
    {
        return $this->categoryRepository->deleteCategory($id);
    }

    // Lấy danh sách thể loại
    public function getAllCategories()
    {
        return $this->categoryRepository->getAllCategories();
    }
    public function searchCategories(string $query)
    {
        return $this->categoryRepository->searchCategories(query: $query);
    }
}