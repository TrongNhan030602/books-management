<?php

namespace App\Repositories;

use App\Models\Book;
use App\Models\BookImage;
use App\Enums\BookStatusEnum;
use Illuminate\Support\Facades\Storage;
use App\Interfaces\BookRepositoryInterface;

class BookRepository implements BookRepositoryInterface
{
    public function findBookById(int $id)
    {
        return Book::with(relations: ['images', 'borrowTransactions'])->find(id: $id);
    }
    public function getUserBooksAndTransactions(int $userId)
    {
        return Book::with(relations: [
            'borrowTransactions' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }
        ])->get();
    }



    public function updateBookStatus(int $id, string $status)
    {
        $book = Book::find(id: $id);
        if ($book) {
            $book->status = $status;
            $book->save();
        }
        return $book;
    }
    public function getBookDetails($bookId, $userId)
    {
        $book = Book::with(['images', 'reviews'])->findOrFail($bookId);

        // Lấy các giao dịch đã hoàn thành
        $completedTransactions = $book->borrowTransactions()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->get();

        // Lấy tất cả giao dịch của người dùng với sách này
        $allTransactions = $book->borrowTransactions()->where('user_id', $userId)->get();

        return [
            'book' => $book,
            'user_transactions' => $completedTransactions, // Chỉ trả về giao dịch đã hoàn thành
            'other_transactions' => $allTransactions // Trả về tất cả giao dịch
        ];
    }


    public function advancedSearchBooks(array $filters, int $userId)
    {
        $query = Book::with([
            'borrowTransactions' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }
        ]);

        if (!empty($filters['keyword'])) {
            $query->where('title', 'like', '%' . $filters['keyword'] . '%');
        }

        if (!empty($filters['author'])) {
            $query->where('author', 'like', '%' . $filters['author'] . '%');
        }

        if (!empty($filters['published_year'])) {
            $query->where('published_year', $filters['published_year']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['publisher_id'])) {
            $query->where('publisher_id', $filters['publisher_id']);
        }
        if (!empty($filters['location'])) {
            $query->where('location', 'like', '%' . $filters['location'] . '%');
        }

        return $query->get();
    }

    public function checkBookAvailability(int $id): bool
    {
        $book = Book::find(id: $id);
        // Kiểm tra nếu sách tồn tại và có ít nhất 1 bản sao
        return $book && $book->quantity > 0;
    }



    public function updateBookQuantity(int $id, int $quantity)
    {
        $book = Book::find(id: $id);
        if ($book) {
            $book->quantity = $quantity;
            $book->save();
        }
        return $book;
    }

    public function createBook(array $data, $file = null)
    {
        // Tạo sách mới với vị trí và thể loại
        $book = Book::create($data);
        if ($file) {
            $this->uploadBookCover(id: $book->id, file: $file);
        }

        return $book;
    }

    public function updateBook(int $id, array $data, $file = null)
    {
        $book = Book::find(id: $id);
        if ($book) {
            $book->update($data);
            if ($file) {
                $this->uploadBookCover(id: $book->id, file: $file);
            }
        }
        return $book;
    }



    public function deleteBook(int $id)
    {
        return Book::destroy($id);
    }

    public function searchBooks(string $keyword)
    {
        return Book::with(relations: 'borrowTransactions')->where(column: 'title', operator: 'like', value: "%$keyword%")->get();
    }
    public function createImages(int $bookId, array $images)
    {
        foreach ($images as $imageUrl) {
            BookImage::create(attributes: [
                'book_id' => $bookId,
                'image_url' => $imageUrl
            ]);
        }
    }


    public function uploadBookCover(int $id, $file)
    {
        $book = Book::find(id: $id);
        if ($book) {
            $this->deleteBookCover(id: $id);

            $originalName = $file->getClientOriginalName();
            $fileName = pathinfo(path: $originalName, flags: PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            // Đảm bảo tên tệp tin không chứa ký tự đặc biệt
            $fileName = preg_replace(pattern: '/[^a-zA-Z0-9_\-]/', replacement: '_', subject: $fileName);

            // Tạo tên tệp tin mới
            $newFileName = $fileName . '.' . $extension;

            // Lưu hình ảnh mới
            $path = $file->storeAs('book_covers', $newFileName, 'public');
            $book->cover_image = $path;
            $book->save();
        }
        return $book;
    }



    public function deleteBookCover(int $id)
    {
        $book = Book::find(id: $id);
        if ($book && $book->cover_image) {
            // Xóa hình ảnh từ lưu trữ
            Storage::disk('public')->delete(paths: $book->cover_image);
            $book->cover_image = null;
            $book->save();
        }
        return $book;
    }
}