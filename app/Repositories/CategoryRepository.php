<?php
namespace App\Repositories;

use App\Models\Category;
use App\Interfaces\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    // Tìm thể loại theo ID
    public function findCategoryById(int $id)
    {
        return Category::find($id);
    }

    // Tạo mới thể loại
    public function createCategory(array $data)
    {
        return Category::create($data);
    }

    // Cập nhật thể loại
    public function updateCategory(int $id, array $data)
    {
        $category = Category::find($id);
        if ($category) {
            $category->update($data);
        }
        return $category;
    }

    // Xóa thể loại
    public function deleteCategory(int $id)
    {
        return Category::destroy($id);
    }

    // Lấy danh sách thể loại
    public function getAllCategories()
    {
        return Category::all();
    }
    public function searchCategories(string $query)
    {
        return Category::where('name', 'like', '%' . $query . '%')
            ->orWhere('description', 'like', '%' . $query . '%')
            ->get();
    }
}