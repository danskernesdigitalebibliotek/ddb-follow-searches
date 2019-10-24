<?php

namespace App\Listeners;

use App\Events\SearchChecked;

class SearchCheckedStatsListener extends BaseListener
{
    public function handle(SearchChecked $event)
    {
        $this->statsCollector->event(
            $event->getUser()->getId(),
            'check_search',
            $event->getList(),
            $event->getSearch()
        );
    }
}
