<?php

namespace App;

use DDB\OpenPlatform\OpenPlatform;
use Illuminate\Support\Carbon;

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

    /**
     * Return counts for searches.
     *
     * Collects the count of new materials for each query, since the last_seen
     * date.
     *
     * @param array $searhes
     *   Array of <id> => ['query' => <string>, 'last_seen' => <Carbon>]
     * @param array
     *   Counts in <id> => <count> format.
     */
    public function getCounts($searches): array
    {
        $results = [];
        $responses = [];
        foreach ($searches as $id => $search) {
            $responses[$id] = $this->openplatform
                ->search($this->getAccessionQuery($search['query'], $search['last_seen']))
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

    /**
     * Return new materials for the given query, since the given last seen.
     *
     * @param string $query
     *   The CQL query.
     * @param Carbon $lastSeen
     *   The last seen date.
     *
     * @return array
     *   List of materials, each an array with at least a 'pid' key.
     */
    public function getSearch($query, Carbon $lastSeen): array
    {
        $result = [];
        $res = $this->openplatform
            ->search($this->getAccessionQuery($query, $lastSeen))
            ->withFields(['pid'])
            ->execute();

        foreach ($res->getMaterials() as $material) {
            $result[] = [
                'pid' => $material['pid'],
            ];
        }

        return $result;
    }

    protected function getAccessionQuery($query, $lastSeen)
    {
        return sprintf(
            '%s and facet.acsource=bibliotekskatalog and holdingsitem.accessiondate>%s',
            $query,
            $lastSeen->format('Y-m-d\TH:i:s\Z')
        );
    }
}
