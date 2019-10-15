<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Laravel\Lumen\Testing\Concerns\MakesHttpRequests;

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
     * @var Faker\Generator
     */
    protected $faker;

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
        return $this->app['Illuminate\Contracts\Console\Kernel']->call($command, $parameters);
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
     * Check the basic structure of the searches response.
     *
     * @return array
     *   The response as array.
     */
    protected function checkSearchesResponse(): array
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
        print($this->response->getContent());
        $this->checkStatusCode(404);
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
    public function fetchingSearchesNamed($list)
    {
        $this->get("/list/$list", $this->getHeaders());
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
        $response = $this->checkSearchesResponse();
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
        foreach ($table as $row) {
            $pids = isset($row['pids']) ? array_map(function ($pid) { return trim($pid);}, explode(',', $row['pids'])) : [];
            $hitcount = isset($row['hitcount']) ? $row['hitcount'] : count($pids);
            $this->state['searchResults'][$row['query']] = [
                'hitcount' => $hitcount,
                'pids' => $pids,
            ];
        }
    }

    /**
     * @Then the searches list should contain:
     */
    public function theSearchesListShouldContain(TableNode $table)
    {
        $response = $this->checkSearchesResponse();
        $index = 0;
        foreach ($table as $row) {
            foreach ($row as $prop => $value) {
                if ($response[$index][$prop] != $value) {
                    throw new Exception("Unexpected \"{$prop}\": \"{$response[$index][$prop]}\", expected \"{$value}\"");
                }
            }
            $index++;
        }
        throw new PendingException();
    }
}
