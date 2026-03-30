<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use App\Models\WeeklyMenu;
use App\Policies\WeeklyMenuPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
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
        Gate::policy(WeeklyMenu::class, WeeklyMenuPolicy::class);

        Carbon::setLocale(config('app.locale'));
        CarbonImmutable::setLocale(config('app.locale'));
        Date::setLocale(config('app.locale'));

        Vite::prefetch(concurrency: 3);
    }
}
