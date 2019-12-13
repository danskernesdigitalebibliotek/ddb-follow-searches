<?php

namespace App\Contracts;

use Illuminate\Support\Carbon;

interface Searcher
{
    /**
     * Return counts for searches.
     *
     * Collects the count of new materials for each query, since the last_seen
     * date.
     *
     * @param array $searches
     *   Array of <id> => ['query' => <string>, 'last_seen' => <Carbon>]
     * @return array
     *   Counts in <id> => <count> format.
     */
    public function getCounts(array $searches): array;

    /**
     * Return new materials for the given query, since the given last seen.
     *
     * @param string $query
     *   The CQL query.
     * @param Carbon $lastSeen
     *   The last seen date.
     * @param array<string> $fields
     *   Materials fields to return in result.
     *   See https://raw.githubusercontent.com/DBCDK/serviceprovider/master/doc/work-context.jsonld
     *   for possibilities.
     *
     * @return array
     *   List of materials, each an array with at least a 'pid' key.
     */
    public function getSearch(string $query, Carbon $lastSeen, array $fields = []): array;
}
