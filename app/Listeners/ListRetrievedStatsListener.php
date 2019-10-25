<?php

namespace App\Listeners;

use App\Events\ListRetrieved;

class ListRetrievedStatsListener extends BaseListener
{
    public function handle(ListRetrieved $event)
    {
        $this->statsCollector->event(
            $event->getUser()->getId(),
            'get_list',
            $event->getList()
        );
    }
}
