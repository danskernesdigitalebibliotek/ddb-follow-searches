<?php

namespace App;

use App\Contracts\Searcher;
use DDB\OpenPlatform\OpenPlatform;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SearchCache implements Searcher
{
    /**
     * @var \App\Contracts\Searcher
     */
    protected $searchHandler;

    public function __construct(Searcher $searchHandler)
    {
        $this->searchHandler = $searchHandler;
    }

    public function getCounts(array $searches): array
    {
        // We're using an "all or nothing" strategy for counts.
        $cacheKey = 'counts-' . hash('sha1', serialize($searches));
        $cache = $this->cacheGet($cacheKey);
        if ($cache) {
            return $cache;
        }

        $result = $this->searchHandler->getCounts($searches);
        $this->cacheSet($cacheKey, $result);

        return $result;
    }

    public function getSearch(string $query, Carbon $lastSeen): array
    {
        $cacheKey = 'search-' . hash('sha1', $query . $lastSeen->format('c'));
        $cache = $this->cacheGet($cacheKey);
        if ($cache) {
            return $cache;
        }

        $result = $this->searchHandler->getSearch($query, $lastSeen);
        $this->cacheSet($cacheKey, $result);

        return $result;
    }

    public function cacheGet($key)
    {
        $cache = DB::table('cache')
            ->where('key', $key)
            ->where('timestamp', '>', Carbon::now()->subHours(6))
            ->first();
        if ($cache) {
            return \unserialize($cache->data);
        }
        return null;
    }

    public function cacheSet($key, $value)
    {
        // Clean cache.
        DB::table('cache')
            ->where('timestamp', '>', Carbon::now()->subHours(6))
            ->delete();
        DB::table('cache')->updateOrInsert(
            ['key' => $key],
            [
                'data' => \serialize($value),
                'timestamp' => Carbon::now(),
            ]
        );
    }
}
