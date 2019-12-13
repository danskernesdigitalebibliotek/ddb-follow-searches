<?php

namespace App;

use Illuminate\Support\Carbon;

class SearchTestingTest extends TestCase
{
    public function testGetCounts()
    {
        $now = Carbon::parse('2019-10-05 10:00:00');
        Carbon::setTestNow($now);

        $searher = new SearchTesting();

        $searches = [
            3 => ['query' => 'harry', 'last_seen' => Carbon::parse('2019-10-02 10:00:00')],
            42 => ['query' => 'hitchhikers', 'last_seen' => Carbon::parse('2019-10-03 11:00:00')],
        ];

        // Should return a new material per 24 hours.
        $this->assertEquals([3 => 3, 42 => 1], $searher->getCounts($searches));

        $now->addDays(2);
        $this->assertEquals([3 => 5, 42 => 3], $searher->getCounts($searches));
    }

    public function testGetSearch()
    {
        $now = Carbon::parse('2019-10-08 10:00:00');
        Carbon::setTestNow($now);

        $search = new SearchTesting();

        $this->assertEquals(
            [['pid' => 'pid 1'], ['pid' => 'pid 2']],
            $search->getSearch('harry', Carbon::parse('2019-10-05 13:00:00'))
        );

        $this->assertEquals(
            [['pid' => 'pid 1', 'title' => 'title 1'], ['pid' => 'pid 2', 'title' => 'title 2']],
            $search->getSearch('harry', Carbon::parse('2019-10-05 13:00:00'), ['title'])
        );

        $now->addDays(2);

        $this->assertEquals(
            [['pid' => 'pid 1'], ['pid' => 'pid 2'], ['pid' => 'pid 3'], ['pid' => 'pid 4']],
            $search->getSearch('harry', Carbon::parse('2019-10-05 13:00:00'))
        );
    }
}
