<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Services\BookService;
use Illuminate\Http\JsonResponse;
use App\Services\BookImageService;
use App\Exceptions\ErrorsException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Books\StoreBookRequest;
use App\Http\Requests\Books\UpdateBookRequest;

class BookController extends Controller
{
    protected $bookService;
    protected $bookImageService;

    public function __construct(BookService $bookService, BookImageService $bookImageService)
    {
        $this->bookService = $bookService;
        $this->bookImageService = $bookImageService;
    }

    public function index(): JsonResponse
    {
        try {
            $books = $this->bookService->searchBooks(keyword: '');
            return response()->json(data: [
                'success' => true,
                'data' => $books->map(function ($book): array {
                    return [
                        'id' => $book->id,
                        'title' => $book->title,
                        'cover_image' => $book->cover_image,
                        'description' => $book->description,
                        'author' => $book->author,
                        'publisher_id' => $book->publisher_id,
                        'price' => $book->price,
                        'initial_quantity' => $book->initial_quantity,
                        'quantity' => $book->quantity,
                        'published_year' => $book->published_year,
                        'status' => $book->status,
                        'location' => $book->location, // Vị trí sách
                        'category_id' => $book->category_id,
                        'category' => $book->category ? $book->category->name : null, // Thể loại sách
                        'borrow_transactions' => $book->borrowTransactions,
                    ];
                }),
                'message' => 'Danh sách sách đã được lấy thành công.'
            ], status: 200);
        } catch (ErrorsException $e) {
            return response()->json(data: [
                'success' => false,
                'message' => $e->getMessage()
            ], status: $e->getCode());
        }
    }

    public function getUserBooksAndTransactions(): JsonResponse
    {
        try {
            $user = Auth::user();
            // Lấy sách và giao dịch của người dùng
            $booksWithTransactions = $this->bookService->getUserBooksAndTransactions(userId: $user->id);

            // Sử dụng tập hợp để đảm bảo không bị trùng lặp
            $uniqueBooks = $booksWithTransactions->unique('id');

            // Thêm trạng thái sách cho từng giao dịch
            $booksWithStatus = $uniqueBooks->map(function ($book) {
                // Lọc giao dịch để chỉ lấy giao dịch liên quan đến người dùng
                $borrowTransactions = $book->borrowTransactions->filter(function ($transaction) {
                    return $transaction->user_id == Auth::id(); // Lọc theo user_id
                })->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'user_id' => $transaction->user_id,
                        'borrowed_at' => $transaction->borrow_date,
                        'returned_at' => $transaction->return_date,
                        'status' => $transaction->status, // Trạng thái mượn
                        'book_status' => $this->getBookStatus(transactionStatus: $transaction->status) // Lấy trạng thái sách
                    ];
                });

                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'cover_image' => $book->cover_image,
                    'description' => $book->description,
                    'author' => $book->author,
                    'publisher_id' => $book->publisher_id,
                    'price' => $book->price,
                    'initial_quantity' => $book->initial_quantity,
                    'quantity' => $book->quantity,
                    'published_year' => $book->published_year,
                    'location' => $book->location,
                    'status' => $book->status,
                    'category' => $book->category ? $book->category->name : null,
                    'borrow_transactions' => $borrowTransactions,
                ];
            });

            return response()->json(data: [
                'success' => true,
                'data' => $booksWithStatus,
                'message' => 'Thông tin sách và giao dịch của người dùng đã được lấy thành công.'
            ], status: 200);
        } catch (ErrorsException $e) {
            return response()->json(data: [
                'success' => false,
                'message' => $e->getMessage()
            ], status: $e->getCode());
        }
    }


    // Phương thức để lấy trạng thái sách dựa trên trạng thái giao dịch
    private function getBookStatus($transactionStatus)
    {
        switch ($transactionStatus) {
            case 'pending':
                return 'Đang chờ mượn';
            case 'approved':
                return 'Đang mượn';
            case 'returned':
                return 'Đã chờ trả';
            case 'cancelled':
                return 'Có sẵn';
            case 'completed':
                return 'Có sẵn';
            case 'overdue':
                return 'Quá hạn';
            default:
                return 'Không xác định';
        }
    }


    public function getBookDetails($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $bookDetails = $this->bookService->getBookDetails($id, $user->id);

            return response()->json(data: [
                'success' => true,
                'data' => $bookDetails,
                'message' => 'Lấy thông tin chi tiết sách thành công.'
            ], status: 200);
        } catch (ErrorsException $e) {
            return response()->json(data: [
                'success' => false,
                'message' => $e->getMessage()
            ], status: $e->getCode());
        }
    }


    public function uploadBookImages(Request $request, int $bookId)
    {
        if (!$request->hasFile(key: 'images')) {
            return response()->json(data: [
                'success' => false,
                'message' => 'Không có ảnh nào được upload.'
            ], status: 400);
        }

        $files = $request->file(key: 'images');

        $uploadResult = $this->bookImageService->uploadImages(bookId: $bookId, files: $files);

        if (!$uploadResult['success']) {
            return response()->json(data: [
                'success' => false,
                'message' => $uploadResult['message'],
                'existing_image' => $uploadResult['existing_image'],
            ], status: 409); // file đã tồn tại
        }

        return response()->json(data: [
            'success' => true,
            'message' => 'Ảnh đã được upload thành công.',
            'images' => $uploadResult['images'],
        ], status: 200);
    }
    public function getBookImages(int $bookId)
    {
        $images = $this->bookImageService->getImagesByBookId(bookId: $bookId);

        if (empty($images)) {
            return response()->json(data: [
                'success' => false,
                'message' => 'Không có ảnh nào cho sách này.'
            ], status: 404);
        }

        return response()->json(data: [
            'success' => true,
            'images' => $images
        ], status: 200);
    }
    public function deleteBookImage(int $bookId, string $imageName)
    {
        $deleteResult = $this->bookImageService->deleteImage($bookId, $imageName);

        if (!$deleteResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $deleteResult['message'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ảnh đã được xóa thành công.'
        ], 200);
    }





    public function show(int $id): JsonResponse
    {
        try {
            $book = $this->bookService->findBookById(id: $id);

            if ($book) {
                return response()->json(data: [
                    'success' => true,
                    'data' => [
                        'id' => $book->id,
                        'title' => $book->title,
                        'description' => $book->description,
                        'cover_image' => $book->cover_image,
                        'images' => $book->images->pluck('image_url'),
                        'author' => $book->author,
                        'publisher_id' => $book->publisher_id,
                        'price' => $book->price,
                        'initial_quantity' => $book->initial_quantity,
                        'quantity' => $book->quantity,
                        'published_year' => $book->published_year,
                        'status' => $book->status,
                        'location' => $book->location,
                        'category_id' => $book->category_id,
                        'category' => $book->category ? $book->category->name : null,

                        'borrow_transactions' => $book->borrowTransactions->map(function ($transaction) {
                            return [
                                'id' => $transaction->id,
                                'user_id' => $transaction->user_id,
                                'borrowed_at' => $transaction->borrowed_at,
                                'returned_at' => $transaction->returned_at,
                                'status' => $transaction->status,
                            ];
                        }),
                    ],
                    'message' => 'Thông tin sách đã được lấy thành công.'
                ], status: 200);
            }

            return response()->json(data: [
                'success' => false,
                'message' => 'Sách không tồn tại.'
            ], status: 404);
        } catch (ErrorsException $e) {
            return response()->json(data: [
                'success' => false,
                'message' => $e->getMessage()
            ], status: 500);
        }
    }



    public function store(StoreBookRequest $request): JsonResponse
    {
        try {
            $file = $request->file(key: 'cover_image');
            $book = $this->bookService->createBook($request->validated(), file: $file);

            return response()->json(data: [
                'success' => true,
                'data' => $book,
                'message' => 'Sách đã được tạo thành công.'
            ], status: 201);
        } catch (ErrorsException $e) {
            return response()->json(data: [
                'success' => false,
                'message' => $e->getMessage()
            ], status: $e->getCode());
        }
    }

    public function update(UpdateBookRequest $request, int $id): JsonResponse
    {
        try {
            $file = $request->file(key: 'cover_image');
            $book = $this->bookService->updateBook(id: $id, data: $request->validated(), file: $file);

            if ($book) {
                return response()->json(data: [
                    'success' => true,
                    'data' => $book,
                    'message' => 'Sách đã được cập nhật thành công.'
                ], status: 200);
            }

            return response()->json(data: [
                'success' => false,
                'message' => 'Sách không tồn tại hoặc có lỗi xảy ra khi cập nhật.'
            ], status: 404);
        } catch (ErrorsException $e) {
            return response()->json(data: [
                'success' => false,
                'message' => $e->getMessage()
            ], status: $e->getCode());
        }
    }


    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->bookService->deleteBook(id: $id);

            if ($result) {
                return response()->json(data: [
                    'success' => true,
                    'message' => 'Sách đã được xóa thành công.'
                ], status: 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Sách không tồn tại hoặc có lỗi xảy ra khi xóa.'
            ], 404);
        } catch (ErrorsException $e) {
            return response()->json(data: [
                'success' => false,
                'message' => $e->getMessage()
            ], status: $e->getCode());
        }
    }

    public function searchByKeyword(string $keyword): JsonResponse
    {
        try {
            $books = $this->bookService->searchBooks(keyword: $keyword);
            return response()->json([
                'success' => true,
                'data' => $books,
                'message' => 'Danh sách sách tìm kiếm theo từ khóa đã được lấy thành công.'
            ], status: 200);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], status: $e->getCode());
        }
    }
    public function advancedSearch(Request $request): JsonResponse
    {
        try {
            // Lấy các tham số tìm kiếm
            $filters = $request->only(['keyword', 'author', 'published_year', 'status', 'publisher_id']);

            // Kiểm tra xem có ít nhất một tham số nào được cung cấp không
            if (empty(array_filter($filters))) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'Vui lòng nhập ít nhất một tham số tìm kiếm.',
                ], 400);
            }

            // Lấy ID người dùng đang đăng nhập
            $userId = Auth::id(); // Hoặc $request->user()->id;

            // Tìm kiếm sách
            $books = $this->bookService->advancedSearchBooks($filters, $userId);

            return response()->json([
                'success' => true,
                'data' => $books,
                'message' => 'Kết quả tìm kiếm nâng cao đã được lấy thành công.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }



    public function uploadCover(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
            $file = $request->file(key: 'cover_image');
            if ($file) {
                $book = $this->bookService->uploadBookCover(id: $id, file: $file);
                return response()->json(data: [
                    'success' => true,
                    'data' => $book,
                    'message' => 'Ảnh bìa sách đã được tải lên thành công.'
                ], status: 200);
            }

            return response()->json(data: [
                'success' => false,
                'message' => 'Không có tệp hình ảnh nào được gửi.'
            ], status: 400);
        } catch (ErrorsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], status: $e->getCode());
        }
    }

    public function deleteCover(int $id): JsonResponse
    {
        try {
            $book = $this->bookService->deleteBookCover(id: $id);
            if ($book) {
                return response()->json(data: [
                    'success' => true,
                    'message' => 'Ảnh bìa sách đã được xóa thành công.'
                ], status: 200);
            }

            return response()->json(data: [
                'success' => false,
                'message' => 'Sách không tồn tại hoặc có lỗi xảy ra khi xóa ảnh.'
            ], status: 404);
        } catch (ErrorsException $e) {
            return response()->json(data: [
                'success' => false,
                'message' => $e->getMessage()
            ], status: $e->getCode());
        }
    }
}