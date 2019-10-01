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
    protected $state = [];

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
     * @Given an unknown user
     */
    public function anUnknownUser()
    {
        // An empty token is considered bad in TestTokenAccess.
        $this->state['token'] = '';
    }

    /**
     * @Given a known user
     * @Given a known user that has no items on list
     */
    public function aKnownUser()
    {
        $this->state['token'] = $this->faker->sha1;
    }
}
