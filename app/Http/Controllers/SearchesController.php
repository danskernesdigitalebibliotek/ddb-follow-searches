<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class implementing the searches controller.
 */
class SearchesController extends Controller
{
    public function get(string $list)
    {
        $this->checkList($list);
        $searches = DB::table('searches')
            ->where('list', '=', $list)
            ->orderBy('changed_at', 'desc')
            ->get(['title', 'query', 'last_seen']);

        foreach ($searches as $search) {
            $search->hit_count = 0;
        }
        return Response($searches);
    }

    /**
     * Add a search to the table.
     *
     * @param \Illuminate\Http\Request $request
     *   The illuminate http request object.
     *
     * @return \Illuminate\Http\Response
     *   The illuminate http response object.
     */
    public function addSearch(Request $request, string $list, string $title)
    {
        $this->checkList($list);
        $this->validate($request, [
            'query' => 'required|string|min:1|max:2048',
        ]);

        DB::table('searches')
            ->updateOrInsert(
                [
                    'guid' => $request->user()->getId(),
                    'list' => $list,
                    'title' => $title,
                    'query' => $request->get('query')
                ],
                [
                    // We need to format the date ourselves to add microseconds.
                    'changed_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
                    'last_seen' => Carbon::now()
                ]
            );

        return new Response('', 201);
    }

    public function removeSearch(Request $request, string $list, string $title)
    {
        $this->checkList($list);
        $count = DB::table('searches')
            ->where([
                'guid' => $request->user()->getId(),
                'list' => $list,
                'title' => $title,
            ])->delete();
        return new Response('', $count > 0 ? 204 : 404);
    }

    protected function checkList(string $listId)
    {
        if ($listId != 'default') {
            throw new NotFoundHttpException('No such list');
        }
    }
}
