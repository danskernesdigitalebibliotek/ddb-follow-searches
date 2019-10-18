<?php

namespace App;

use Illuminate\Support\Carbon;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Prophecy\Argument;

class SearchCacheTest extends TestCase
{
    use DatabaseMigrations;

    public function tearDown(): void
    {
        parent::tearDown();
        Carbon::setTestNow();
    }

    public function testCacheSetAndCacheGetAndTimeout()
    {
        $now = Carbon::parse('2019-10-05 11:06:00');
        Carbon::setTestNow($now);
        $searcher = $this->prophesize(SearchOpenPlatform::class);
        $cache = new SearchCache($searcher->reveal());

        $cache->cacheSet('test', ['value']);
        $now->addHour();

        $this->assertEquals(['value'], $cache->cacheGet('test'));

        $now->addDay();

        $this->assertNull($cache->cacheGet('test'));
    }

    public function testGetCountsCaching()
    {
        $searcher = $this->prophesize(SearchOpenPlatform::class);
        $cache = new SearchCache($searcher->reveal());

        $searches = [2 => ['query' => 'test', 'last_seen' => Carbon::parse('2019-10-02 10:00:00')]];
        $expected = [2 => 4];
        $searcher->getCounts($searches)
            ->willReturn($expected);

        $res = $cache->getCounts($searches);
        // Expect one call on empty cache.
        $searcher->getCounts($searches)
            ->shouldHaveBeenCalledTimes(1);

        $this->assertEquals($expected, $res);

        $res = $cache->getCounts($searches);
        // Expect that we're still at one call.
        $searcher->getCounts($searches)
            ->shouldHaveBeenCalledTimes(1);

        $this->assertEquals($expected, $res);
        // Not testing that the cache times out too, that's covered by
        // testCacheSetAndCacheGetAndTimeout().
    }

    public function testGetSearchCache()
    {
        $searcher = $this->prophesize(SearchOpenPlatform::class);
        $cache = new SearchCache($searcher->reveal());

        $searcher->getSearch('harry', Carbon::parse('2019-10-05 13:00:00'))
            ->willReturn([['pid' => '1'], ['pid' => '2']]);

        // Once...
        $this->assertEquals(
            [['pid' => '1'], ['pid' => '2']],
            $cache->getSearch('harry', Carbon::parse('2019-10-05 13:00:00'))
        );

        $searcher->getSearch('harry', Carbon::parse('2019-10-05 13:00:00'))
            ->shouldHaveBeenCalledTimes(1);

        // Twice...
        $this->assertEquals(
            [['pid' => '1'], ['pid' => '2']],
            $cache->getSearch('harry', Carbon::parse('2019-10-05 13:00:00'))
        );

        $searcher->getSearch('harry', Carbon::parse('2019-10-05 13:00:00'))
            ->shouldHaveBeenCalledTimes(1);
    }
}
