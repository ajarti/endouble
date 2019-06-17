<?php

namespace App\Providers;

use App\Contracts\CacheService;
use App\Contracts\SourceTransport;
use App\Services\DBCache;
use App\Services\GuzzleTransport;
use App\Services\SpaceService;
use App\Services\ComicsService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Set the current transport service.
        $this->app->singleton(SourceTransport::class, function ($app) {
            return new GuzzleTransport();
        });

        // Set the current cache service.
        $this->app->singleton(CacheService::class, function ($app) {
            return new DBCache();
        });

        // Set the current sources services.

        $this->app->singleton('spaceService', function ($app) {
            return new SpaceService();
        });

        $this->app->singleton('comicsService', function ($app) {
            return new ComicsService();
        });

    }


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
