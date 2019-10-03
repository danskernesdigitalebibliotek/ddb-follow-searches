<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller;

/**
 * Class implementing the searches controller.
 */
class SearchesController extends Controller
{
    public function get(string $listId)
    {
        return [];
    }

    /**
 * Add a search to the table.
   *
   * @param \Illuminate\Http\Request $request
   *   The illuminate http request object.
   * @param string $searchQuery
   *   The actual human readable search query.
   *
   * @return \Illuminate\Http\Response
   *   The illuminate http response object.
   */
    public function addSearch(Request $request, string $searchQuery)
    {
        DB::table('searches')
        ->updateOrInsert(
            [
            'guid' => $request->user()->getId(),
            'search_query' => $searchQuery,
            ],
            [
                // We need to format the date ourselves to add microseconds.
                'changed_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
                'last_seen' => Carbon::now()
            ]
        );

        return new Response('', 201);
    }
}
