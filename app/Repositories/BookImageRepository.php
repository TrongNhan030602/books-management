<?php

namespace App\Repositories;

use App\Models\BookImage;
use App\Interfaces\BookImageRepositoryInterface;

class BookImageRepository implements BookImageRepositoryInterface
{
    public function createImages(int $bookId, array $images)
    {
        foreach ($images as $imageUrl) {
            BookImage::create(attributes: [
                'book_id' => $bookId,
                'image_url' => $imageUrl
            ]);
        }
    }
    public function getImagesByBookId(int $bookId)
    {
        return BookImage::where(column: 'book_id', operator: $bookId)->pluck(column: 'image_url')->toArray();
    }
    public function deleteImage(int $bookId, string $imageUrl)
    {
        BookImage::where(column: 'book_id', operator: $bookId)->where(column: 'image_url', operator: $imageUrl)->delete();
    }
}