<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        // ให้ paginate()->links() แสดงผลแบบ Bootstrap (แก้บัค pagination ไม่ตรงธีม)
        Paginator::useBootstrapFive();
    }
}
