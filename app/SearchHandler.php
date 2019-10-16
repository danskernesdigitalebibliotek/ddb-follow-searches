<?php

namespace App;

use DDB\OpenPlatform\OpenPlatform;

class SearchHandler
{

    /**
     * @var \DDB\OpenPlatform\OpenPlatform
     */
    protected $openplatform;

    public function __construct(OpenPlatform $openplatform)
    {
        $this->openplatform = $openplatform;
    }

    public function getCounts($searches)
    {
        $results = [];
        $responses = [];
        foreach ($searches as $id => $search) {
            $responses[$id] = $this->openplatform
                ->search($search['query'] . ' and facet.acsource=bibliotekskatalog and ' .
                         'holdingsitem.accessiondate>' . $search['last_seen']->format('Y-m-d\TH:i:s\Z'))
                // Limit result. We'd like to set this to 0, but OpenPlatform has
                // a minimum of 1.
                ->withLimit(1)
                ->execute();
        }

        foreach ($responses as $id => $res) {
            $results[$id] = $res->getHitCount();
        }
        return $results;
    }

    public function getSearch($query, $lastseen)
    {

    }
}
