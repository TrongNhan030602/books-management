<?php

namespace App\Interfaces;

interface CategoryRepositoryInterface
{
    public function findCategoryById(int $id);
    public function createCategory(array $data);
    public function updateCategory(int $id, array $data);
    public function deleteCategory(int $id);
    public function getAllCategories();
    public function searchCategories(string $query);
}