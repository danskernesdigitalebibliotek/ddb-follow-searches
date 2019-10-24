<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return true;
    }

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\ListCreated' => [
          'App\Listeners\ListCreatedStatsListener'
        ],
        'App\Events\ListDeleted' => [
          'App\Listeners\ListDeletedStatsListener'
        ],
        'App\Events\ListRetrieved' => [
          'App\Listeners\ListRetrievedStatsListener'
        ],
        'App\Events\SearchAdded' => [
            'App\Listeners\SearchAddedStatsListener'
        ],
        'App\Events\SearchChecked' => [
            'App\Listeners\SearchCheckedStatsListener'
        ],
        'App\Events\SearchRemoved' => [
            'App\Listeners\SearchRemovedStatsListener'
        ]
    ];
}
