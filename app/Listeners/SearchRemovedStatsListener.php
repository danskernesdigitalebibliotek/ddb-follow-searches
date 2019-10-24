<?php

namespace App\Listeners;

use App\Events\ListDeleted;
use App\Events\SearchRemoved;

class SearchRemovedStatsListener extends BaseListener
{
    public function handle(SearchRemoved $event)
    {
        $searches = $this->database->table('searches')
            ->where('guid', $event->getUser()->getId())
            ->where('list', $event->getList())
            ->select('query')
            ->pluck('query');

        $this->statsCollector->event(
            $event->getUser()->getId(),
            'remove_search',
            $event->getList(),
            $event->getSearch(),
            $searches->count(),
            $searches->toArray()
        );

        if (count($searches) === 0) {
            $this->dispatcher->dispatch(new ListDeleted($event->getUser(), $event->getList()));
        }
    }
}
