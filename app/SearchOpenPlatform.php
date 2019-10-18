<?php

namespace App;

use App\Contracts\Searcher;
use DDB\OpenPlatform\OpenPlatform;
use Illuminate\Support\Carbon;

class SearchOpenPlatform implements Searcher
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
     * {@inheritdoc}
     */
    public function getCounts(array $searches): array
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
     * {@inheritdoc}
     */
    public function getSearch(string $query, Carbon $lastSeen): array
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
