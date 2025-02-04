<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BookController;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\PenaltyController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\PublisherController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\BorrowTransactionController;
// Group routes for Account routes
Route::prefix('account')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refreshToken'])->middleware('auth:api');
    Route::put('update-password', [AuthController::class, 'updatePassword'])->middleware('auth:api');
    Route::put('update-profile', [AuthController::class, 'updateProfile'])->middleware('auth:api');

    //Forgot and reset password
    Route::post('reset-password', [AuthController::class, 'sendMail']);
    Route::put('reset-password/{token}', [AuthController::class, 'reset']);

    // Avatar
    Route::post('/avatar', [UserController::class, 'uploadAvatar'])->middleware('auth:api')->name('users.uploadAvatar');
    Route::delete('/avatar', [UserController::class, 'deleteAvatar'])->middleware('auth:api')->name('users.deleteAvatar');
});

// Group routes for Admin
Route::group(['middleware' => ['auth:api']], function () {
    // Group for users management
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/{id}', [UserController::class, 'show'])->name('users.show');
        Route::get('/{user_id}/transactions', [UserController::class, 'getUserTransactions']);
        Route::post('', [UserController::class, 'store'])->name('users.store')->middleware('auth:api', 'role:Admin');
        Route::put('/{user_id}', [UserController::class, 'updateUserInfo'])->name('users.update')->middleware('auth:api', 'role:Admin');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('auth:api', 'role:Admin');
        Route::get('/search/{keyword}', [UserController::class, 'search'])->name('users.search')->middleware('auth:api', 'role:Admin');

    });
    // Group for book management
    Route::prefix('books')->group(function () {
        Route::get('/', [BookController::class, 'index'])->name('books.index');
        Route::get('my-books', [BookController::class, 'getUserBooksAndTransactions'])->name('user.books');
        Route::get('/advanced-search', [BookController::class, 'advancedSearch'])->name('books.advancedSearch');
        Route::get('/{id}', [BookController::class, 'show'])->name('books.show');
        Route::post('/', [BookController::class, 'store'])->name('books.store')->middleware('auth:api', 'role:Admin');
        Route::post('/{id}', [BookController::class, 'update'])->name('books.update')->middleware('auth:api', 'role:Admin');
        Route::delete('/{id}', [BookController::class, 'destroy'])->name('books.destroy')->middleware('auth:api', 'role:Admin');
        Route::get('/search/{keyword}', [BookController::class, 'searchByKeyword'])->name('books.search');

        // Book cover image (1 ảnh đại diện)
        Route::post('/{id}/cover', [BookController::class, 'uploadCover'])->name('books.uploadCover');
        Route::delete('/{id}/cover', [BookController::class, 'deleteCover'])->name('books.deleteCover');

        // Book gallery images (nhiều ảnh)
        Route::get('/{id}/details', [BookController::class, 'getBookDetails']);
        Route::post('/{id}/book_images', [BookController::class, 'uploadBookImages'])->name('books.uploadImages');
        Route::get('/{bookId}/book_images', [BookController::class, 'getBookImages']);
        Route::delete('/{bookId}/book_images/{imageName}', [BookController::class, 'deleteBookImage']);
    });

    // Group for pushers management
    Route::prefix('publishers')->group(function () {
        Route::get('/', [PublisherController::class, 'index'])->name('publishers.index');
        Route::get('/{id}', [PublisherController::class, 'show'])->name('publishers.show');
        Route::post('/', [PublisherController::class, 'store'])->name('publishers.store')->middleware('auth:api', 'role:Admin');
        Route::put('/{id}', [PublisherController::class, 'update'])->name('publishers.update')->middleware('auth:api', 'role:Admin');
        Route::delete('/{id}', [PublisherController::class, 'destroy'])->name('publishers.destroy')->middleware('auth:api', 'role:Admin');
        Route::get('/search/{query}', [PublisherController::class, 'search'])->name('publishers.search');
    });

    // Group for categories management
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/{id}', [CategoryController::class, 'show'])->name('categories.show');
        Route::post('/', [CategoryController::class, 'store'])->name('categories.store')->middleware('auth:api', 'role:Admin');
        Route::put('/{id}', [CategoryController::class, 'update'])->name('categories.update')->middleware('auth:api', 'role:Admin');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy')->middleware('auth:api', 'role:Admin');
        Route::get('/search/{query}', [CategoryController::class, 'search'])->name('categories.search');

    });
    // Group for transactions management
    Route::prefix('transactions')->group(function () {
        Route::get('/', [BorrowTransactionController::class, 'getAllTransactions'])->middleware(['auth:api', 'role:Admin']);
        Route::get('/{id}', [BorrowTransactionController::class, 'getTransactionDetails'])->middleware(['auth:api', 'role:Admin']);



    });
    // Group for Report management
    Route::prefix('report')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->middleware(middleware: ['auth:api', 'role:Admin']);
        Route::get('/summary', [ReportController::class, 'summary'])->middleware(middleware: ['auth:api', 'role:Admin']);
        // Thống kê
        Route::get('/transaction-stats', [ReportController::class, 'getBorrowStats']);


    });



});






// Group for borrow-transaction
Route::prefix('borrow-transaction')->group(function () {


    //Reader
    Route::post('/', [BorrowTransactionController::class, 'create'])->middleware(middleware: 'auth:api');
    Route::get('/borrowed', [BorrowTransactionController::class, 'getBorrowedBooksByUser'])->middleware(middleware: 'auth:api');
    Route::put('/{id}/return', [BorrowTransactionController::class, 'returnBook'])->middleware(middleware: 'auth:api');
    Route::delete('/cancel/{id}', [BorrowTransactionController::class, 'cancelBorrowTransaction'])->middleware(middleware: 'auth:api');
    Route::get('/history', [BorrowTransactionController::class, 'getHistory'])->middleware(middleware: 'auth:api');
    Route::put('/{transactionId}/extend', [BorrowTransactionController::class, 'extendTransaction'])->middleware(middleware: 'auth:api');



    //Admin
    Route::get('/notify-upcoming-due', [BorrowTransactionController::class, 'testNotifyUsersOfUpcomingDueDate']);
    Route::put('/{id}/approve', [BorrowTransactionController::class, 'approve'])->middleware('auth:api', 'role:Admin');
    Route::delete('/{id}', [BorrowTransactionController::class, 'delete'])->middleware('auth:api', 'role:Admin');
    Route::get('/user/{userId}', [BorrowTransactionController::class, 'getAllByUser'])->middleware(middleware: 'auth:api');
    Route::get('/pending', [BorrowTransactionController::class, 'getPendingtransactions'])->middleware('auth:api', 'role:Admin');
    Route::get('/return', [BorrowTransactionController::class, 'getReturnTransactions'])->middleware('auth:api', 'role:Admin');
    Route::post('/reject/{id}', [BorrowTransactionController::class, 'rejectBorrowTransaction'])->middleware('auth:api', 'role:Admin');
    Route::put('/{id}/complete-return', [BorrowTransactionController::class, 'completeReturn'])->middleware('auth:api', 'role:Admin');

});


Route::group(['middleware' => ['auth:api']], function () {
    Route::prefix('/notifications')->group(function () {
        Route::post('/', [NotificationController::class, 'createNotification']);
        Route::get('/users', [NotificationController::class, 'getNotificationsByUser']);
        Route::get('/users/unread', [NotificationController::class, 'getUnreadNotificationsByUser']);
        Route::get('/users/unread-count', [NotificationController::class, 'getUnreadCount']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'deleteNotification']);
    });

    Route::prefix('/wishlist')->group(function () {
        Route::post('/check', [WishlistController::class, 'isBookInWishlist']);
        Route::post('/add', [WishlistController::class, 'addBookToWishlist']);
        Route::delete('/remove', [WishlistController::class, 'removeBookFromWishlist']);
        Route::get('/', [WishlistController::class, 'getWishlistByUser']);
    });

    Route::prefix('/reviews')->group(function () {
        // Các route hiện tại
        Route::get('/', [ReviewController::class, 'index'])->middleware('auth:api', 'role:Admin');
        // My reviews
        Route::get('/my-reviews', [ReviewController::class, 'getUserReviews'])->middleware('auth:api');
        Route::put('/my-reviews/{id}', [ReviewController::class, 'updateMyReview'])->middleware('auth:api');
        Route::delete('/my-reviews/{id}', [ReviewController::class, 'deleteMyReview'])->middleware('auth:api');
        Route::get('/{id}', [ReviewController::class, 'show'])->middleware('auth:api', 'role:Admin');
        Route::post('/', [ReviewController::class, 'store']);
        Route::put('/{id}', [ReviewController::class, 'update']);
        Route::delete('/{id}', [ReviewController::class, 'destroy']);
        Route::get('/book/{bookId}', [ReviewController::class, 'getReviewsByBook'])->middleware('auth:api', 'role:Admin');
        Route::get('/user/{userId}', [ReviewController::class, 'getReviewsByUser'])->middleware('auth:api', 'role:Admin');
    });


    Route::group(['prefix' => 'penalties'], function () {
        Route::get('/my-penalties', [PenaltyController::class, 'getUserPenalties']);
        Route::get('/user/{userId}', [PenaltyController::class, 'getByUser']);
        Route::put('/{id}', [PenaltyController::class, 'update']);
        Route::delete('/{id}', [PenaltyController::class, 'delete']);
        Route::get('/', [PenaltyController::class, 'getAll']);
    });
    Route::prefix('chats')->group(function () {
        // Lấy danh sách phòng chat của người dùng
        Route::get('/rooms', [ChatController::class, 'getRooms']);

        // Kiểm tra hoặc tạo phòng chat riêng tư
        Route::post('/private', [ChatController::class, 'checkOrCreatePrivateRoom']);

        // Lấy tin nhắn của một phòng chat
        Route::get('/{roomId}/messages', [ChatController::class, 'getMessages']);

        // Gửi tin nhắn vào phòng chat
        Route::post('/{roomId}/message', [ChatController::class, 'sendMessage']);

        // Lấy danh sách phòng chat có admin
        Route::get('/admin/rooms', [ChatController::class, 'getAdminRooms']);
    });



});