<?php

namespace App\Interfaces;

interface BookImageRepositoryInterface
{
    public function createImages(int $bookId, array $images);
    public function getImagesByBookId(int $bookId);
    public function deleteImage(int $bookId, string $imageUrl);
}