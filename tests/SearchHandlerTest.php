<?php

namespace App;

use Illuminate\Support\Carbon;
use DDB\OpenPlatform\OpenPlatform;
use DDB\OpenPlatform\Request\SearchRequest;
use DDB\OpenPlatform\Response\SearchResponse;

class SearchHandlerTest extends TestCase
{
    public function testFirst()
    {
        $op = $this->prophesize(OpenPlatform::class);

        // First query.
        $searchResponse = $this->prophesize(SearchResponse::class);
        $searchResponse->getData()
            ->shouldNotBeCalled();
        $searchResponse->getHitCount()
            ->willReturn(4)
            ->shouldBeCalled();
        $searchRequest = $this->prophesize(SearchRequest::class);

        $searchRequest->withLimit(1)->willReturn($searchRequest)->shouldBeCalled();

        $searchRequest->execute()
            ->willReturn($searchResponse);

        $op->search('harry and facet.acsource=bibliotekskatalog and ' .
                    'holdingsitem.accessiondate>2019-10-02T10:00:00Z')
            ->willReturn($searchRequest);

        // Second query.
        $searchResponse2 = $this->prophesize(SearchResponse::class);
        $searchResponse2->getHitCount()
            ->willReturn(30)
            ->shouldBeCalled();
        $searchRequest2 = $this->prophesize(SearchRequest::class);

        $searchRequest2->withLimit(1)
            ->willReturn($searchRequest2)
            ->shouldBeCalled();

        $searchRequest2->execute()->willReturn($searchResponse2);

        $op->search('hitchhikers and facet.acsource=bibliotekskatalog and '.
                    'holdingsitem.accessiondate>2019-10-03T11:00:00Z')
            ->willReturn($searchRequest2);

        $searchHandler = new SearchHandler($op->reveal());

        $searches = [
            3 => ['query' => 'harry', 'last_seen' => Carbon::parse('2019-10-02 10:00:00')],
            42 => ['query' => 'hitchhikers', 'last_seen' => Carbon::parse('2019-10-03 11:00:00')],
        ];
        $res = $searchHandler->getCounts($searches);

        $this->assertEquals([3 => 4, 42 => 30], $res);
    }
}
