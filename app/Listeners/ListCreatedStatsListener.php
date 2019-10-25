<?php

namespace App\Listeners;

use App\Events\ListCreated;

class ListCreatedStatsListener extends BaseListener
{
    public function handle(ListCreated $event)
    {
        $count = $this->database->table('searches')
            ->distinct()
            ->count('list');

        $this->statsCollector->event(
            $event->getUser()->getId(),
            'create_list',
            $event->getList(),
            null,
            $count
        );
    }
}
