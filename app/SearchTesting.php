<?php

namespace App;

use App\Contracts\Searcher;
use Illuminate\Support\Carbon;

/**
 * Testing search provider.
 */
class SearchTesting implements Searcher
{

    /**
     * {@inheritdoc}
     */
    public function getCounts(array $searches): array
    {
        $results = [];

        foreach ($searches as $key => $search) {
            // Set number of hits to the amount of 24 hour periods since last_seen.
            $results[$key] = $search['last_seen']->diffInDays(Carbon::now());
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearch(string $query, Carbon $lastSeen): array
    {
        $result = [];
        $count = $lastSeen->diffInDays(Carbon::now());

        foreach (range(1, $count) as $index) {
            $result[] = [
                'pid' => 'pid ' . $index,
            ];
        }

        return $result;
    }
}
