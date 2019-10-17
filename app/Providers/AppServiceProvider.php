<?php

namespace App\Providers;

use App\Contracts\Searcher;
use App\SearchCache;
use App\SearchOpenPlatform;
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
        $this->app->singleton(Searcher::class, function ($app) {
            return new SearchCache($app->make(SearchOpenPlatform::class));
        });
    }
}
