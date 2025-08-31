<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Models\User;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       
        
        // ثبت زمان شروع درخواست
    $startTime = microtime(true);

    // قبل از اینکه پاسخ به کاربر فرستاده بشه، زمان پایان رو ثبت می‌کنیم
    app()->terminating(function () use ($startTime) {
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // ثبت زمان کل اجرای درخواست
        Log::info('Total Execution Time: ' . $executionTime . ' seconds');
    });

    DB::listen(function ($query) {
        Log::info(
            'Query Executed: ' . $query->sql,
            ['bindings' => $query->bindings, 'time' => $query->time]
        );
    });
    }
}
