<?php

namespace App\Http\Controllers;

use App\Contracts\Searcher;
use App\Events\ListCreated;
use App\Events\ListRetrieved;
use App\Events\SearchAdded;
use App\Events\SearchChecked;
use App\Events\SearchRemoved;
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
    public function get(Request $request, Searcher $searchHandler, string $listName)
    {
        /* @var \Adgangsplatformen\Provider\AdgangsplatformenUser $user */
        $user = $request->user();

        $this->validate($request, [
            'size' => 'integer|min:1',
            'page' => 'integer|min:1',
        ]);

        $this->checkList($listName);

        $searches = DB::table('searches')
            ->where('list', '=', $listName)
            ->where('guid', '=', $user->getId())
            ->when($request->query('size'), function ($query, $size) use ($request) {
                $query->take($size);
                $query->skip((intval($request->query('page', '1')) - 1) * $size);
            })
            ->orderBy('changed_at', 'desc')
            ->get(['id', 'title', 'query', 'last_seen']);

        $counts = [];
        foreach ($searches as $search) {
            $counts[$search->id] = ['query' => $search->query, 'last_seen' => Carbon::parse($search->last_seen)];
        }

        $counts = $searchHandler->getCounts($counts);
        foreach ($searches as $search) {
            $search->hit_count = isset($counts[$search->id]) ? $counts[$search->id] : 0;
        }

        event(new ListRetrieved($user, $listName));

        return $searches;
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
    public function addSearch(Request $request, string $listName)
    {
        $this->checkList($listName);

        /* @var \Adgangsplatformen\Provider\AdgangsplatformenUser $user */
        $user = $request->user();

        $this->validate($request, [
            'title' => 'required|string|min:1|max:255',
            'query' => 'required|string|min:1|max:2048',
        ]);

        $existingCount = DB::table('searches')->where([
            'guid' => $user->getId(),
            'list' => $listName,
        ])->count();

        DB::table('searches')
            ->updateOrInsert(
                [
                    'guid' => $request->user()->getId(),
                    'hash' => hash('sha512', $request->get('query')),
                ],
                [
                    'list' => $listName,
                    'title' => $request->get('title'),
                    'query' => $request->get('query'),
                    // We need to format the date ourselves to add microseconds.
                    'changed_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
                    'last_seen' => Carbon::now()
                ]
            );

        // We have to check the new count to determine whether we added or
        // updated an entry.
        $newCount = DB::table('searches')->where([
            'guid' => $user->getId(),
            'list' => $listName,
        ])->count();

        if ($newCount > $existingCount) {
            // If there was no items on the list before, we have created a new
            // one.
            if ($existingCount < 1) {
                event(new ListCreated($user, $listName));
            }

            event(new SearchAdded($user, $listName, DB::getPdo()->lastInsertId()));
        }

        return new Response('', 201);
    }

    public function getSearch(Request $request, Searcher $searchHandler, string $listName, string $searchId)
    {
        $this->checkList($listName);

        /* @var \Adgangsplatformen\Provider\AdgangsplatformenUser $user */
        $user = $request->user();

        $fields = [];

        $search = DB::table('searches')
            ->where([
                'guid' => $user->getId(),
                'list' => $listName,
                'id' => $searchId,
            ])
            ->first();

        if (!$search) {
            throw new NotFoundHttpException('No such search');
        }

        if ($request->has('fields')) {
            // The OpenAPI spec defines the parameter as a comma separated
            // list. OpenAPI defaults to using "id=1&id=2" for array types,
            // but PHP expects "id[]=1&id[]=2". So rather than trying to hack
            // around that, we use the other common option of using a single
            // comma separated value and just split it up here. Looks nicer in
            // the URL.
            $fields = explode(',', $request->get('fields'));
        }

        $materials = $searchHandler->getSearch($search->query, Carbon::parse($search->last_seen), $fields);

        DB::table('searches')
            ->where('id', $search->id)
            ->update(['last_seen' => Carbon::now()]);

        event(new SearchChecked($user, $listName, $search->id));

        return ['materials' => $materials];
    }

    public function removeSearch(Request $request, string $listName, string $searchId)
    {
        $this->checkList($listName);

        /* @var \Adgangsplatformen\Provider\AdgangsplatformenUser $user */
        $user = $request->user();

        $count = DB::table('searches')
            ->where([
                'guid' => $request->user()->getId(),
                'list' => $listName,
                'id' => $searchId,
            ])->delete();

        if ($count > 0) {
            event(new SearchRemoved($user, $listName, $searchId));
        }

        return new Response('', $count > 0 ? 204 : 404);
    }

    protected function checkList(string $listId)
    {
        if ($listId != 'default') {
            throw new NotFoundHttpException('No such list');
        }
    }
}
