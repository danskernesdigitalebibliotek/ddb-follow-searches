<?php

namespace App\Providers;

use App\Contracts\Searcher;
use App\SearchCache;
use App\SearchOpenPlatform;
use App\SearchTesting;
use DDB\OpenPlatform\OpenPlatform;
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
        $this->app->singleton(OpenPlatform::class, function ($app) {
            return new OpenPlatform($app->make('request')->bearerToken());
        });

        $this->app->singleton(Searcher::class, function ($app) {
            // Same rule as Adgangsplatformen uses to select driver.
            if (env('ADGANGSPLATFORMEN_DRIVER', env('APP_ENV', 'production')) == 'production') {
                return new SearchCache($app->make(SearchOpenPlatform::class));
            } else {
                return new SearchTesting();
            }
        });
    }
}
