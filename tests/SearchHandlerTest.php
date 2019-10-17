<?php

namespace App;

use Illuminate\Support\Carbon;
use DDB\OpenPlatform\OpenPlatform;
use DDB\OpenPlatform\Request\SearchRequest;
use DDB\OpenPlatform\Response\SearchResponse;

class SearchHandlerTest extends TestCase
{
    public function testGetCounts()
    {
        $op = $this->prophesize(OpenPlatform::class);

        // First query.
        $response = $this->prophesize(SearchResponse::class);
        $response->getMaterials()
            ->shouldNotBeCalled();
        $response->getHitCount()
            ->willReturn(4);

        $search = $this->prophesize(SearchRequest::class);
        $search->withLimit(1)
            ->willReturn($search)
            ->shouldBeCalled();
        $search->execute()
            ->willReturn($response);

        $op->search('harry and facet.acsource=bibliotekskatalog and ' .
                    'holdingsitem.accessiondate>2019-10-02T10:00:00Z')
            ->willReturn($search);

        // Second query.
        $response2 = $this->prophesize(SearchResponse::class);
        $response2->getHitCount()
            ->willReturn(30);

        $search2 = $this->prophesize(SearchRequest::class);
        $search2->withLimit(1)
            ->willReturn($search2)
            ->shouldBeCalled();
        $search2->execute()
            ->willReturn($response2);

        $op->search('hitchhikers and facet.acsource=bibliotekskatalog and '.
                    'holdingsitem.accessiondate>2019-10-03T11:00:00Z')
            ->willReturn($search2);

        $searchHandler = new SearchHandler($op->reveal());

        $searches = [
            3 => ['query' => 'harry', 'last_seen' => Carbon::parse('2019-10-02 10:00:00')],
            42 => ['query' => 'hitchhikers', 'last_seen' => Carbon::parse('2019-10-03 11:00:00')],
        ];
        $res = $searchHandler->getCounts($searches);

        $this->assertEquals([3 => 4, 42 => 30], $res);
    }

    public function testGetSearch()
    {
        $op = $this->prophesize(OpenPlatform::class);

        $response = $this->prophesize(SearchResponse::class);
        $response->getMaterials()
            ->willReturn([
                [
                    'pid' => '1',
                    // We shouldn't leak random data from the search result to
                    // the client, so we'll add some there.
                    'unexpected' => 'data',
                ],
                ['pid' => '2']
            ]);
        $response->getHitCount()
            ->willReturn(2);

        $search = $this->prophesize(SearchRequest::class);
        $search->withFields(['pid'])
            ->willReturn($search);
        $search->execute()
            ->willReturn($response);

        $op->search('harry and facet.acsource=bibliotekskatalog and ' .
                    'holdingsitem.accessiondate>2019-10-05T13:00:00Z')
            ->willReturn($search);

        $searchHandler = new SearchHandler($op->reveal());

        $res = $searchHandler->getSearch('harry', Carbon::parse('2019-10-05 13:00:00'));

        $this->assertEquals([['pid' => '1'], ['pid' => '2']], $res);
    }
}
