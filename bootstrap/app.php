<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use App\Services\BorrowTransactionService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withSchedule(callback: function (Schedule $schedule): void {
        $schedule->call(callback: function (): void {
            app(abstract: BorrowTransactionService::class)->notifyUsersOfUpcomingDueDate();
        })->dailyAt(time: '06:00'); // Giờ gửi thông báo mỗi ngày
    })
    ->withMiddleware(callback: function (Middleware $middleware): void {
        $middleware->alias(aliases: [
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(using: function (AuthenticationException $e, Request $request) {
            if ($request->is(patterns: 'api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], status: 401);
            }
        });
    })->create();