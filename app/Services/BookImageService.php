<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use App\Interfaces\BookImageRepositoryInterface;

class BookImageService
{
    protected $bookImageRepository;

    public function __construct(BookImageRepositoryInterface $bookImageRepository)
    {
        $this->bookImageRepository = $bookImageRepository;
    }

    public function uploadImages(int $bookId, array $files)
    {
        $uploadedImages = [];

        foreach ($files as $file) {
            $originalFilename = $file->getClientOriginalName();
            $filePath = 'book_images/' . $originalFilename;

            if (Storage::disk('public')->exists($filePath)) {
                return [
                    'success' => false,
                    'message' => "File ảnh '$originalFilename' đã tồn tại.",
                    'existing_image' => $filePath,
                ];
            }

            $path = $file->storeAs('book_images', $originalFilename, 'public');

            $uploadedImages[] = $path;
        }

        // Lưu các đường dẫn ảnh vào CSDL
        $this->bookImageRepository->createImages(bookId: $bookId, images: $uploadedImages);

        return [
            'success' => true,
            'images' => $uploadedImages,
        ];
    }

    public function getImagesByBookId(int $bookId)
    {
        return $this->bookImageRepository->getImagesByBookId(bookId: $bookId);
    }
    public function deleteImage(int $bookId, string $imageName)
    {
        $filePath = 'book_images/' . $imageName;

        if (!Storage::disk('public')->exists($filePath)) {
            return [
                'success' => false,
                'message' => "Ảnh không tồn tại.",
            ];
        }

        Storage::disk('public')->delete(paths: $filePath);

        $this->bookImageRepository->deleteImage(bookId: $bookId, imageUrl: $filePath);

        return [
            'success' => true,
            'message' => 'Ảnh đã được xóa thành công.'
        ];
    }
}