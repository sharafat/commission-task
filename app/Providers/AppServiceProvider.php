<?php

namespace App\Providers;

use App\Repositories\TransactionRepository;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TransactionRepository::class, fn() => new TransactionRepository());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setWeekStartsAt(Carbon::MONDAY);
        Carbon::setWeekEndsAt(Carbon::SUNDAY);
    }
}
