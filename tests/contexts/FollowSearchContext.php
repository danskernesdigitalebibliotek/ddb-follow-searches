<?php

use App\Contracts\Searcher;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Laravel\Lumen\Testing\Concerns\MakesHttpRequests;
use Prophecy\ObjectProphecy;
use Prophecy\Argument;
use Prophecy\Prophet;

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class FollowSearchContext implements Context, SnippetAcceptingContext
{
    use MakesHttpRequests;

    /**
     * The application instance.
     *
     * @var \Laravel\Lumen\Application
     */
    protected $app;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Faker instance.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var \Prophecy\Prophet
     */
    protected $prophet;

    /**
     * @var \Prophecy\ObjectProphecy
     */
    protected $searcher;

    /**
     * Scenario state data.
     */
    protected $state = [
        'searchResults' => [],
    ];

    public function __construct()
    {
        $this->faker = Faker\Factory::create();
    }

    /**
     * Boot the app before each scenario.
     *
     * @BeforeScenario
     */
    public function before(BeforeScenarioScope $scope)
    {
        $this->state = [];
        // Boot the app.

        putenv('APP_ENV=testing');
        // To get original exception message rather than the useless 'Internal
        // error'.
        putenv('APP_DEBUG=true');
        putenv('ADGANGSPLATFORMEN_DRIVER=testing');

        // Use in-memory db for speed.
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');

        Facade::clearResolvedInstances();

        $this->app = require __DIR__ . '/../../bootstrap/app.php';

        $url = $this->app->make('config')->get('app.url', env('APP_URL', 'http://localhost'));

        $this->app->make('url')->forceRootUrl($url);

        $this->app->boot();

        // Run migration to create db tables.
        $this->artisan('migrate:fresh');

        $this->prophet = new Prophet();

        // Create a Searcher mock.
        $searcher = $this->prophet->prophesize(Searcher::class);
        // Stub for those tests that don't care.
        $searcher->getCounts(Argument::any())->willReturn([]);
        $this->app->singleton(Searcher::class, function () use ($searcher) {
            return $searcher->reveal();
        });
        $this->searcher = $searcher;
    }

    /**
     * Clean up after each scenario.
     *
     * @AfterScenario
     */
    public function after(AfterScenarioScope $scope)
    {
        // If something locked down time, release it before the next scenario.
        Carbon::setTestNow(null);
    }

    /**
     * Call artisan command and return code.
     *
     * (pilfered from Laravel\Lumen\Testing\TestCase)
     *
     * @param string  $command
     * @param array   $parameters
     * @return int
     */
    public function artisan($command, $parameters = [])
    {
        $kernel = $this->app['Illuminate\Contracts\Console\Kernel'];
        if (!$kernel) {
            throw new Exception('Cannot call artisan without a kernel');
        }
        return $kernel->call($command, $parameters);
    }

    /**
     * Get headers for requests.
     *
     * Most importantly the Authorization header.
     */
    protected function getHeaders() : array
    {
        return [
            'Authorization' => 'Bearer ' . $this->state['token'],
        ];
    }

    /**
     * Get and check the basic structure of the searches response.
     *
     * @return array
     *   The response as array.
     */
    protected function getSearchesResponse(): array
    {
        $this->checkStatusCode(200);
        $json = $this->response->getContent();
        if (!$json) {
            throw new Exception('Empty response');
        }
        $response = json_decode($json, true);

        return $response;
    }

    /**
     * Get and check the basic structure of the search response.
     *
     * @return array
     *   The response as array.
     */
    protected function getSearchResponse(): array
    {
        $this->checkStatusCode(200);
        $json = $this->response->getContent();
        if (!$json) {
            throw new Exception('Empty response');
        }
        $response = json_decode($json, true);

        return $response;
    }

    /**
     * Add searches to list.
     *
     * @param string $guid
     *   User guid.
     * @param string $list
     *   List to add searches to.
     * @param array $searches
     *   Searches to add. Each an Column => value array.
     */
    protected function addToList(string $guid, string $list, $searches)
    {
        foreach ($searches as $search) {
            DB::table('searches')->insert([
                'guid' => $guid,
                'list' => $list,
                'title' => $search['title'],
                'query' => $search['query'],
                'last_seen' => isset($search['last_seen']) ? Carbon::parse($search['last_seen']) : Carbon::now(),
                'changed_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
            ]);
        }
    }

    /**
     * Get the id of the search with the given title.
     */
    protected function getSearchIdFromTitle($title)
    {
        $this->fetchingSearches();
        $response = $this->getSearchesResponse();
        foreach ($response as $search) {
            if ($search['title'] == $title) {
                return $search['id'];
            }
        }
        throw new Exception(sprintf('Search "%s" not found on list', $title));
    }

    /**
     * @Given an unknown user
     */
    public function anUnknownUser()
    {
        // An empty token is considered bad in TestTokenAccess.
        $this->state['token'] = '';
    }

    /**
     * @Given a known user
     * @Given a known user that has no items on searches list
     */
    public function aKnownUser()
    {
        $this->state['token'] = $this->faker->sha1;
    }

    /**
     * @Then the system should return success
     */
    public function theSystemShouldReturnSuccess()
    {
        $this->checkStatusCode([200, 201, 204]);
    }

    /**
     * @Then the system should return access denied
     */
    public function theSystemShouldReturnAccessDenied()
    {
        $this->checkStatusCode(401);
    }

    /**
     * @Then the system should return not found
     */
    public function theSystemShouldReturnNotFound()
    {
        $this->checkStatusCode(404);
    }

    /**
     * @Then the system should return validation error
     */
    public function theSystemShouldReturnValidationError()
    {
        $this->checkStatusCode(422);
    }

    /**
     * Check that status code is the expected.
     */
    protected function checkStatusCode($expected)
    {
        if (!is_array($expected)) {
            $expected = [$expected];
        }
        if (!in_array($this->response->getStatusCode(), $expected)) {
            throw new Exception('Status code ' . $this->response->getStatusCode() .
                                ' instead of the expected ' . implode(', ', $expected) .
                                "\nResponse content: \n" . $this->response->getContent());
        }
    }

    /**
     * @When fetching searches
     */
    public function fetchingSearches()
    {
        $this->fetchingSearchesNamed('default');
    }

    /**
     * @When fetching :list searches
     */
    public function fetchingSearchesNamed($list, $page = null, $size = null)
    {
        $query = [];
        if ($page) {
            $query[] = 'page=' . $page;
        }
        if ($size) {
            $query[] = 'size=' . $size;
        }
        $query = implode('&', $query);
        if ($query) {
            $query = '?' . $query;
        }
        $this->get("/list/$list" . $query, $this->getHeaders());
    }

    /**
     * @Given the time is :time
     */
    public function theTimeIs($time)
    {
        Carbon::setTestNow(Carbon::parse($time));
    }

    /**
     * @When search :query with title :title is added to the list
     */
    public function searchWithTitleIsAddedToTheSearches($query, $title)
    {
        $list = 'default';
        $this->post("/list/$list/add", [
            'title' => $title,
            'query' => $query,
        ], $this->getHeaders());

        print_r($this->response->getContent());
        $this->checkStatusCode(201);
    }

    /**
     * @Then the searches list should be emtpy
     */
    public function theSearchesListShouldBeEmtpy()
    {
        $response = $this->getSearchesResponse();
        if (!empty($response)) {
            throw new Exception('Searches not empty');
        }
    }

    /**
     * @Given they have the following items on the list:
     */
    public function theyHaveTheFollowingItemsOnTheList(TableNode $table)
    {
        $columns = $table->getRow(0);
        if (!in_array('title', $columns) || !in_array('query', $columns)) {
            throw new Exception('Need at least "title" and "query" to create search');
        }

        $this->addToList($this->state['token'], 'default', $table->getHash());
    }

    /**
     * @Given the searches has the following hitcounts:
     */
    public function theSearchesHasTheFollowingHitcounts(TableNode $table)
    {
        $hitcounts = [];
        foreach ($table as $row) {
            $pids = isset($row['pids']) ? array_map(function ($pid) {
                return trim($pid);
            }, explode(',', $row['pids'])) : [];
            $hitcount = isset($row['hitcount']) ? $row['hitcount'] : count($pids);
            $hitcounts[$row['query']] = [
                'hitcount' => $hitcount,
                'pids' => $pids,
            ];
        }

        $this->searcher->getCounts(Argument::any())->will(function ($args) use ($hitcounts) {
            $res = [];
            foreach ($args[0] as $id => $search) {
                $res[$id] = isset($hitcounts[$search['query']]['hitcount']) ?
                    $hitcounts[$search['query']]['hitcount'] :
                    0;
            }

            return $res;
        });

        foreach ($hitcounts as $query => $hitcount) {
            $this->searcher->getSearch($query, Argument::any())->will(function ($args) use ($hitcount) {
                $res = [];
                foreach ($hitcount['pids'] as $pid) {
                    $res[] = [
                        'pid' => $pid,
                    ];
                }

                return $res;
            });
        }
    }

    /**
     * @Then the searches list should contain:
     */
    public function theSearchesListShouldContain(TableNode $table)
    {
        $response = $this->getSearchesResponse();
        $index = 0;
        foreach ($table as $row) {
            foreach ($row as $prop => $value) {
                if ($response[$index][$prop] != $value) {
                    throw new Exception(sprintf(
                        'Unexpected "%s": "%s", expected "%s"',
                        $prop,
                        $response[$index][$prop],
                        $value
                    ));
                }
            }
            $index++;
        }
    }

    /**
     * @When fetching searches should return:
     */
    public function fetchingSearchesShouldReturn(TableNode $table)
    {
        $this->fetchingSearches();
        $this->theSystemShouldReturnSuccess();
        $this->theSearchesListShouldContain($table);
    }

    /**
     * @Then search :query should be on the list with title :title
     */
    public function searchShouldBeOnTheListWithTitle($query, $title)
    {
        $this->fetchingSearches();
        $response = $this->getSearchesResponse();
        foreach ($response as $search) {
            if ($search['query'] == $query) {
                if ($search['title'] == $title) {
                    return;
                } else {
                    throw new Exception(sprintf('Query "%s" has wrong title: "%s"', $query, $search['title']));
                }
            }
        }
        throw new Exception(sprintf('Query "%s" not found on list', $query));
    }

    /**
     * @When deleting the search :title from the searches list
     */
    public function deletingTheSearchFromTheSearchesList($title)
    {
        $this->delete("/list/default/" . $this->getSearchIdFromTitle($title), [], $this->getHeaders());
    }

    /**
     * @When they fetch the :title search
     */
    public function theyFetchTheSearch($title)
    {
        $this->get("/list/default/" . $this->getSearchIdFromTitle($title), $this->getHeaders());
    }

    /**
     * @Then the search result should be:
     */
    public function theSearchResultShouldBe(TableNode $table)
    {
        $response = $this->getSearchResponse();
        $actualMaterials = $response['materials'];
        $expectedPids = $table->getColumn(0);
        // Lose header.
        array_shift($expectedPids);
        $expectedMaterials = [];
        foreach ($expectedPids as $pid) {
            $expectedMaterials[] = [
                'pid' => $pid,
            ];
        }
        if ($actualMaterials != $expectedMaterials) {
            throw new Exception(sprintf(
                'PIDs %s not equal %s',
                var_export($actualMaterials, true),
                var_export($expectedMaterials, true)
            ));
        }
    }

    /**
     * @Given they have searches from A to Z on their search list
     */
    public function theyHaveSearchesFromAToZOnTheirSearchList()
    {
        // Create searches yesterday.
        $time = Carbon::parse('yesterday');
        Carbon::setTestNow($time);
        foreach (range('A', 'Z') as $letter) {
            $this->searchWithTitleIsAddedToTheSearches($letter, $letter);
            $time->addSecond();
        }
    }

    /**
     * @When fetching the search list page :page, with a page size of :size
     */
    public function fetchingTheSearchListPageWithAPageSizeOf($page, $size)
    {
        $this->fetchingSearchesNamed('default', $page, $size);
    }

    /**
     * @Then /^the search list should have searches (.*)$/
     */
    public function theSearchListShouldHaveSearches($searches)
    {
        $searches = explode(',', $searches);
        $searches = array_filter(array_map('trim', $searches));

        $response = $this->getSearchesResponse();
        $index = 0;
        $actualSearches = [];
        foreach ($response as $search) {
            $actualSearches[] = $search['title'];
        }
        if ($actualSearches != $searches) {
            throw new Exception(sprintf(
                'Searches "%s" does not match "%s"',
                var_export($actualSearches, true),
                var_export($searches, true)
            ));
        }
    }
}
