<?php

namespace App\Providers;

use App\Repositories\AuthRepository;
use App\Repositories\BookRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\EloquentRepository;
use App\Interfaces\AuthRepositoryInterface;
use App\Interfaces\BookRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\EloquentRepositoryInterface;
use App\Repositories\BorrowTransactionRepository;
use App\Interfaces\BorrowTransactionRepositoryInterface;
use App\Interfaces\PublisherRepositoryInterface;
use App\Repositories\PublisherRepository;
use App\Interfaces\NotificationRepositoryInterface;
use App\Repositories\NotificationRepository;
use App\Interfaces\WishlistRepositoryInterface;
use App\Repositories\WishlistRepository;
use App\Interfaces\ReviewRepositoryInterface;
use App\Repositories\ReviewRepository;
use App\Interfaces\BookImageRepositoryInterface;
use App\Repositories\BookImageRepository;
use App\Interfaces\PenaltyRepositoryInterface;
use App\Repositories\PenaltyRepository;
use App\Interfaces\ChatRepositoryInterface;
use App\Repositories\ChatRepository;
use App\Interfaces\CategoryRepositoryInterface;
use App\Repositories\CategoryRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(abstract: EloquentRepositoryInterface::class, concrete: EloquentRepository::class);
        $this->app->bind(abstract: UserRepositoryInterface::class, concrete: UserRepository::class);
        $this->app->bind(abstract: AuthRepositoryInterface::class, concrete: AuthRepository::class);
        $this->app->bind(abstract: BorrowTransactionRepositoryInterface::class, concrete: BorrowTransactionRepository::class);
        $this->app->bind(abstract: BookRepositoryInterface::class, concrete: BookRepository::class);
        $this->app->bind(abstract: PublisherRepositoryInterface::class, concrete: PublisherRepository::class);
        $this->app->bind(abstract: NotificationRepositoryInterface::class, concrete: NotificationRepository::class);
        $this->app->bind(abstract: WishlistRepositoryInterface::class, concrete: WishlistRepository::class);
        $this->app->bind(abstract: ReviewRepositoryInterface::class, concrete: ReviewRepository::class);
        $this->app->bind(abstract: BookImageRepositoryInterface::class, concrete: BookImageRepository::class);
        $this->app->bind(abstract: PenaltyRepositoryInterface::class, concrete: PenaltyRepository::class);
        $this->app->bind(abstract: ChatRepositoryInterface::class, concrete: ChatRepository::class);
        $this->app->bind(abstract: CategoryRepositoryInterface::class, concrete: CategoryRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}