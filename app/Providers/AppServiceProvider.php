<?php

namespace App\Providers;

use App\Models\Recommendation;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // Pending-decision count badge on the nav — the size of the
        // queue the system is waiting on, visible from every page.
        View::share('navPendingCount', Recommendation::where('status', Recommendation::STATUS_PENDING)->count());
    }
}
