<?php

namespace App\Listeners;

use App\Events\ListCreated;
use App\Events\SearchAdded;

class SearchAddedStatsListener extends BaseListener
{
    public function handle(SearchAdded $event)
    {
        $searches = $this->database->table('searches')
            ->where('guid', $event->getUser()->getId())
            ->where('list', $event->getList())
            ->select('query')
            ->pluck('query');

            $searches2 = $this->database->table('searches')
            ->where('guid', $event->getUser()->getId())
            ->where('list', $event->getList());
        if ($searches2->count() === 0) {
            $this->dispatcher->dispatch(new ListCreated($event->getUser(), $event->getList()));
            $this->dispatcher->dispatch(new ListCreated($event->getUser(), $event->getList()));
        }

        $this->statsCollector->event(
            $event->getUser()->getId(),
            'add_search',
            $event->getList(),
            $event->getSearch(),
            $searches->count(),
            $searches->toArray()
        );
    }
}
