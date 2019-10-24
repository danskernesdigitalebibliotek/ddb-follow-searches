<?php

namespace App\Listeners;

use App\Events\ListDeleted;

class ListDeletedStatsListener extends BaseListener
{
    public function handle(ListDeleted $event)
    {
        $count = $this->database->table('searches')
            ->distinct()
            ->count('guid');

        $this->statsCollector->event(
            $event->getUser()->getId(),
            'delete_list',
            $event->getList(),
            null,
            $count
        );
    }
}
