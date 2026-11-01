<?php

namespace App\Providers;

use App\Models\Report;
use App\Observers\ReportObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Report::observe(ReportObserver::class);
    }
}
