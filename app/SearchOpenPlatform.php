<?php

namespace App;

use App\Contracts\Searcher;
use DDB\OpenPlatform\OpenPlatform;
use Illuminate\Support\Carbon;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Throwable;

class SearchOpenPlatform implements Searcher
{
    use LoggerAwareTrait;

    /**
     * @var \DDB\OpenPlatform\OpenPlatform
     */
    protected $openplatform;

    public function __construct(OpenPlatform $openplatform, LoggerInterface $logger)
    {
        $this->openplatform = $openplatform;
        $this->logger = $logger;
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
                ->search($this->getAccessionQuery($search['query'], Carbon::parse($search['last_seen'])))
                // Limit result. We'd like to set this to 0, but OpenPlatform has
                // a minimum of 1.
                ->withLimit(1)
                ->execute();
        }

        foreach ($responses as $id => $res) {
            try {
                $results[$id] = $res->getHitCount();
            } catch (Throwable $e) {
                $this->logger->error($e->getMessage());
                $results[$id] = 0;
            }
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

        try {
            foreach ($res->getMaterials() as $material) {
                $result[] = [
                    'pid' => $material['pid'],
                ];
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            return [];
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
